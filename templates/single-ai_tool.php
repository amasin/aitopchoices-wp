<?php
/**
 * Single AI Tool Template
 */

get_header();

if ( ! function_exists( 'aitc_decode_json_array' ) ) {
	function aitc_decode_json_array( $value ) {
		if ( empty( $value ) ) {
			return array();
		}
		if ( is_array( $value ) ) {
			return array_values( array_filter( $value ) );
		}
		$decoded = json_decode( $value, true );
		if ( is_array( $decoded ) ) {
			return array_values( array_filter( $decoded ) );
		}
		return array();
	}
}

if ( ! function_exists( 'aitc_decode_faqs' ) ) {
	function aitc_decode_faqs( $value ) {
		$decoded = json_decode( (string) $value, true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}
		$faqs = array();
		foreach ( $decoded as $faq ) {
			if ( is_array( $faq ) && ! empty( $faq['q'] ) && ! empty( $faq['a'] ) ) {
				$faqs[] = array(
					'q' => $faq['q'],
					'a' => $faq['a'],
				);
			}
		}
		return $faqs;
	}
}

if ( ! function_exists( 'aitc_get_primary_tool_category' ) ) {
	function aitc_get_primary_tool_category( $post_id ) {
		$categories = get_the_terms( $post_id, 'ai_tool_category' );
		if ( ! $categories || is_wp_error( $categories ) ) {
			return null;
		}

		foreach ( $categories as $category ) {
			if ( ! is_numeric( $category->name ) && ! empty( trim( $category->name ) ) ) {
				return $category;
			}
		}

		return null;
	}
}

