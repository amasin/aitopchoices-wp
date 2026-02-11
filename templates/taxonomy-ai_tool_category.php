<?php
/**
 * Taxonomy template for AI Tool Categories
 */

get_header(); ?>

<div class="aitc-archive aitc-taxonomy">
	<header class="page-header">
		<h1 class="page-title"><?php single_term_title(); ?> <?php esc_html_e( 'AI Tools', 'aitc-ai-tools' ); ?></h1>
		<?php if ( term_description() ) : ?>
			<div class="aitc-archive__intro"><?php echo term_description(); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="aitc-archive__meta">
			<span class="aitc-archive__count">
				<?php
				$total = $wp_query->found_posts;
				printf( _n( '%s tool in this category', '%s tools in this category', $total, 'aitc-ai-tools' ), '<strong>' . number_format_i18n( $total ) . '</strong>' );
				?>
			</span>
		</div>

		<div class="aitc-tools-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$post_id = get_the_ID();
				$official_url = get_post_meta( $post_id, '_official_url', true );
				$pricing_model = get_post_meta( $post_id, '_pricing_model', true );
				$editor_rating = get_post_meta( $post_id, '_editor_rating_value', true );
				$rating_summary = AITC_Ratings::get_rating_summary( $post_id );
				$free_plan = get_post_meta( $post_id, '_aitc_free_plan_available', true );
				?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'aitc-tool-card' ); ?>>
					<div class="aitc-tool-card__header">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="aitc-tool-card__thumb" aria-hidden="true" tabindex="-1">
								<?php the_post_thumbnail( 'thumbnail' ); ?>
							</a>
						<?php endif; ?>
						<div class="aitc-tool-card__badges">
							<?php if ( $pricing_model ) : ?>
								<span class="aitc-badge aitc-badge--pricing"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $pricing_model ) ) ); ?></span>
							<?php endif; ?>
							<?php if ( $free_plan ) : ?>
								<span class="aitc-badge aitc-badge--free"><?php esc_html_e( 'Free Plan', 'aitc-ai-tools' ); ?></span>
							<?php endif; ?>
						</div>
					</div>

					<div class="aitc-tool-card__body">
						<h2 class="aitc-tool-card__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>

						<?php if ( has_excerpt() ) : ?>
							<p class="aitc-tool-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

						<div class="aitc-tool-card__ratings">
							<?php if ( $editor_rating ) : ?>
								<div class="aitc-tool-card__rating">
									<span class="aitc-tool-card__rating-label"><?php esc_html_e( 'Editor', 'aitc-ai-tools' ); ?></span>
									<span class="aitc-stars-sm" aria-label="<?php printf( esc_attr__( 'Editor rating: %s out of 5', 'aitc-ai-tools' ), number_format( floatval( $editor_rating ), 1 ) ); ?>">
										<span class="aitc-star aitc-star--filled">&#9733;</span> <?php echo number_format( floatval( $editor_rating ), 1 ); ?>
									</span>
								</div>
							<?php endif; ?>

							<?php if ( $rating_summary && $rating_summary->count > 0 ) : ?>
								<div class="aitc-tool-card__rating">
									<span class="aitc-tool-card__rating-label"><?php esc_html_e( 'Users', 'aitc-ai-tools' ); ?></span>
									<span class="aitc-stars-sm" aria-label="<?php printf( esc_attr__( 'User rating: %s out of 5 from %s reviews', 'aitc-ai-tools' ), number_format( floatval( $rating_summary->average ), 1 ), number_format_i18n( $rating_summary->count ) ); ?>">
										<span class="aitc-star aitc-star--filled">&#9733;</span> <?php echo number_format( floatval( $rating_summary->average ), 1 ); ?>
									</span>
									<span class="aitc-tool-card__review-count">(<?php echo number_format_i18n( $rating_summary->count ); ?>)</span>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="aitc-tool-card__footer">
						<a href="<?php the_permalink(); ?>" class="aitc-btn aitc-btn--secondary aitc-btn--full"><?php esc_html_e( 'Full Review', 'aitc-ai-tools' ); ?></a>
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
		<p class="aitc-empty-state"><?php esc_html_e( 'No AI tools found in this category.', 'aitc-ai-tools' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
