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
		$overview = get_post_meta( $post->ID, '_aitc_overview', true );
		if ( $overview ) {
			$schema['description'] = wp_strip_all_tags( $overview );
		} elseif ( $post->post_excerpt ) {
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
		$rating_summary = AITC_Ratings::get_rating_summary( $post->ID );

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

		$reviews = AITC_Ratings::get_approved_reviews( $post->ID, 5 );
		if ( ! empty( $reviews ) ) {
			$review_schema = array();
			foreach ( $reviews as $review ) {
				$review_item = array(
					'@type'        => 'Review',
					'reviewRating' => array(
						'@type'       => 'Rating',
						'ratingValue' => floatval( $review->rating ),
						'bestRating'  => 5,
						'worstRating' => 1,
					),
					'author'       => array(
						'@type' => 'Person',
						'name'  => $review->display_name ? wp_strip_all_tags( $review->display_name ) : __( 'Guest', 'aitc-ai-tools' ),
					),
				);

				$review_body = $review->review_text ? wp_strip_all_tags( $review->review_text ) : '';
				if ( $review_body ) {
					$review_item['reviewBody'] = $review_body;
				}

				$review_title = $review->review_title ? wp_strip_all_tags( $review->review_title ) : '';
				if ( $review_title ) {
					$review_item['name'] = $review_title;
				}

				$review_schema[] = $review_item;
			}
			$schema['review'] = $review_schema;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";

		self::output_breadcrumb_schema( $post->ID );
		self::output_faq_schema( $post->ID );
	}

	/**
	 * Get pricing offers for schema
	 */
	private static function get_pricing_offers( $post_id ) {
		$pricing_model = get_post_meta( $post_id, '_aitc_pricing_model', true );
		$free_plan_available = get_post_meta( $post_id, '_aitc_free_plan_available', true );
		$pricing_url = get_post_meta( $post_id, '_aitc_pricing_url', true );
		$official_url = get_post_meta( $post_id, '_official_url', true );
		$offer_url = $pricing_url ? $pricing_url : $official_url;

		$is_free = false;
		if ( $free_plan_available ) {
			$is_free = true;
		} elseif ( $pricing_model ) {
			$is_free = strtolower( $pricing_model ) === 'free';
		}

		$offer = array(
			'@type'        => 'Offer',
			'availability' => 'https://schema.org/OnlineOnly',
		);

		if ( $offer_url ) {
			$offer['url'] = $offer_url;
		}

		if ( $is_free ) {
			$offer['price'] = '0';
			$offer['priceCurrency'] = 'USD';
		}

		return $offer;
	}

	/**
	 * Output BreadcrumbList schema for AI tool
	 */
	private static function output_breadcrumb_schema( $post_id ) {
		$items = array();

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => __( 'Home', 'aitc-ai-tools' ),
			'item'     => home_url( '/' ),
		);

		$archive_link = get_post_type_archive_link( 'ai_tool' );
		if ( $archive_link ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => __( 'AI Tools', 'aitc-ai-tools' ),
				'item'     => $archive_link,
			);
		}

		$categories = get_the_terms( $post_id, 'ai_tool_category' );
		$position = 3;
		if ( $categories && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				if ( is_numeric( $category->name ) || empty( trim( $category->name ) ) ) {
					continue;
				}
				$term_link = get_term_link( $category );
				if ( ! is_wp_error( $term_link ) ) {
					$items[] = array(
						'@type'    => 'ListItem',
						'position' => $position++,
						'name'     => $category->name,
						'item'     => $term_link,
					);
				}
				break;
			}
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => get_the_title( $post_id ),
			'item'     => get_permalink( $post_id ),
		);

		$schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}

	/**
	 * Output FAQPage schema for AI tool
	 */
	private static function output_faq_schema( $post_id ) {
		$faqs_json = get_post_meta( $post_id, '_aitc_faqs', true );
		if ( ! $faqs_json ) {
			return;
		}

		$faqs = json_decode( $faqs_json, true );
		if ( ! is_array( $faqs ) ) {
			return;
		}

		$entities = array();
		foreach ( $faqs as $faq ) {
			if ( ! is_array( $faq ) || empty( $faq['q'] ) || empty( $faq['a'] ) ) {
				continue;
			}
			$entities[] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $faq['q'] ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_kses_post( $faq['a'] ),
				),
			);
		}

		if ( empty( $entities ) ) {
			return;
		}

		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'FAQPage',
			'mainEntity'  => $entities,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
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
