<?php
/**
 * Admin page for moderating ratings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AITC_Ratings_Admin {

	/**
	 * Initialize hooks
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_post_aitc_moderate_rating', array( __CLASS__, 'handle_moderation' ) );
	}

	/**
	 * Add admin submenu
	 */
	public static function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=ai_tool',
			__( 'Moderate Reviews', 'aitc-ai-tools' ),
			__( 'Moderate Reviews', 'aitc-ai-tools' ),
			'edit_posts',
			'aitc-moderate-reviews',
			array( __CLASS__, 'render_moderation_page' )
		);
	}

	/**
	 * Render moderation page
	 */
	public static function render_moderation_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'aitc-ai-tools' ) );
		}

		// Get status filter
		$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'pending';
		$valid_statuses = array( 'pending', 'approved', 'spam' );
		if ( ! in_array( $status, $valid_statuses, true ) ) {
			$status = 'pending';
		}

		// Get ratings
		$ratings = self::get_ratings_by_status( $status );
		?>

		<div class="wrap">
			<h1><?php esc_html_e( 'Moderate Reviews', 'aitc-ai-tools' ); ?></h1>

			<ul class="subsubsub">
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ai_tool&page=aitc-moderate-reviews&status=pending' ) ); ?>" <?php echo $status === 'pending' ? 'class="current"' : ''; ?>><?php esc_html_e( 'Pending', 'aitc-ai-tools' ); ?> (<?php echo self::get_count_by_status( 'pending' ); ?>)</a> |</li>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ai_tool&page=aitc-moderate-reviews&status=approved' ) ); ?>" <?php echo $status === 'approved' ? 'class="current"' : ''; ?>><?php esc_html_e( 'Approved', 'aitc-ai-tools' ); ?> (<?php echo self::get_count_by_status( 'approved' ); ?>)</a> |</li>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ai_tool&page=aitc-moderate-reviews&status=spam' ) ); ?>" <?php echo $status === 'spam' ? 'class="current"' : ''; ?>><?php esc_html_e( 'Spam', 'aitc-ai-tools' ); ?> (<?php echo self::get_count_by_status( 'spam' ); ?>)</a></li>
			</ul>

			<br class="clear">

			<?php if ( empty( $ratings ) ) : ?>
				<p><?php esc_html_e( 'No reviews found.', 'aitc-ai-tools' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Tool', 'aitc-ai-tools' ); ?></th>
							<th><?php esc_html_e( 'Rating', 'aitc-ai-tools' ); ?></th>
							<th><?php esc_html_e( 'Review', 'aitc-ai-tools' ); ?></th>
							<th><?php esc_html_e( 'Author', 'aitc-ai-tools' ); ?></th>
							<th><?php esc_html_e( 'Date', 'aitc-ai-tools' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'aitc-ai-tools' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $ratings as $rating ) : ?>
							<tr>
								<td>
									<a href="<?php echo esc_url( get_permalink( $rating->post_id ) ); ?>" target="_blank">
										<?php echo esc_html( get_the_title( $rating->post_id ) ); ?>
									</a>
								</td>
								<td>
									<?php echo str_repeat( '★', $rating->rating ) . str_repeat( '☆', 5 - $rating->rating ); ?>
								</td>
								<td>
									<?php if ( $rating->review_title ) : ?>
										<strong><?php echo esc_html( $rating->review_title ); ?></strong><br>
									<?php endif; ?>
									<?php echo esc_html( wp_trim_words( $rating->review_text, 20 ) ); ?>
								</td>
								<td>
									<?php
									if ( $rating->display_name ) {
										echo esc_html( $rating->display_name );
									} else {
										esc_html_e( 'Guest', 'aitc-ai-tools' );
									}
									?>
								</td>
								<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $rating->created_at ) ); ?></td>
								<td>
									<?php if ( $status !== 'approved' ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
											<input type="hidden" name="action" value="aitc_moderate_rating">
											<input type="hidden" name="rating_id" value="<?php echo esc_attr( $rating->id ); ?>">
											<input type="hidden" name="new_status" value="approved">
											<input type="hidden" name="current_status" value="<?php echo esc_attr( $status ); ?>">
											<?php wp_nonce_field( 'aitc_moderate_rating_' . $rating->id ); ?>
											<button type="submit" class="button button-small"><?php esc_html_e( 'Approve', 'aitc-ai-tools' ); ?></button>
										</form>
									<?php endif; ?>

									<?php if ( $status !== 'spam' ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
											<input type="hidden" name="action" value="aitc_moderate_rating">
											<input type="hidden" name="rating_id" value="<?php echo esc_attr( $rating->id ); ?>">
											<input type="hidden" name="new_status" value="spam">
											<input type="hidden" name="current_status" value="<?php echo esc_attr( $status ); ?>">
											<?php wp_nonce_field( 'aitc_moderate_rating_' . $rating->id ); ?>
											<button type="submit" class="button button-small"><?php esc_html_e( 'Spam', 'aitc-ai-tools' ); ?></button>
										</form>
									<?php endif; ?>

									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
										<input type="hidden" name="action" value="aitc_moderate_rating">
										<input type="hidden" name="rating_id" value="<?php echo esc_attr( $rating->id ); ?>">
										<input type="hidden" name="new_status" value="delete">
										<input type="hidden" name="current_status" value="<?php echo esc_attr( $status ); ?>">
										<?php wp_nonce_field( 'aitc_moderate_rating_' . $rating->id ); ?>
										<button type="submit" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this review?', 'aitc-ai-tools' ); ?>');"><?php esc_html_e( 'Delete', 'aitc-ai-tools' ); ?></button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Get ratings by status
	 */
	private static function get_ratings_by_status( $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'aitc_tool_ratings';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT r.*, u.display_name, p.post_title
				FROM {$table_name} r
				LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
				LEFT JOIN {$wpdb->posts} p ON r.post_id = p.ID
				WHERE r.status = %s
				ORDER BY r.created_at DESC
				LIMIT 100",
				$status
			)
		);
	}

	/**
	 * Get count by status
	 */
	private static function get_count_by_status( $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'aitc_tool_ratings';

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE status = %s",
				$status
			)
		);
	}

	/**
	 * Handle moderation actions
	 */
	public static function handle_moderation() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to perform this action.', 'aitc-ai-tools' ) );
		}

		$rating_id = isset( $_POST['rating_id'] ) ? absint( $_POST['rating_id'] ) : 0;
		$new_status = isset( $_POST['new_status'] ) ? sanitize_text_field( $_POST['new_status'] ) : '';
		$current_status = isset( $_POST['current_status'] ) ? sanitize_text_field( $_POST['current_status'] ) : 'pending';

		if ( ! $rating_id || ! wp_verify_nonce( $_POST['_wpnonce'], 'aitc_moderate_rating_' . $rating_id ) ) {
			wp_die( __( 'Invalid request.', 'aitc-ai-tools' ) );
		}

		if ( $new_status === 'delete' ) {
			AITC_Ratings::delete_rating( $rating_id );
			$message = __( 'Review deleted.', 'aitc-ai-tools' );
		} else {
			AITC_Ratings::update_status( $rating_id, $new_status );
			$message = __( 'Review status updated.', 'aitc-ai-tools' );
		}

		wp_redirect(
			add_query_arg(
				array(
					'post_type' => 'ai_tool',
					'page'      => 'aitc-moderate-reviews',
					'status'    => $current_status,
					'message'   => 'updated',
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}
}
