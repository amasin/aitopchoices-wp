<?php
/**
 * Ratings and reviews system
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AITC_Ratings {

	/**
	 * Table name
	 */
	private static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'aitc_tool_ratings';
	}

	/**
	 * Create ratings table on activation
	 */
	public static function create_table() {
		global $wpdb;
		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			user_id BIGINT(20) UNSIGNED DEFAULT NULL,
			rating TINYINT(1) UNSIGNED NOT NULL,
			review_title VARCHAR(255) DEFAULT NULL,
			review_text TEXT DEFAULT NULL,
			status ENUM('pending','approved','spam') NOT NULL DEFAULT 'pending',
			ip_hash CHAR(64) NOT NULL,
			user_agent_hash CHAR(64) NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY user_id (user_id),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Initialize hooks
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_aitc_submit_rating', array( __CLASS__, 'ajax_submit_rating' ) );
		add_action( 'wp_ajax_nopriv_aitc_submit_rating', array( __CLASS__, 'ajax_submit_rating' ) );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public static function enqueue_scripts() {
		if ( is_singular( 'ai_tool' ) ) {
			wp_enqueue_style( 'aitc-ratings', AITC_AI_TOOLS_URL . 'assets/css/ratings.css', array(), AITC_AI_TOOLS_VERSION );
			wp_enqueue_script( 'aitc-ratings', AITC_AI_TOOLS_URL . 'assets/js/ratings.js', array( 'jquery' ), AITC_AI_TOOLS_VERSION, true );
			wp_localize_script(
				'aitc-ratings',
				'aitcRatings',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'aitc_rating_nonce' ),
				)
			);
		}
	}

	/**
	 * AJAX handler for submitting ratings
	 */
	public static function ajax_submit_rating() {
		check_ajax_referer( 'aitc_rating_nonce', 'nonce' );

		$post_id      = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$rating       = isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0;
		$review_title = isset( $_POST['review_title'] ) ? sanitize_text_field( $_POST['review_title'] ) : '';
		$review_text  = isset( $_POST['review_text'] ) ? sanitize_textarea_field( $_POST['review_text'] ) : '';
		$honeypot     = isset( $_POST['website'] ) ? $_POST['website'] : '';

		// Validation
		if ( ! $post_id || get_post_type( $post_id ) !== 'ai_tool' ) {
			wp_send_json_error( array( 'message' => 'Invalid tool ID.' ) );
		}

		if ( $rating < 1 || $rating > 5 ) {
			wp_send_json_error( array( 'message' => 'Rating must be between 1 and 5.' ) );
		}

		// Honeypot check
		if ( ! empty( $honeypot ) ) {
			wp_send_json_error( array( 'message' => 'Spam detected.' ) );
		}

		// Rate limiting
		$ip_hash = hash( 'sha256', self::get_client_ip() );
		if ( self::is_rate_limited( $ip_hash ) ) {
			wp_send_json_error( array( 'message' => 'You are submitting reviews too quickly. Please wait a few minutes.' ) );
		}

		$user_id = get_current_user_id();
		$status  = 'pending';

		// Auto-approve for logged-in users
		if ( $user_id > 0 ) {
			$status = 'approved';
		}

		// Check for existing rating
		if ( $user_id > 0 ) {
			$existing = self::get_user_rating( $post_id, $user_id );
			if ( $existing ) {
				// Update existing
				$updated = self::update_rating(
					$existing->id,
					array(
						'rating'       => $rating,
						'review_title' => $review_title,
						'review_text'  => $review_text,
					)
				);
				if ( $updated ) {
					self::clear_rating_cache( $post_id );
					wp_send_json_success( array( 'message' => 'Your rating has been updated!' ) );
				} else {
					wp_send_json_error( array( 'message' => 'Failed to update rating.' ) );
				}
			}
		}

		// Insert new rating
		global $wpdb;
		$table_name = self::get_table_name();

		$result = $wpdb->insert(
			$table_name,
			array(
				'post_id'         => $post_id,
				'user_id'         => $user_id > 0 ? $user_id : null,
				'rating'          => $rating,
				'review_title'    => $review_title,
				'review_text'     => $review_text,
				'status'          => $status,
				'ip_hash'         => $ip_hash,
				'user_agent_hash' => hash( 'sha256', $_SERVER['HTTP_USER_AGENT'] ?? '' ),
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			self::clear_rating_cache( $post_id );
			$message = $status === 'approved' ? 'Your rating has been submitted!' : 'Your rating has been submitted and is awaiting moderation.';
			wp_send_json_success( array( 'message' => $message ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to submit rating.' ) );
		}
	}

	/**
	 * Get client IP address
	 */
	private static function get_client_ip() {
		$ip = '';
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	/**
	 * Check rate limiting (max 3 reviews per hour from same IP)
	 */
	private static function is_rate_limited( $ip_hash ) {
		global $wpdb;
		$table_name = self::get_table_name();
		$one_hour_ago = date( 'Y-m-d H:i:s', strtotime( '-1 hour' ) );

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE ip_hash = %s AND created_at > %s",
				$ip_hash,
				$one_hour_ago
			)
		);

		return $count >= 3;
	}

	/**
	 * Get user's rating for a tool
	 */
	public static function get_user_rating( $post_id, $user_id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE post_id = %d AND user_id = %d",
				$post_id,
				$user_id
			)
		);
	}

	/**
	 * Update existing rating
	 */
	private static function update_rating( $rating_id, $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		return $wpdb->update(
			$table_name,
			$data,
			array( 'id' => $rating_id ),
			array( '%d', '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Get rating summary for a tool (with caching)
	 */
	public static function get_rating_summary( $post_id ) {
		$cache_key = 'aitc_rating_summary_' . $post_id;
		$summary   = get_transient( $cache_key );

		if ( false === $summary ) {
			global $wpdb;
			$table_name = self::get_table_name();

			$summary = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT
						COUNT(*) as count,
						AVG(rating) as average,
						SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
						SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
						SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
						SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
						SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
					FROM {$table_name}
					WHERE post_id = %d AND status = 'approved'",
					$post_id
				)
			);

			set_transient( $cache_key, $summary, DAY_IN_SECONDS );
		}

		return $summary;
	}

	/**
	 * Get approved reviews for a tool
	 */
	public static function get_approved_reviews( $post_id, $limit = 10, $offset = 0 ) {
		global $wpdb;
		$table_name = self::get_table_name();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT r.*, u.display_name
				FROM {$table_name} r
				LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
				WHERE r.post_id = %d AND r.status = 'approved'
				ORDER BY r.created_at DESC
				LIMIT %d OFFSET %d",
				$post_id,
				$limit,
				$offset
			)
		);
	}

	/**
	 * Clear rating cache
	 */
	public static function clear_rating_cache( $post_id ) {
		delete_transient( 'aitc_rating_summary_' . $post_id );
	}

	/**
	 * Update rating status
	 */
	public static function update_status( $rating_id, $status ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$result = $wpdb->update(
			$table_name,
			array( 'status' => $status ),
			array( 'id' => $rating_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( $result ) {
			// Get post_id to clear cache
			$post_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT post_id FROM {$table_name} WHERE id = %d",
					$rating_id
				)
			);
			if ( $post_id ) {
				self::clear_rating_cache( $post_id );
			}
		}

		return $result;
	}

	/**
	 * Delete rating
	 */
	public static function delete_rating( $rating_id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		// Get post_id before deleting
		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$table_name} WHERE id = %d",
				$rating_id
			)
		);

		$result = $wpdb->delete(
			$table_name,
			array( 'id' => $rating_id ),
			array( '%d' )
		);

		if ( $result && $post_id ) {
			self::clear_rating_cache( $post_id );
		}

		return $result;
	}
}
