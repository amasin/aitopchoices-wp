<?php
/**
 * Single AI Tool Template
 */

get_header();

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();
	$official_url = get_post_meta( $post_id, '_official_url', true );
	$pricing_page_url = get_post_meta( $post_id, '_pricing_page_url', true );
	$pricing_model = get_post_meta( $post_id, '_pricing_model', true );
	$price_type = get_post_meta( $post_id, '_price_type', true );
	$price_single = get_post_meta( $post_id, '_price_single_amount', true );
	$price_range_low = get_post_meta( $post_id, '_price_range_low', true );
	$price_range_high = get_post_meta( $post_id, '_price_range_high', true );
	$billing_unit = get_post_meta( $post_id, '_billing_unit', true );
	$has_free_plan = get_post_meta( $post_id, '_has_free_plan', true );
	$has_free_trial = get_post_meta( $post_id, '_has_free_trial', true );
	$trial_days = get_post_meta( $post_id, '_trial_days', true );
	$pricing_tiers_json = get_post_meta( $post_id, '_pricing_tiers_json', true );
	$editor_rating = get_post_meta( $post_id, '_editor_rating_value', true );
	$editor_summary = get_post_meta( $post_id, '_editor_review_summary', true );
	$editor_pros = get_post_meta( $post_id, '_editor_pros', true );
	$editor_cons = get_post_meta( $post_id, '_editor_cons', true );
	$editor_features = get_post_meta( $post_id, '_editor_features', true );
	$rating_summary = AITC_Ratings::get_rating_summary( $post_id );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'aitc-single-tool' ); ?>>
		<header class="entry-header">
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

		<?php if ( has_excerpt() ) : ?>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div>
		<?php endif; ?>

		<div class="entry-content">
			<?php the_content(); ?>
		</div>

		<?php if ( $pricing_model || $price_type !== 'none' ) : ?>
			<div class="aitc-pricing-section">
				<h2><?php esc_html_e( 'Pricing', 'aitc-ai-tools' ); ?></h2>

				<?php if ( $pricing_model ) : ?>
					<p><strong><?php esc_html_e( 'Pricing Model:', 'aitc-ai-tools' ); ?></strong> <?php echo esc_html( ucfirst( str_replace( '_', ' ', $pricing_model ) ) ); ?></p>
				<?php endif; ?>

				<?php if ( $price_type === 'single' && $price_single ) : ?>
					<p class="aitc-price-display">
						<strong>$<?php echo number_format( floatval( $price_single ), 2 ); ?> USD</strong>
						<?php if ( $billing_unit ) : ?>
							<span class="billing-unit">/ <?php echo esc_html( str_replace( '_', ' ', $billing_unit ) ); ?></span>
						<?php endif; ?>
					</p>
				<?php elseif ( $price_type === 'range' && $price_range_low && $price_range_high ) : ?>
					<p class="aitc-price-display">
						<strong>$<?php echo number_format( floatval( $price_range_low ), 2 ); ?> - $<?php echo number_format( floatval( $price_range_high ), 2 ); ?> USD</strong>
						<?php if ( $billing_unit ) : ?>
							<span class="billing-unit">/ <?php echo esc_html( str_replace( '_', ' ', $billing_unit ) ); ?></span>
						<?php endif; ?>
					</p>
				<?php elseif ( $price_type === 'tiers' && $pricing_tiers_json ) : ?>
					<?php
					$tiers = json_decode( $pricing_tiers_json, true );
					if ( is_array( $tiers ) && ! empty( $tiers ) ) :
						?>
						<div class="aitc-pricing-tiers">
							<?php foreach ( $tiers as $tier ) : ?>
								<div class="pricing-tier">
									<?php if ( isset( $tier['name'] ) ) : ?>
										<h3><?php echo esc_html( $tier['name'] ); ?></h3>
									<?php endif; ?>
									<?php if ( isset( $tier['amount'] ) ) : ?>
										<p class="tier-price">
											<strong>$<?php echo number_format( floatval( $tier['amount'] ), 2 ); ?> <?php echo esc_html( $tier['currency'] ?? 'USD' ); ?></strong>
											<?php if ( isset( $tier['unit'] ) ) : ?>
												<span class="billing-unit">/ <?php echo esc_html( $tier['unit'] ); ?></span>
											<?php endif; ?>
										</p>
									<?php endif; ?>
									<?php if ( isset( $tier['notes'] ) && $tier['notes'] ) : ?>
										<p class="tier-notes"><?php echo esc_html( $tier['notes'] ); ?></p>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( $has_free_plan ) : ?>
					<p class="aitc-free-plan">✓ <?php esc_html_e( 'Free plan available', 'aitc-ai-tools' ); ?></p>
				<?php endif; ?>

				<?php if ( $has_free_trial ) : ?>
					<p class="aitc-free-trial">
						✓ <?php esc_html_e( 'Free trial available', 'aitc-ai-tools' ); ?>
						<?php if ( $trial_days ) : ?>
							(<?php echo absint( $trial_days ); ?> <?php esc_html_e( 'days', 'aitc-ai-tools' ); ?>)
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php if ( $pricing_page_url ) : ?>
					<p><a href="<?php echo esc_url( $pricing_page_url ); ?>" target="_blank" rel="nofollow noopener"><?php esc_html_e( 'View full pricing details', 'aitc-ai-tools' ); ?> &rarr;</a></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $editor_rating || $editor_summary || $editor_pros || $editor_cons || $editor_features ) : ?>
			<div class="aitc-editorial-review">
				<h2><?php esc_html_e( 'Our Review', 'aitc-ai-tools' ); ?></h2>

				<?php if ( $editor_rating ) : ?>
					<div class="aitc-editor-rating">
						<strong><?php esc_html_e( 'Editor Rating:', 'aitc-ai-tools' ); ?></strong>
						<span class="rating-value"><?php echo number_format( floatval( $editor_rating ), 1 ); ?>/5.0</span>
						<span class="rating-stars"><?php echo str_repeat( '★', round( floatval( $editor_rating ) ) ) . str_repeat( '☆', 5 - round( floatval( $editor_rating ) ) ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $editor_summary ) : ?>
					<div class="editor-summary">
						<?php echo wpautop( esc_html( $editor_summary ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $editor_features ) : ?>
					<div class="editor-features">
						<h3><?php esc_html_e( 'Key Features', 'aitc-ai-tools' ); ?></h3>
						<ul>
							<?php foreach ( explode( "\n", $editor_features ) as $feature ) : ?>
								<?php if ( trim( $feature ) ) : ?>
									<li><?php echo esc_html( trim( $feature ) ); ?></li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( $editor_pros ) : ?>
					<div class="editor-pros">
						<h3><?php esc_html_e( 'Pros', 'aitc-ai-tools' ); ?></h3>
						<ul>
							<?php foreach ( explode( "\n", $editor_pros ) as $pro ) : ?>
								<?php if ( trim( $pro ) ) : ?>
									<li>✓ <?php echo esc_html( trim( $pro ) ); ?></li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( $editor_cons ) : ?>
					<div class="editor-cons">
						<h3><?php esc_html_e( 'Cons', 'aitc-ai-tools' ); ?></h3>
						<ul>
							<?php foreach ( explode( "\n", $editor_cons ) as $con ) : ?>
								<?php if ( trim( $con ) ) : ?>
									<li>✗ <?php echo esc_html( trim( $con ) ); ?></li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

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

					<div class="form-field" style="display:none;">
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

		<footer class="entry-footer">
			<?php
			$categories = get_the_terms( $post_id, 'ai_tool_category' );
			if ( $categories && ! is_wp_error( $categories ) ) :
				?>
				<div class="tool-categories">
					<strong><?php esc_html_e( 'Categories:', 'aitc-ai-tools' ); ?></strong>
					<?php
					$category_links = array();
					foreach ( $categories as $category ) {
						$category_links[] = '<a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
					}
					echo implode( ', ', $category_links );
					?>
				</div>
			<?php endif; ?>
		</footer>
	</article>

<?php endwhile; ?>

<?php get_footer(); ?>
