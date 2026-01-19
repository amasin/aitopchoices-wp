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
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'aitc-single-tool' ); ?>>
		<header class="entry-header">
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

			<h1 class="entry-title"><?php the_title(); ?></h1>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="tool-logo">
					<?php the_post_thumbnail( 'medium' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $official_url ) : ?>
				<p class="tool-official-link">
					<a href="<?php echo esc_url( $official_url ); ?>" target="_blank" rel="nofollow noopener" class="button">
						<?php esc_html_e( 'Visit Official Website', 'aitc-ai-tools' ); ?> &rarr;
					</a>
				</p>
			<?php endif; ?>
		</header>

		<div class="entry-content">
			<?php if ( $overview ) : ?>
				<div class="aitc-overview">
					<h2><?php esc_html_e( 'Overview', 'aitc-ai-tools' ); ?></h2>
					<?php echo wpautop( wp_kses_post( $overview ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $key_features ) ) : ?>
				<div class="aitc-key-features">
					<h2><?php esc_html_e( 'Key Features', 'aitc-ai-tools' ); ?></h2>
					<ul>
						<?php foreach ( $key_features as $feature ) : ?>
							<li><?php echo esc_html( $feature ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $best_use_cases ) ) : ?>
				<div class="aitc-best-use-cases">
					<h2><?php esc_html_e( 'Best Use Cases', 'aitc-ai-tools' ); ?></h2>
					<ul>
						<?php foreach ( $best_use_cases as $use_case ) : ?>
							<li><?php echo esc_html( $use_case ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( $has_pricing_section ) : ?>
				<div class="aitc-pricing-section">
					<h2><?php esc_html_e( 'Pricing & Plans', 'aitc-ai-tools' ); ?></h2>

					<?php if ( $pricing_model ) : ?>
						<p><strong><?php esc_html_e( 'Pricing Model:', 'aitc-ai-tools' ); ?></strong> <?php echo esc_html( $pricing_model ); ?></p>
					<?php endif; ?>

					<?php if ( $free_plan_available ) : ?>
						<p class="aitc-free-plan">✓ <?php esc_html_e( 'Free plan available', 'aitc-ai-tools' ); ?></p>
					<?php endif; ?>

					<?php if ( $pricing_notes ) : ?>
						<div class="aitc-pricing-notes">
							<?php echo wpautop( wp_kses_post( $pricing_notes ) ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $pricing_url ) : ?>
						<p><a href="<?php echo esc_url( $pricing_url ); ?>" target="_blank" rel="nofollow noopener"><?php esc_html_e( 'View pricing details', 'aitc-ai-tools' ); ?> &rarr;</a></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $pros ) || ! empty( $cons ) ) : ?>
				<div class="aitc-pros-cons">
					<h2><?php esc_html_e( 'Pros & Cons', 'aitc-ai-tools' ); ?></h2>

					<?php if ( ! empty( $pros ) ) : ?>
						<div class="aitc-pros">
							<h3><?php esc_html_e( 'Pros', 'aitc-ai-tools' ); ?></h3>
							<ul>
								<?php foreach ( $pros as $pro ) : ?>
									<li><?php echo esc_html( $pro ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $cons ) ) : ?>
						<div class="aitc-cons">
							<h3><?php esc_html_e( 'Cons', 'aitc-ai-tools' ); ?></h3>
							<ul>
								<?php foreach ( $cons as $con ) : ?>
									<li><?php echo esc_html( $con ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
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
					<div class="aitc-alternatives">
						<h2><?php esc_html_e( 'Alternatives', 'aitc-ai-tools' ); ?></h2>
						<ul>
							<?php foreach ( $alternatives as $slug ) : ?>
								<?php if ( isset( $alternative_map[ $slug ] ) ) : ?>
									<li>
										<a href="<?php echo esc_url( get_permalink( $alternative_map[ $slug ]->ID ) ); ?>">
											<?php echo esc_html( get_the_title( $alternative_map[ $slug ]->ID ) ); ?>
										</a>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</div>
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
					<div class="aitc-related-tools">
						<h2><?php esc_html_e( 'Related AI Tools', 'aitc-ai-tools' ); ?></h2>
						<ul>
							<?php foreach ( $related_tools as $related_tool ) : ?>
								<li>
									<a href="<?php echo esc_url( get_permalink( $related_tool->ID ) ); ?>">
										<?php echo esc_html( get_the_title( $related_tool->ID ) ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="aitc-user-ratings">
			<h2><?php esc_html_e( 'User Reviews', 'aitc-ai-tools' ); ?></h2>

			<?php if ( $rating_summary && $rating_summary->count > 0 ) : ?>
				<div class="aitc-rating-summary">
					<div class="average-rating">
						<span class="rating-value"><?php echo number_format( floatval( $rating_summary->average ), 1 ); ?></span>
						<span class="rating-stars"><?php echo str_repeat( '★', round( floatval( $rating_summary->average ) ) ) . str_repeat( '☆', 5 - round( floatval( $rating_summary->average ) ) ); ?></span>
						<span class="rating-count"><?php printf( _n( '%s review', '%s reviews', $rating_summary->count, 'aitc-ai-tools' ), number_format_i18n( $rating_summary->count ) ); ?></span>
					</div>
				</div>

				<div class="aitc-reviews-list">
					<?php
					$reviews = AITC_Ratings::get_approved_reviews( $post_id, 10 );
					foreach ( $reviews as $review ) :
						?>
						<div class="aitc-review">
							<div class="review-header">
								<span class="review-author"><?php echo esc_html( $review->display_name ?: __( 'Guest', 'aitc-ai-tools' ) ); ?></span>
								<span class="review-rating"><?php echo str_repeat( '★', $review->rating ) . str_repeat( '☆', 5 - $review->rating ); ?></span>
								<span class="review-date"><?php echo esc_html( mysql2date( get_option( 'date_format' ), $review->created_at ) ); ?></span>
							</div>
							<?php if ( $review->review_title ) : ?>
								<h4 class="review-title"><?php echo esc_html( $review->review_title ); ?></h4>
							<?php endif; ?>
							<?php if ( $review->review_text ) : ?>
								<div class="review-text"><?php echo wpautop( esc_html( $review->review_text ) ); ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'No reviews yet. Be the first to review this tool!', 'aitc-ai-tools' ); ?></p>
			<?php endif; ?>

			<div class="aitc-submit-review">
				<h3><?php esc_html_e( 'Submit Your Review', 'aitc-ai-tools' ); ?></h3>
				<form id="aitc-review-form" class="aitc-review-form">
					<div class="form-field">
						<label for="aitc-rating"><?php esc_html_e( 'Your Rating:', 'aitc-ai-tools' ); ?> *</label>
						<div class="star-rating-input">
							<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
								<input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
								<label for="star<?php echo $i; ?>">★</label>
							<?php endfor; ?>
						</div>
					</div>

					<div class="form-field">
						<label for="aitc-review-title"><?php esc_html_e( 'Review Title:', 'aitc-ai-tools' ); ?></label>
						<input type="text" id="aitc-review-title" name="review_title" maxlength="255">
					</div>

					<div class="form-field">
						<label for="aitc-review-text"><?php esc_html_e( 'Your Review:', 'aitc-ai-tools' ); ?></label>
						<textarea id="aitc-review-text" name="review_text" rows="5"></textarea>
					</div>

					<div class="form-field" hidden>
						<label for="aitc-website"><?php esc_html_e( 'Website:', 'aitc-ai-tools' ); ?></label>
						<input type="text" id="aitc-website" name="website" tabindex="-1" autocomplete="off">
					</div>

					<input type="hidden" name="post_id" value="<?php echo absint( $post_id ); ?>">

					<div class="form-field">
						<button type="submit" class="button"><?php esc_html_e( 'Submit Review', 'aitc-ai-tools' ); ?></button>
					</div>

					<div class="aitc-form-message"></div>
				</form>
			</div>
		</div>

		<?php if ( ! empty( $faqs ) ) : ?>
			<div class="aitc-faqs">
				<h2><?php esc_html_e( 'FAQs', 'aitc-ai-tools' ); ?></h2>
				<?php foreach ( $faqs as $faq ) : ?>
					<div class="aitc-faq-item">
						<h3><?php echo esc_html( $faq['q'] ); ?></h3>
						<?php echo wpautop( wp_kses_post( $faq['a'] ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<footer class="entry-footer">
			<?php
			$categories = get_the_terms( $post_id, 'ai_tool_category' );
			if ( $categories && ! is_wp_error( $categories ) ) :
				// Filter out invalid categories (numeric names are bad data from imports)
				$valid_categories = array_filter( $categories, function( $cat ) {
					return ! is_numeric( $cat->name ) && ! empty( trim( $cat->name ) );
				} );
				if ( ! empty( $valid_categories ) ) :
				?>
				<div class="tool-categories">
					<strong><?php esc_html_e( 'Categories:', 'aitc-ai-tools' ); ?></strong>
					<?php
					$category_links = array();
					foreach ( $valid_categories as $category ) {
						$category_links[] = '<a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
					}
					echo implode( ', ', $category_links );
					?>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		</footer>
	</article>

<?php endwhile; ?>

<?php get_footer(); ?>
