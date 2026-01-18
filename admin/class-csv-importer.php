<?php
/**
 * CSV Importer for AI Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AITC_CSV_Importer {

	/**
	 * Initialize hooks
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_post_aitc_import_csv', array( __CLASS__, 'handle_import' ) );
	}

	/**
	 * Add admin submenu
	 */
	public static function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=ai_tool',
			__( 'Import CSV', 'aitc-ai-tools' ),
			__( 'Import CSV', 'aitc-ai-tools' ),
			'edit_posts',
			'aitc-import-csv',
			array( __CLASS__, 'render_import_page' )
		);
	}

	/**
	 * Render import page
	 */
	public static function render_import_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'aitc-ai-tools' ) );
		}
		?>

		<div class="wrap">
			<h1><?php esc_html_e( 'Import AI Tools from CSV', 'aitc-ai-tools' ); ?></h1>

			<?php if ( isset( $_GET['imported'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php printf( __( 'Successfully imported %d tools!', 'aitc-ai-tools' ), absint( $_GET['imported'] ) ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( isset( $_GET['error'] ) ) : ?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( urldecode( $_GET['error'] ) ); ?></p>
				</div>
			<?php endif; ?>

			<div class="card">
				<h2><?php esc_html_e( 'CSV Format', 'aitc-ai-tools' ); ?></h2>
				<p><?php esc_html_e( 'Your CSV file should contain the following columns (in any order):', 'aitc-ai-tools' ); ?></p>
				<pre style="background: #f0f0f1; padding: 15px; overflow-x: auto;">title,excerpt,content,official_url,pricing_page_url,pricing_model,price_type,price_single_amount,price_range_low,price_range_high,billing_unit,has_free_plan,has_free_trial,trial_days,pricing_tiers_json,editor_rating_value,editor_review_summary,editor_features,editor_pros,editor_cons,category_slug,use_case,platform,ai_pricing_model,ai_billing_unit</pre>

				<h3><?php esc_html_e( 'Field Notes:', 'aitc-ai-tools' ); ?></h3>
				<ul>
					<li><strong>title</strong> (required): Tool name</li>
					<li><strong>excerpt</strong>: Short description</li>
					<li><strong>content</strong>: Full description (HTML allowed)</li>
					<li><strong>pricing_model</strong>: free|freemium|paid|usage|one_time|enterprise|open_source</li>
					<li><strong>price_type</strong>: none|single|range|tiers</li>
					<li><strong>has_free_plan, has_free_trial</strong>: 1 or 0</li>
					<li><strong>pricing_tiers_json</strong>: JSON array of tier objects</li>
					<li><strong>editor_rating_value</strong>: 1.0-5.0</li>
					<li><strong>editor_features, editor_pros, editor_cons</strong>: Pipe-separated values (e.g., "Feature 1|Feature 2|Feature 3")</li>
					<li><strong>category_slug, use_case, platform, ai_pricing_model, ai_billing_unit</strong>: Comma-separated term slugs</li>
				</ul>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="aitc_import_csv">
				<?php wp_nonce_field( 'aitc_import_csv_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="csv_file"><?php esc_html_e( 'CSV File:', 'aitc-ai-tools' ); ?></label>
						</th>
						<td>
							<input type="file" id="csv_file" name="csv_file" accept=".csv" required>
							<p class="description"><?php esc_html_e( 'Select a CSV file to import.', 'aitc-ai-tools' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="update_existing"><?php esc_html_e( 'Update Existing:', 'aitc-ai-tools' ); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="update_existing" name="update_existing" value="1">
								<?php esc_html_e( 'Update existing tools with matching titles', 'aitc-ai-tools' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Import CSV', 'aitc-ai-tools' ); ?></button>
				</p>
			</form>
		</div>

		<?php
	}

	/**
	 * Handle CSV import
	 */
	public static function handle_import() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to perform this action.', 'aitc-ai-tools' ) );
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'aitc_import_csv_nonce' ) ) {
			wp_die( __( 'Invalid request.', 'aitc-ai-tools' ) );
		}

		if ( ! isset( $_FILES['csv_file'] ) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
			self::redirect_with_error( __( 'Please select a valid CSV file.', 'aitc-ai-tools' ) );
		}

		$update_existing = isset( $_POST['update_existing'] );
		$file = $_FILES['csv_file']['tmp_name'];
		$imported = 0;

		// Open CSV file
		if ( ( $handle = fopen( $file, 'r' ) ) !== false ) {
			// Get headers
			$headers = fgetcsv( $handle, 0, ',' );
			if ( ! $headers ) {
				fclose( $handle );
				self::redirect_with_error( __( 'Invalid CSV file format.', 'aitc-ai-tools' ) );
			}

			// Normalize headers
			$headers = array_map( 'trim', $headers );

			// Process rows
			while ( ( $row = fgetcsv( $handle, 0, ',' ) ) !== false ) {
				if ( count( $row ) !== count( $headers ) ) {
					continue; // Skip malformed rows
				}

				$data = array_combine( $headers, $row );
				if ( self::import_tool( $data, $update_existing ) ) {
					$imported++;
				}
			}

			fclose( $handle );
		}

		// Redirect with success message
		wp_redirect(
			add_query_arg(
				array(
					'post_type' => 'ai_tool',
					'page'      => 'aitc-import-csv',
					'imported'  => $imported,
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}

	/**
	 * Import single tool from CSV row
	 */
	private static function import_tool( $data, $update_existing = false ) {
		$title = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		if ( empty( $title ) ) {
			return false;
		}

		// Check if tool exists
		$existing_post = get_page_by_title( $title, OBJECT, 'ai_tool' );
		if ( $existing_post && ! $update_existing ) {
			return false; // Skip existing
		}

		$post_data = array(
			'post_title'   => $title,
			'post_excerpt' => isset( $data['excerpt'] ) ? sanitize_textarea_field( $data['excerpt'] ) : '',
			'post_content' => isset( $data['content'] ) ? wp_kses_post( $data['content'] ) : '',
			'post_type'    => 'ai_tool',
			'post_status'  => 'publish',
		);

		if ( $existing_post ) {
			$post_data['ID'] = $existing_post->ID;
			$post_id = wp_update_post( $post_data );
		} else {
			$post_id = wp_insert_post( $post_data );
		}

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			return false;
		}

		// Update meta fields
		$meta_fields = array(
			'official_url'          => 'esc_url_raw',
			'pricing_page_url'      => 'esc_url_raw',
			'pricing_model'         => 'sanitize_text_field',
			'price_type'            => 'sanitize_text_field',
			'price_single_amount'   => 'floatval',
			'price_range_low'       => 'floatval',
			'price_range_high'      => 'floatval',
			'billing_unit'          => 'sanitize_text_field',
			'trial_days'            => 'absint',
			'pricing_tiers_json'    => 'sanitize_textarea_field',
			'editor_rating_value'   => 'floatval',
			'editor_review_summary' => 'sanitize_textarea_field',
		);

		foreach ( $meta_fields as $field => $sanitize_callback ) {
			if ( isset( $data[ $field ] ) && $data[ $field ] !== '' ) {
				update_post_meta( $post_id, '_' . $field, call_user_func( $sanitize_callback, $data[ $field ] ) );
			}
		}

		// Boolean fields
		if ( isset( $data['has_free_plan'] ) ) {
			update_post_meta( $post_id, '_has_free_plan', $data['has_free_plan'] == 1 ? '1' : '0' );
		}
		if ( isset( $data['has_free_trial'] ) ) {
			update_post_meta( $post_id, '_has_free_trial', $data['has_free_trial'] == 1 ? '1' : '0' );
		}

		// Pipe-separated fields (features, pros, cons)
		$list_fields = array( 'editor_features', 'editor_pros', 'editor_cons' );
		foreach ( $list_fields as $field ) {
			if ( isset( $data[ $field ] ) && $data[ $field ] !== '' ) {
				$value = implode( "\n", array_map( 'trim', explode( '|', $data[ $field ] ) ) );
				update_post_meta( $post_id, '_' . $field, sanitize_textarea_field( $value ) );
			}
		}

		// Assign taxonomies
		$taxonomies = array(
			'category_slug'     => 'ai_tool_category',
			'use_case'          => 'ai_use_case',
			'platform'          => 'ai_platform',
			'ai_pricing_model'  => 'ai_pricing_model',
			'ai_billing_unit'   => 'ai_billing_unit',
		);

		foreach ( $taxonomies as $csv_field => $taxonomy ) {
			if ( isset( $data[ $csv_field ] ) && $data[ $csv_field ] !== '' ) {
				$terms = array_map( 'trim', explode( ',', $data[ $csv_field ] ) );
				$term_ids = array();

				foreach ( $terms as $term_slug ) {
					// Skip empty values
					if ( empty( $term_slug ) ) {
						continue;
					}

					// Skip purely numeric values - these are bad data (e.g., term IDs, column errors)
					if ( is_numeric( $term_slug ) ) {
						continue;
					}

					// Sanitize the slug to ensure it's valid
					$term_slug = sanitize_title( $term_slug );
					if ( empty( $term_slug ) ) {
						continue;
					}

					$term = term_exists( $term_slug, $taxonomy );
					if ( ! $term ) {
						// Create term if it doesn't exist
						// Generate readable name from slug (e.g., "image-design" -> "Image Design")
						$term_name = ucwords( str_replace( array( '-', '_' ), ' ', $term_slug ) );
						$term = wp_insert_term( $term_name, $taxonomy, array( 'slug' => $term_slug ) );
					}
					if ( ! is_wp_error( $term ) ) {
						$term_ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
					}
				}

				// Always set terms (replaces existing) - even empty array clears bad terms
				wp_set_object_terms( $post_id, array_map( 'intval', $term_ids ), $taxonomy );
			} elseif ( $update_existing ) {
				// When updating and CSV field is empty, clear any invalid numeric-named terms
				$existing_terms = get_the_terms( $post_id, $taxonomy );
				if ( $existing_terms && ! is_wp_error( $existing_terms ) ) {
					$valid_term_ids = array();
					foreach ( $existing_terms as $term ) {
						// Keep only terms with non-numeric names
						if ( ! is_numeric( $term->name ) && ! empty( trim( $term->name ) ) ) {
							$valid_term_ids[] = $term->term_id;
						}
					}
					// If we filtered out any bad terms, update
					if ( count( $valid_term_ids ) !== count( $existing_terms ) ) {
						wp_set_object_terms( $post_id, $valid_term_ids, $taxonomy );
					}
				}
			}
		}

		return true;
	}

	/**
	 * Redirect with error message
	 */
	private static function redirect_with_error( $message ) {
		wp_redirect(
			add_query_arg(
				array(
					'post_type' => 'ai_tool',
					'page'      => 'aitc-import-csv',
					'error'     => urlencode( $message ),
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}
}
