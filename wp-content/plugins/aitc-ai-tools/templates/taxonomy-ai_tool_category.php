<?php
/**
 * Taxonomy template for AI Tool Categories
 */

get_header(); ?>

<div class="aitc-archive aitc-taxonomy">
	<header class="page-header">
		<h1 class="page-title"><?php single_term_title(); ?></h1>
		<?php if ( term_description() ) : ?>
			<div class="taxonomy-description"><?php echo term_description(); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="aitc-tools-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$post_id = get_the_ID();
				$official_url = get_post_meta( $post_id, '_official_url', true );
				$pricing_model = get_post_meta( $post_id, '_pricing_model', true );
				$editor_rating = get_post_meta( $post_id, '_editor_rating_value', true );
				$rating_summary = AITC_Ratings::get_rating_summary( $post_id );
				?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'aitc-tool-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="tool-thumbnail">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'medium' ); ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="tool-content">
						<h2 class="tool-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>

						<?php if ( has_excerpt() ) : ?>
							<div class="tool-excerpt">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>

						<div class="tool-meta">
							<?php if ( $editor_rating ) : ?>
								<div class="tool-rating">
									<span class="rating-label"><?php esc_html_e( 'Editor:', 'aitc-ai-tools' ); ?></span>
									<span class="rating-stars"><?php echo str_repeat( '★', round( floatval( $editor_rating ) ) ) . str_repeat( '☆', 5 - round( floatval( $editor_rating ) ) ); ?></span>
									<span class="rating-value"><?php echo number_format( floatval( $editor_rating ), 1 ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( $rating_summary && $rating_summary->count > 0 ) : ?>
								<div class="tool-user-rating">
									<span class="rating-label"><?php esc_html_e( 'Users:', 'aitc-ai-tools' ); ?></span>
									<span class="rating-stars"><?php echo str_repeat( '★', round( floatval( $rating_summary->average ) ) ) . str_repeat( '☆', 5 - round( floatval( $rating_summary->average ) ) ); ?></span>
									<span class="rating-value"><?php echo number_format( floatval( $rating_summary->average ), 1 ); ?></span>
									<span class="rating-count">(<?php echo number_format_i18n( $rating_summary->count ); ?>)</span>
								</div>
							<?php endif; ?>

							<?php if ( $pricing_model ) : ?>
								<div class="tool-pricing-model">
									<span class="pricing-badge"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $pricing_model ) ) ); ?></span>
								</div>
							<?php endif; ?>
						</div>

						<div class="tool-actions">
							<a href="<?php the_permalink(); ?>" class="button button-secondary"><?php esc_html_e( 'Learn More', 'aitc-ai-tools' ); ?></a>
							<?php if ( $official_url ) : ?>
								<a href="<?php echo esc_url( $official_url ); ?>" target="_blank" rel="nofollow noopener" class="button button-primary"><?php esc_html_e( 'Visit Site', 'aitc-ai-tools' ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</article>

			<?php endwhile; ?>
		</div>

		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 2,
				'prev_text' => __( '&laquo; Previous', 'aitc-ai-tools' ),
				'next_text' => __( 'Next &raquo;', 'aitc-ai-tools' ),
			)
		);
		?>

	<?php else : ?>
		<p><?php esc_html_e( 'No AI tools found in this category.', 'aitc-ai-tools' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