if ( ! function_exists( 'aitc_get_tool_breadcrumbs' ) ) {
	function aitc_get_tool_breadcrumbs( $post_id ) {
		$items = array(
			array(
				'label' => __( 'Home', 'aitc-ai-tools' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => __( 'AI Tools', 'aitc-ai-tools' ),
				'url'   => get_post_type_archive_link( 'ai_tool' ),
			),
		);

		$category = aitc_get_primary_tool_category( $post_id );
		if ( $category ) {
			$term_link = get_term_link( $category );
			if ( ! is_wp_error( $term_link ) ) {
				$items[] = array(
					'label' => $category->name,
					'url'   => $term_link,
				);
			}
		}

		$items[] = array(
			'label' => get_the_title( $post_id ),
			'url'   => '',
		);

		return $items;
	}
}

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();
	$official_url = get_post_meta( $post_id, '_official_url', true );
	$overview = get_post_meta( $post_id, '_aitc_overview', true );
	$key_features = aitc_decode_json_array( get_post_meta( $post_id, '_aitc_key_features', true ) );
	$best_use_cases = aitc_decode_json_array( get_post_meta( $post_id, '_aitc_best_use_cases', true ) );
	$pricing_model = get_post_meta( $post_id, '_aitc_pricing_model', true );
	$free_plan_available = get_post_meta( $post_id, '_aitc_free_plan_available', true );
	$pricing_notes = get_post_meta( $post_id, '_aitc_pricing_notes', true );
	$pricing_url = get_post_meta( $post_id, '_aitc_pricing_url', true );
	$pros = aitc_decode_json_array( get_post_meta( $post_id, '_aitc_pros', true ) );
	$cons = aitc_decode_json_array( get_post_meta( $post_id, '_aitc_cons', true ) );
	$alternatives = aitc_decode_json_array( get_post_meta( $post_id, '_aitc_alternatives', true ) );
	$faqs = aitc_decode_faqs( get_post_meta( $post_id, '_aitc_faqs', true ) );
	$rating_summary = AITC_Ratings::get_rating_summary( $post_id );
	$breadcrumbs = aitc_get_tool_breadcrumbs( $post_id );
	$primary_category = aitc_get_primary_tool_category( $post_id );
	$has_pricing_section = $pricing_model || $free_plan_available || $pricing_notes || $pricing_url;
	$last_modified = get_the_modified_date( 'c' );
	$last_modified_display = get_the_modified_date();
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'aitc-single-tool' ); ?>>

		<?php do_action( 'aitc_before_tool_title', $post_id, $breadcrumbs ); ?>

		<?php if ( ! empty( $breadcrumbs ) ) : ?>
			<nav class="aitc-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'aitc-ai-tools' ); ?>">
				<ol>
					<?php foreach ( $breadcrumbs as $breadcrumb ) : ?>
						<li>
							<?php if ( ! empty( $breadcrumb['url'] ) ) : ?>
								<a href="<?php echo esc_url( $breadcrumb['url'] ); ?>"><?php echo esc_html( $breadcrumb['label'] ); ?></a>
							<?php else : ?>
								<span aria-current="page"><?php echo esc_html( $breadcrumb['label'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</nav>
		<?php endif; ?>

		<!-- Hero header: logo + title + key info above the fold -->
		<header class="aitc-tool-hero">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="aitc-tool-hero__logo">
					<?php the_post_thumbnail( 'thumbnail' ); ?>
				</div>
			<?php endif; ?>

			<div class="aitc-tool-hero__info">
				<div class="aitc-tool-hero__badges">
					<?php if ( $primary_category ) : ?>
						<a href="<?php echo esc_url( get_term_link( $primary_category ) ); ?>" class="aitc-badge aitc-badge--category"><?php echo esc_html( $primary_category->name ); ?></a>
					<?php endif; ?>
					<?php if ( $pricing_model ) : ?>
						<span class="aitc-badge aitc-badge--pricing"><?php echo esc_html( ucfirst( $pricing_model ) ); ?></span>
					<?php endif; ?>
					<?php if ( $free_plan_available ) : ?>
						<span class="aitc-badge aitc-badge--free"><?php esc_html_e( 'Free Plan', 'aitc-ai-tools' ); ?></span>
					<?php endif; ?>
				</div>

				<h1 class="entry-title"><?php the_title(); ?></h1>

				<?php if ( $rating_summary && $rating_summary->count > 0 ) : ?>
					<div class="aitc-tool-hero__rating">
						<span class="aitc-stars" aria-label="<?php printf( esc_attr__( 'Rated %s out of 5', 'aitc-ai-tools' ), number_format( floatval( $rating_summary->average ), 1 ) ); ?>">
							<?php echo str_repeat( '<span class="aitc-star aitc-star--filled">&#9733;</span>', round( floatval( $rating_summary->average ) ) ) . str_repeat( '<span class="aitc-star aitc-star--empty">&#9734;</span>', 5 - round( floatval( $rating_summary->average ) ) ); ?>
						</span>
						<span class="aitc-tool-hero__rating-text">
							<?php echo number_format( floatval( $rating_summary->average ), 1 ); ?>/5
							(<?php printf( _n( '%s review', '%s reviews', $rating_summary->count, 'aitc-ai-tools' ), number_format_i18n( $rating_summary->count ) ); ?>)
						</span>
					</div>
				<?php endif; ?>

				<?php if ( $official_url ) : ?>
					<div class="aitc-tool-hero__actions">
						<a href="<?php echo esc_url( $official_url ); ?>" target="_blank" rel="nofollow noopener" class="aitc-btn aitc-btn--primary">
							<?php esc_html_e( 'Visit Official Website', 'aitc-ai-tools' ); ?>
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
						</a>
					</div>
				<?php endif; ?>

				<div class="aitc-tool-hero__updated">
					<?php esc_html_e( 'Last updated:', 'aitc-ai-tools' ); ?>
					<time datetime="<?php echo esc_attr( $last_modified ); ?>"><?php echo esc_html( $last_modified_display ); ?></time>
				</div>
			</div>
		</header>

		<!-- At-a-glance summary card -->
		<div class="aitc-at-a-glance">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Quick Summary', 'aitc-ai-tools' ); ?></h2>
			<dl class="aitc-at-a-glance__grid">
				<?php if ( $pricing_model ) : ?>
					<div class="aitc-at-a-glance__item">
						<dt><?php esc_html_e( 'Pricing', 'aitc-ai-tools' ); ?></dt>
						<dd><?php echo esc_html( ucfirst( $pricing_model ) ); ?></dd>
					</div>
				<?php endif; ?>

				<?php if ( $free_plan_available ) : ?>
					<div class="aitc-at-a-glance__item">
						<dt><?php esc_html_e( 'Free Plan', 'aitc-ai-tools' ); ?></dt>
						<dd class="aitc-text--success"><?php esc_html_e( 'Yes', 'aitc-ai-tools' ); ?></dd>
					</div>
				<?php endif; ?>

				<?php if ( $rating_summary && $rating_summary->count > 0 ) : ?>
					<div class="aitc-at-a-glance__item">
						<dt><?php esc_html_e( 'User Rating', 'aitc-ai-tools' ); ?></dt>
						<dd><?php echo number_format( floatval( $rating_summary->average ), 1 ); ?>/5 (<?php echo number_format_i18n( $rating_summary->count ); ?>)</dd>
					</div>
				<?php endif; ?>

				<?php if ( $primary_category ) : ?>
					<div class="aitc-at-a-glance__item">
						<dt><?php esc_html_e( 'Category', 'aitc-ai-tools' ); ?></dt>
						<dd><a href="<?php echo esc_url( get_term_link( $primary_category ) ); ?>"><?php echo esc_html( $primary_category->name ); ?></a></dd>
					</div>
				<?php endif; ?>

				<?php
				$platforms = get_the_terms( $post_id, 'ai_platform' );
				if ( $platforms && ! is_wp_error( $platforms ) ) :
					$platform_names = wp_list_pluck( $platforms, 'name' );
					?>
					<div class="aitc-at-a-glance__item">
						<dt><?php esc_html_e( 'Platforms', 'aitc-ai-tools' ); ?></dt>
						<dd><?php echo esc_html( implode( ', ', $platform_names ) ); ?></dd>
					</div>
				<?php endif; ?>
			</dl>
		</div>

		<div class="entry-content">
			<?php if ( $overview ) : ?>
				<section class="aitc-section aitc-overview" id="overview">
					<h2><?php esc_html_e( 'Overview', 'aitc-ai-tools' ); ?></h2>
					<?php echo wpautop( wp_kses_post( $overview ) ); ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $key_features ) ) : ?>
				<section class="aitc-section aitc-key-features" id="features">
					<h2><?php esc_html_e( 'Key Features', 'aitc-ai-tools' ); ?></h2>
					<ul class="aitc-feature-list">
						<?php foreach ( $key_features as $feature ) : ?>
							<li><?php echo esc_html( $feature ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $best_use_cases ) ) : ?>
				<section class="aitc-section aitc-best-use-cases" id="use-cases">
					<h2><?php esc_html_e( 'Best Use Cases', 'aitc-ai-tools' ); ?></h2>
					<ul class="aitc-usecase-list">
						<?php foreach ( $best_use_cases as $use_case ) : ?>
							<li><?php echo esc_html( $use_case ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( $has_pricing_section ) : ?>
				<section class="aitc-section aitc-pricing-section" id="pricing">
					<h2><?php esc_html_e( 'Pricing & Plans', 'aitc-ai-tools' ); ?></h2>

					<?php if ( $pricing_model ) : ?>
						<p><strong><?php esc_html_e( 'Pricing Model:', 'aitc-ai-tools' ); ?></strong> <?php echo esc_html( $pricing_model ); ?></p>
					<?php endif; ?>

					<?php if ( $free_plan_available ) : ?>
						<p class="aitc-free-plan">
							<svg class="aitc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
							<?php esc_html_e( 'Free plan available', 'aitc-ai-tools' ); ?>
						</p>
					<?php endif; ?>

					<?php if ( $pricing_notes ) : ?>
						<div class="aitc-pricing-notes">
							<?php echo wpautop( wp_kses_post( $pricing_notes ) ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $pricing_url ) : ?>
						<p>
							<a href="<?php echo esc_url( $pricing_url ); ?>" target="_blank" rel="nofollow noopener" class="aitc-btn aitc-btn--outline">
								<?php esc_html_e( 'View pricing details', 'aitc-ai-tools' ); ?>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
							</a>
						</p>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $pros ) || ! empty( $cons ) ) : ?>
				<section class="aitc-section aitc-pros-cons" id="pros-cons">
					<h2><?php esc_html_e( 'Pros & Cons', 'aitc-ai-tools' ); ?></h2>
					<div class="aitc-pros-cons__grid">
						<?php if ( ! empty( $pros ) ) : ?>
							<div class="aitc-pros-cons__column aitc-pros-cons__column--pros">
								<h3>
									<svg class="aitc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
									<?php esc_html_e( 'Pros', 'aitc-ai-tools' ); ?>
								</h3>
								<ul>
									<?php foreach ( $pros as $pro ) : ?>
										<li><?php echo esc_html( $pro ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $cons ) ) : ?>
							<div class="aitc-pros-cons__column aitc-pros-cons__column--cons">
								<h3>
									<svg class="aitc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
									<?php esc_html_e( 'Cons', 'aitc-ai-tools' ); ?>
								</h3>
								<ul>
									<?php foreach ( $cons as $con ) : ?>
										<li><?php echo esc_html( $con ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $alternatives ) ) : ?>
				<?php
				$alternative_posts = get_posts(
					array(
						'post_type'      => 'ai_tool',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'post_name__in'  => $alternatives,
						'orderby'        => 'post_name__in',
					)
				);
				$alternative_map = array();
				foreach ( $alternative_posts as $alternative_post ) {
					$alternative_map[ $alternative_post->post_name ] = $alternative_post;
				}
				?>
				<?php if ( ! empty( $alternative_map ) ) : ?>
					<section class="aitc-section aitc-alternatives" id="alternatives">
						<h2><?php esc_html_e( 'Alternatives', 'aitc-ai-tools' ); ?></h2>
						<div class="aitc-mini-cards">
							<?php foreach ( $alternatives as $slug ) : ?>
								<?php if ( isset( $alternative_map[ $slug ] ) ) :
									$alt_post = $alternative_map[ $slug ];
									$alt_rating = AITC_Ratings::get_rating_summary( $alt_post->ID );
									$alt_pricing = get_post_meta( $alt_post->ID, '_aitc_pricing_model', true );
									?>
									<a href="<?php echo esc_url( get_permalink( $alt_post->ID ) ); ?>" class="aitc-mini-card">
										<?php if ( has_post_thumbnail( $alt_post->ID ) ) : ?>
											<div class="aitc-mini-card__thumb">
												<?php echo get_the_post_thumbnail( $alt_post->ID, 'thumbnail' ); ?>
											</div>
										<?php endif; ?>
										<div class="aitc-mini-card__body">
											<span class="aitc-mini-card__title"><?php echo esc_html( get_the_title( $alt_post->ID ) ); ?></span>
											<span class="aitc-mini-card__meta">
												<?php if ( $alt_rating && $alt_rating->count > 0 ) : ?>
													<span class="aitc-stars-sm" aria-label="<?php printf( esc_attr__( '%s out of 5', 'aitc-ai-tools' ), number_format( floatval( $alt_rating->average ), 1 ) ); ?>">&#9733; <?php echo number_format( floatval( $alt_rating->average ), 1 ); ?></span>
												<?php endif; ?>
												<?php if ( $alt_pricing ) : ?>
													<span class="aitc-badge aitc-badge--sm"><?php echo esc_html( ucfirst( $alt_pricing ) ); ?></span>
												<?php endif; ?>
											</span>
										</div>
									</a>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>
			<?php endif; ?>

			<?php do_action( 'aitc_after_alternatives', $post_id ); ?>

			<?php if ( $primary_category ) : ?>
				<?php
				$related_tools = get_posts(
					array(
						'post_type'      => 'ai_tool',
						'post_status'    => 'publish',
						'posts_per_page' => 4,
						'post__not_in'   => array( $post_id ),
						'tax_query'      => array(
							array(
								'taxonomy' => 'ai_tool_category',
								'field'    => 'term_id',
								'terms'    => array( $primary_category->term_id ),
							),
						),
					)
				);
				?>
				<?php if ( ! empty( $related_tools ) ) : ?>
					<section class="aitc-section aitc-related-tools" id="related">
						<h2><?php printf( esc_html__( 'More %s Tools', 'aitc-ai-tools' ), esc_html( $primary_category->name ) ); ?></h2>
						<div class="aitc-mini-cards">
							<?php foreach ( $related_tools as $related_tool ) :
								$rel_rating = AITC_Ratings::get_rating_summary( $related_tool->ID );
								$rel_pricing = get_post_meta( $related_tool->ID, '_aitc_pricing_model', true );
								?>
								<a href="<?php echo esc_url( get_permalink( $related_tool->ID ) ); ?>" class="aitc-mini-card">
									<?php if ( has_post_thumbnail( $related_tool->ID ) ) : ?>
										<div class="aitc-mini-card__thumb">
											<?php echo get_the_post_thumbnail( $related_tool->ID, 'thumbnail' ); ?>
										</div>
									<?php endif; ?>
									<div class="aitc-mini-card__body">
										<span class="aitc-mini-card__title"><?php echo esc_html( get_the_title( $related_tool->ID ) ); ?></span>
										<span class="aitc-mini-card__meta">
											<?php if ( $rel_rating && $rel_rating->count > 0 ) : ?>
												<span class="aitc-stars-sm" aria-label="<?php printf( esc_attr__( '%s out of 5', 'aitc-ai-tools' ), number_format( floatval( $rel_rating->average ), 1 ) ); ?>">&#9733; <?php echo number_format( floatval( $rel_rating->average ), 1 ); ?></span>
											<?php endif; ?>
											<?php if ( $rel_pricing ) : ?>
												<span class="aitc-badge aitc-badge--sm"><?php echo esc_html( ucfirst( $rel_pricing ) ); ?></span>
											<?php endif; ?>
										</span>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<!-- User Reviews -->
		<section class="aitc-section aitc-user-ratings" id="reviews">
			<h2><?php esc_html_e( 'User Reviews', 'aitc-ai-tools' ); ?></h2>

			<?php if ( $rating_summary && $rating_summary->count > 0 ) : ?>
				<div class="aitc-rating-summary">
					<div class="aitc-rating-summary__score">
						<span class="aitc-rating-summary__value"><?php echo number_format( floatval( $rating_summary->average ), 1 ); ?></span>
						<span class="aitc-rating-summary__max">/5</span>
					</div>
					<div class="aitc-rating-summary__detail">
						<span class="aitc-stars" aria-hidden="true">
							<?php echo str_repeat( '<span class="aitc-star aitc-star--filled">&#9733;</span>', round( floatval( $rating_summary->average ) ) ) . str_repeat( '<span class="aitc-star aitc-star--empty">&#9734;</span>', 5 - round( floatval( $rating_summary->average ) ) ); ?>
						</span>
						<span class="aitc-rating-summary__count"><?php printf( _n( 'Based on %s review', 'Based on %s reviews', $rating_summary->count, 'aitc-ai-tools' ), number_format_i18n( $rating_summary->count ) ); ?></span>
					</div>
				</div>

				<div class="aitc-reviews-list">
					<?php
					$reviews = AITC_Ratings::get_approved_reviews( $post_id, 10 );
					foreach ( $reviews as $review ) :
						$is_verified = ! empty( $review->user_id );
						?>
						<div class="aitc-review">
							<div class="aitc-review__header">
								<div class="aitc-review__author-info">
									<span class="aitc-review__author">
										<?php echo esc_html( $review->display_name ?: __( 'Anonymous', 'aitc-ai-tools' ) ); ?>
									</span>
									<?php if ( $is_verified ) : ?>
										<span class="aitc-badge aitc-badge--verified" title="<?php esc_attr_e( 'Verified user', 'aitc-ai-tools' ); ?>">
											<svg class="aitc-icon" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
											<?php esc_html_e( 'Verified', 'aitc-ai-tools' ); ?>
										</span>
									<?php endif; ?>
								</div>
								<div class="aitc-review__meta">
									<span class="aitc-review__rating aitc-stars-sm" aria-label="<?php printf( esc_attr__( 'Rated %s out of 5', 'aitc-ai-tools' ), $review->rating ); ?>">
										<?php echo str_repeat( '<span class="aitc-star aitc-star--filled">&#9733;</span>', $review->rating ) . str_repeat( '<span class="aitc-star aitc-star--empty">&#9734;</span>', 5 - $review->rating ); ?>
									</span>
									<time class="aitc-review__date" datetime="<?php echo esc_attr( date( 'c', strtotime( $review->created_at ) ) ); ?>">
										<?php echo esc_html( mysql2date( get_option( 'date_format' ), $review->created_at ) ); ?>
									</time>
								</div>
							</div>
							<?php if ( $review->review_title ) : ?>
								<h4 class="aitc-review__title"><?php echo esc_html( $review->review_title ); ?></h4>
							<?php endif; ?>
							<?php if ( $review->review_text ) : ?>
								<div class="aitc-review__text"><?php echo wpautop( esc_html( $review->review_text ) ); ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="aitc-empty-state"><?php esc_html_e( 'No reviews yet. Be the first to review this tool!', 'aitc-ai-tools' ); ?></p>
			<?php endif; ?>

			<details class="aitc-submit-review">
				<summary class="aitc-btn aitc-btn--outline">
					<?php esc_html_e( 'Write a Review', 'aitc-ai-tools' ); ?>
				</summary>
				<div class="aitc-submit-review__form-wrapper">
					<form id="aitc-review-form" class="aitc-review-form">
						<div class="form-field">
							<label for="aitc-rating"><?php esc_html_e( 'Your Rating', 'aitc-ai-tools' ); ?> <span aria-hidden="true">*</span></label>
							<div class="star-rating-input" role="radiogroup" aria-label="<?php esc_attr_e( 'Rating', 'aitc-ai-tools' ); ?>">
								<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
									<input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
									<label for="star<?php echo $i; ?>" title="<?php printf( esc_attr__( '%d star', 'aitc-ai-tools' ), $i ); ?>">&#9733;</label>
								<?php endfor; ?>
							</div>
						</div>

						<div class="form-field">
							<label for="aitc-review-title"><?php esc_html_e( 'Review Title', 'aitc-ai-tools' ); ?></label>
							<input type="text" id="aitc-review-title" name="review_title" maxlength="255" placeholder="<?php esc_attr_e( 'Summarize your experience', 'aitc-ai-tools' ); ?>">
						</div>

						<div class="form-field">
							<label for="aitc-review-text"><?php esc_html_e( 'Your Review', 'aitc-ai-tools' ); ?></label>
							<textarea id="aitc-review-text" name="review_text" rows="4" placeholder="<?php esc_attr_e( 'What did you like or dislike? How do you use this tool?', 'aitc-ai-tools' ); ?>"></textarea>
						</div>

						<div class="form-field" hidden>
							<label for="aitc-website"><?php esc_html_e( 'Website:', 'aitc-ai-tools' ); ?></label>
							<input type="text" id="aitc-website" name="website" tabindex="-1" autocomplete="off">
						</div>

						<input type="hidden" name="post_id" value="<?php echo absint( $post_id ); ?>">

						<div class="form-field">
							<button type="submit" class="aitc-btn aitc-btn--primary"><?php esc_html_e( 'Submit Review', 'aitc-ai-tools' ); ?></button>
						</div>

						<div class="aitc-form-message"></div>
					</form>
				</div>
			</details>
		</section>

		<?php if ( ! empty( $faqs ) ) : ?>
			<section class="aitc-section aitc-faqs" id="faqs">
				<h2><?php esc_html_e( 'Frequently Asked Questions', 'aitc-ai-tools' ); ?></h2>
				<?php foreach ( $faqs as $index => $faq ) : ?>
					<details class="aitc-faq-item" <?php echo $index === 0 ? 'open' : ''; ?>>
						<summary><?php echo esc_html( $faq['q'] ); ?></summary>
						<div class="aitc-faq-item__answer">
							<?php echo wpautop( wp_kses_post( $faq['a'] ) ); ?>
						</div>
					</details>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<footer class="entry-footer">
			<?php
			$categories = get_the_terms( $post_id, 'ai_tool_category' );
			if ( $categories && ! is_wp_error( $categories ) ) :
				$valid_categories = array_filter( $categories, function( $cat ) {
					return ! is_numeric( $cat->name ) && ! empty( trim( $cat->name ) );
				} );
				if ( ! empty( $valid_categories ) ) :
				?>
				<div class="aitc-tool-tags">
					<?php foreach ( $valid_categories as $category ) : ?>
						<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="aitc-tag"><?php echo esc_html( $category->name ); ?></a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		</footer>
	</article>

<?php endwhile; ?>

<?php get_footer(); ?>
