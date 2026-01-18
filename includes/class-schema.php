<?php
/**
 * JSON-LD schema output
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AITC_Schema {

	/**
	 * Initialize hooks
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'output_schema' ) );
	}

	/**
	 * Output schema in wp_head
	 */
	public static function output_schema() {
		// Check if schema is enabled
		if ( ! get_option( 'aitc_ai_tools_schema_enabled', '1' ) ) {
			return;
		}

		// Only on frontend
		if ( is_admin() ) {
			return;
		}

		if ( is_singular( 'ai_tool' ) ) {
			self::output_single_tool_schema();
		} elseif ( is_post_type_archive( 'ai_tool' ) || is_tax( array( 'ai_tool_category', 'ai_use_case', 'ai_platform', 'ai_pricing_model', 'ai_billing_unit' ) ) ) {
			self::output_list_schema();
		}
	}

	/**
	 * Output schema for single AI tool
	 */
	private static function output_single_tool_schema() {
		global $post;

		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'SoftwareApplication',
			'name'     => get_the_title(),
			'url'      => get_permalink(),
		);

		// Description
		if ( $post->post_excerpt ) {
			$schema['description'] = wp_strip_all_tags( $post->post_excerpt );
		} elseif ( $post->post_content ) {
			$schema['description'] = wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );
		}

		// Operating system
		$schema['operatingSystem'] = 'Web';

		// Category
		$categories = get_the_terms( $post->ID, 'ai_tool_category' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			// Filter out invalid categories (numeric names are bad data from imports)
			$valid_categories = array_filter( $categories, function( $cat ) {
				return ! is_numeric( $cat->name ) && ! empty( trim( $cat->name ) );
			} );
			if ( ! empty( $valid_categories ) ) {
				$category = reset( $valid_categories );
				$schema['applicationCategory'] = $category->name;
			}
		}

		// Image
		if ( has_post_thumbnail() ) {
			$schema['image'] = get_the_post_thumbnail_url( $post->ID, 'full' );
		}

		// Official URL
		$official_url = get_post_meta( $post->ID, '_official_url', true );
		if ( $official_url ) {
			$schema['sameAs'] = $official_url;
		}

		// Publisher
		$schema['provider'] = array(
			'@type' => 'Organization',
			'name'  => 'AI Top Choices',
		);

		// Pricing offers
		$offers = self::get_pricing_offers( $post->ID );
		if ( $offers ) {
			$schema['offers'] = $offers;
		}

		// Ratings
		$editor_rating = get_post_meta( $post->ID, '_editor_rating_value', true );
		$rating_summary = AITC_Ratings::get_rating_summary( $post->ID );

		// Editorial review
		if ( $editor_rating ) {
			$editor_review_summary = get_post_meta( $post->ID, '_editor_review_summary', true );

			$schema['review'] = array(
				'@type'         => 'Review',
				'reviewRating'  => array(
					'@type'       => 'Rating',
					'ratingValue' => floatval( $editor_rating ),
					'bestRating'  => 5,
					'worstRating' => 1,
				),
				'author'        => array(
					'@type' => 'Organization',
					'name'  => 'AI Top Choices',
				),
			);

			if ( $editor_review_summary ) {
				$schema['review']['reviewBody'] = $editor_review_summary;
			}
		}

		// Aggregate rating (only if >= 5 approved ratings)
		if ( $rating_summary && $rating_summary->count >= 5 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => round( floatval( $rating_summary->average ), 1 ),
				'reviewCount' => intval( $rating_summary->count ),
				'bestRating'  => 5,
				'worstRating' => 1,
			);
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}

	/**
	 * Get pricing offers for schema
	 */
	private static function get_pricing_offers( $post_id ) {
		$pricing_model = get_post_meta( $post_id, '_pricing_model', true );
		$price_type    = get_post_meta( $post_id, '_price_type', true );
		$pricing_page_url = get_post_meta( $post_id, '_pricing_page_url', true );
		$official_url  = get_post_meta( $post_id, '_official_url', true );
		$offer_url     = $pricing_page_url ? $pricing_page_url : $official_url;

		// Free tools
		if ( $pricing_model === 'free' || $pricing_model === 'open_source' ) {
			return array(
				'@type'         => 'Offer',
				'price'         => 0,
				'priceCurrency' => 'USD',
				'url'           => $offer_url,
			);
		}

		// Enterprise / Contact sales
		if ( $pricing_model === 'enterprise' || $price_type === 'none' ) {
			return array(
				'@type'       => 'Offer',
				'url'         => $offer_url,
				'description' => 'Contact sales for pricing',
			);
		}

		// Single price
		if ( $price_type === 'single' ) {
			$price = get_post_meta( $post_id, '_price_single_amount', true );
			if ( $price ) {
				return array(
					'@type'         => 'Offer',
					'price'         => floatval( $price ),
					'priceCurrency' => 'USD',
					'url'           => $offer_url,
				);
			}
		}

		// Range or tiers
		if ( $price_type === 'range' || $price_type === 'tiers' ) {
			$low  = 0;
			$high = 0;
			$offers_array = array();

			if ( $price_type === 'range' ) {
				$low  = get_post_meta( $post_id, '_price_range_low', true );
				$high = get_post_meta( $post_id, '_price_range_high', true );
			} elseif ( $price_type === 'tiers' ) {
				$tiers_json = get_post_meta( $post_id, '_pricing_tiers_json', true );
				$tiers = json_decode( $tiers_json, true );

				if ( is_array( $tiers ) && ! empty( $tiers ) ) {
					$prices = array_column( $tiers, 'amount' );
					$low  = min( $prices );
					$high = max( $prices );

					// Add up to 5 tiers to offers array
					foreach ( array_slice( $tiers, 0, 5 ) as $tier ) {
						if ( isset( $tier['amount'] ) ) {
							$offers_array[] = array(
								'@type'         => 'Offer',
								'name'          => $tier['name'] ?? '',
								'price'         => floatval( $tier['amount'] ),
								'priceCurrency' => $tier['currency'] ?? 'USD',
								'url'           => $offer_url,
							);
						}
					}
				}
			}

			if ( $low && $high ) {
				$aggregate = array(
					'@type'         => 'AggregateOffer',
					'lowPrice'      => floatval( $low ),
					'highPrice'     => floatval( $high ),
					'priceCurrency' => 'USD',
					'url'           => $offer_url,
				);

				if ( ! empty( $offers_array ) ) {
					$aggregate['offerCount'] = count( $offers_array );
					$aggregate['offers'] = $offers_array;
				}

				return $aggregate;
			}
		}

		return null;
	}

	/**
	 * Output ItemList schema for archives
	 */
	private static function output_list_schema() {
		global $wp_query;

		if ( ! have_posts() ) {
			return;
		}

		$schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'itemListElement' => array(),
		);

		$position = 1;
		$posts = $wp_query->posts;

		foreach ( array_slice( $posts, 0, 25 ) as $post ) {
			$schema['itemListElement'][] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'url'      => get_permalink( $post->ID ),
				'name'     => get_the_title( $post->ID ),
			);
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}
}
