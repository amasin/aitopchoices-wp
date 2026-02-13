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

		$import_errors = get_transient( 'aitc_ai_tools_import_errors' );
		if ( $import_errors ) {
			delete_transient( 'aitc_ai_tools_import_errors' );
		}
		?>

		<div class="wrap">
			<h1><?php esc_html_e( 'Import AI Tools from CSV', 'aitc-ai-tools' ); ?></h1>

			<?php if ( isset( $_GET['imported'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php printf( __( 'Successfully imported %d tools.', 'aitc-ai-tools' ), absint( $_GET['imported'] ) ); ?>
						<?php if ( isset( $_GET['skipped'] ) && absint( $_GET['skipped'] ) > 0 ) : ?>
							<?php printf( __( ' %d rows skipped (see warnings below).', 'aitc-ai-tools' ), absint( $_GET['skipped'] ) ); ?>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( isset( $_GET['error'] ) ) : ?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( urldecode( $_GET['error'] ) ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $import_errors ) ) : ?>
				<div class="notice notice-warning is-dismissible">
					<p><?php esc_html_e( 'Some rows were skipped or had issues:', 'aitc-ai-tools' ); ?></p>
					<ul>
						<?php foreach ( $import_errors as $error ) : ?>
							<li><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="card">
				<h2><?php esc_html_e( 'CSV Format', 'aitc-ai-tools' ); ?></h2>
				<p><?php esc_html_e( 'Your CSV file should contain the following columns (in any order):', 'aitc-ai-tools' ); ?></p>
				<pre style="background: #f0f0f1; padding: 15px; overflow-x: auto;">tool_slug,tool_name,overview,key_features,best_use_cases,pricing_model,free_plan_available,pricing_notes,pricing_url,pros,cons,alternatives,faqs,excerpt,content,official_url,pricing_page_url,price_type,price_single_amount,price_range_low,price_range_high,billing_unit,has_free_plan,has_free_trial,trial_days,pricing_tiers_json,editor_rating_value,editor_review_summary,editor_features,editor_pros,editor_cons,category_slug,use_case,platform,ai_pricing_model,ai_billing_unit</pre>

				<h3><?php esc_html_e( 'Field Notes:', 'aitc-ai-tools' ); ?></h3>
				<ul>
					<li><strong>tool_slug</strong> (required): Unique slug for the tool</li>
					<li><strong>tool_name</strong> (required): Tool name (post title)</li>
					<li><strong>overview</strong>: Short overview (HTML allowed)</li>
					<li><strong>key_features, best_use_cases, pros, cons</strong>: Pipe-separated, comma-separated, or newline-separated values</li>
					<li><strong>alternatives</strong>: Pipe-separated or comma-separated tool names/slugs (e.g., "SEMrush | Moz Pro" or "semrush,moz-pro")</li>
					<li><strong>faqs</strong>: JSON array [{"q":"...","a":"..."}] <em>or</em> pipe-separated "Question? Answer | Question? Answer"</li>
					<li><strong>free_plan_available</strong>: 1/0, true/false, yes/no</li>
					<li><strong>pricing_url</strong>: Full pricing page URL</li>
					<li><strong>category_slug, use_case, platform, ai_pricing_model, ai_billing_unit</strong>: Comma-separated term slugs (auto-creates missing terms)</li>
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
								<?php esc_html_e( 'Update existing tools with matching slugs', 'aitc-ai-tools' ); ?>
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
			$upload_errors = array(
				UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload_max_filesize limit.',
				UPLOAD_ERR_FORM_SIZE  => 'File exceeds form MAX_FILE_SIZE.',
				UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
				UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
				UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder.',
				UPLOAD_ERR_CANT_WRITE => 'Server failed to write file to disk.',
			);
			$error_code = isset( $_FILES['csv_file']['error'] ) ? $_FILES['csv_file']['error'] : UPLOAD_ERR_NO_FILE;
			$error_msg  = isset( $upload_errors[ $error_code ] ) ? $upload_errors[ $error_code ] : 'Unknown upload error.';
			self::redirect_with_error( $error_msg );
		}

		$update_existing       = isset( $_POST['update_existing'] );
		$overwrite_empty_fields = get_option( 'aitc_ai_tools_overwrite_empty_fields', '0' ) === '1';
		$file     = $_FILES['csv_file']['tmp_name'];
		$imported = 0;
		$skipped  = 0;
		$errors   = array();

		// Convert file to UTF-8 if needed (handles Excel/Windows-1252 encoding)
		$file = self::ensure_utf8( $file );

		// Enable auto-detection of line endings (Mac/Windows/Unix)
		$prev_detect = ini_get( 'auto_detect_line_endings' );
		ini_set( 'auto_detect_line_endings', true );

		// Open CSV file
		if ( ( $handle = fopen( $file, 'r' ) ) !== false ) {
			// Get headers
			$headers = fgetcsv( $handle, 0, ',' );
			if ( ! $headers ) {
				fclose( $handle );
				self::redirect_with_error( __( 'Invalid CSV file format — could not read headers.', 'aitc-ai-tools' ) );
			}

			// Strip UTF-8 BOM from first header if present
			$headers[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $headers[0] );

			// Normalize headers (trim whitespace and lowercase)
			$headers     = array_map( 'trim', $headers );
			$header_count = count( $headers );

			// Validate required columns exist
			if ( ! in_array( 'tool_slug', $headers, true ) || ! in_array( 'tool_name', $headers, true ) ) {
				fclose( $handle );
				self::redirect_with_error( __( 'CSV must contain "tool_slug" and "tool_name" columns.', 'aitc-ai-tools' ) );
			}

			// Process rows
			$row_number = 1;
			while ( ( $row = fgetcsv( $handle, 0, ',' ) ) !== false ) {
				$row_number++;

				// Skip completely empty rows
				if ( count( $row ) === 1 && ( $row[0] === null || trim( $row[0] ) === '' ) ) {
					continue;
				}

				$row_count = count( $row );

				// Lenient column handling: pad short rows, trim extra columns
				if ( $row_count < $header_count ) {
					$row = array_pad( $row, $header_count, '' );
				} elseif ( $row_count > $header_count ) {
					// Only trim if extra columns are all empty (trailing commas)
					$extra = array_slice( $row, $header_count );
					$extra_non_empty = array_filter( $extra, function( $v ) {
						return trim( $v ) !== '';
					} );
					if ( ! empty( $extra_non_empty ) ) {
						$errors[] = sprintf(
							__( 'Row %d: has %d columns but header has %d. Extra non-empty data may be lost.', 'aitc-ai-tools' ),
							$row_number, $row_count, $header_count
						);
					}
					$row = array_slice( $row, 0, $header_count );
				}

				$data = array_combine( $headers, $row );
				if ( self::import_tool( $data, $update_existing, $overwrite_empty_fields, $errors, $row_number ) ) {
					$imported++;
				} else {
					$skipped++;
				}
			}

			fclose( $handle );
		} else {
			self::redirect_with_error( __( 'Could not open uploaded file.', 'aitc-ai-tools' ) );
		}

		// Restore line ending detection setting
		ini_set( 'auto_detect_line_endings', $prev_detect );

		if ( ! empty( $errors ) ) {
			set_transient( 'aitc_ai_tools_import_errors', $errors, 5 * MINUTE_IN_SECONDS );
		}

		// Redirect with success message
		wp_redirect(
			add_query_arg(
				array(
					'post_type' => 'ai_tool',
					'page'      => 'aitc-import-csv',
					'imported'  => $imported,
					'skipped'   => $skipped,
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}

	/**
	 * Import single tool from CSV row
	 */
	private static function import_tool( $data, $update_existing = false, $overwrite_empty_fields = false, &$errors = array(), $row_number = 0 ) {
		$slug = isset( $data['tool_slug'] ) ? sanitize_title( $data['tool_slug'] ) : '';
		$title = isset( $data['tool_name'] ) ? sanitize_text_field( $data['tool_name'] ) : '';

		if ( empty( $slug ) ) {
			self::add_import_error( $errors, $row_number, __( 'Missing tool_slug.', 'aitc-ai-tools' ) );
			return false;
		}
		if ( empty( $title ) ) {
			self::add_import_error( $errors, $row_number, __( 'Missing tool_name.', 'aitc-ai-tools' ) );
			return false;
		}

		// Check if tool exists by slug
		$existing_post = get_page_by_path( $slug, OBJECT, 'ai_tool' );
		if ( $existing_post && ! $update_existing ) {
			self::add_import_error( $errors, $row_number, __( 'Tool already exists and update is disabled.', 'aitc-ai-tools' ) );
			return false;
		}

		$post_data = array(
			'post_title'   => $title,
			'post_name'    => $slug,
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
			self::add_import_error( $errors, $row_number, __( 'Failed to save post.', 'aitc-ai-tools' ) );
			return false;
		}

		self::update_string_meta( $post_id, $data, 'overview', '_aitc_overview', 'wp_kses_post', $overwrite_empty_fields );
		self::update_string_meta( $post_id, $data, 'pricing_model', '_aitc_pricing_model', 'sanitize_text_field', $overwrite_empty_fields );
		self::update_string_meta( $post_id, $data, 'pricing_notes', '_aitc_pricing_notes', 'wp_kses_post', $overwrite_empty_fields );
		self::update_string_meta( $post_id, $data, 'pricing_url', '_aitc_pricing_url', 'esc_url_raw', $overwrite_empty_fields );

		self::update_json_list_meta( $post_id, $data, 'key_features', '_aitc_key_features', $overwrite_empty_fields );
		self::update_json_list_meta( $post_id, $data, 'best_use_cases', '_aitc_best_use_cases', $overwrite_empty_fields );
		self::update_json_list_meta( $post_id, $data, 'pros', '_aitc_pros', $overwrite_empty_fields );
		self::update_json_list_meta( $post_id, $data, 'cons', '_aitc_cons', $overwrite_empty_fields );

		self::update_json_alternatives_meta( $post_id, $data, 'alternatives', '_aitc_alternatives', $overwrite_empty_fields );
		self::update_json_faqs_meta( $post_id, $data, 'faqs', '_aitc_faqs', $overwrite_empty_fields, $errors, $row_number );

		self::update_bool_meta( $post_id, $data, 'free_plan_available', '_aitc_free_plan_available', $overwrite_empty_fields, $errors, $row_number );

		// Legacy meta fields (if provided)
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
			self::update_string_meta( $post_id, $data, $field, '_' . $field, $sanitize_callback, $overwrite_empty_fields );
		}

		// Legacy boolean fields
		if ( array_key_exists( 'has_free_plan', $data ) ) {
			self::update_bool_meta( $post_id, $data, 'has_free_plan', '_has_free_plan', $overwrite_empty_fields, $errors, $row_number );
		}
		if ( array_key_exists( 'has_free_trial', $data ) ) {
			self::update_bool_meta( $post_id, $data, 'has_free_trial', '_has_free_trial', $overwrite_empty_fields, $errors, $row_number );
		}

		// Legacy pipe-separated fields
		$list_fields = array( 'editor_features', 'editor_pros', 'editor_cons' );
		foreach ( $list_fields as $field ) {
			if ( array_key_exists( $field, $data ) ) {
				$value = self::parse_list_field( $data[ $field ] );
				if ( ! empty( $value ) ) {
					update_post_meta( $post_id, '_' . $field, sanitize_textarea_field( implode( "\n", $value ) ) );
				} elseif ( $overwrite_empty_fields ) {
					delete_post_meta( $post_id, '_' . $field );
				}
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

	private static function update_string_meta( $post_id, $data, $field, $meta_key, $sanitize_callback, $overwrite_empty_fields ) {
		if ( ! array_key_exists( $field, $data ) ) {
			return;
		}

		$value = $data[ $field ];
		if ( $value !== '' ) {
			update_post_meta( $post_id, $meta_key, call_user_func( $sanitize_callback, $value ) );
		} elseif ( $overwrite_empty_fields ) {
			delete_post_meta( $post_id, $meta_key );
		}
	}

	private static function update_bool_meta( $post_id, $data, $field, $meta_key, $overwrite_empty_fields, &$errors, $row_number ) {
		if ( ! array_key_exists( $field, $data ) ) {
			return;
		}

		$raw = trim( $data[ $field ] );
		if ( $raw === '' ) {
			if ( $overwrite_empty_fields ) {
				delete_post_meta( $post_id, $meta_key );
			}
			return;
		}

		$parsed = self::parse_bool( $raw );
		if ( null === $parsed ) {
			self::add_import_error( $errors, $row_number, sprintf( __( 'Invalid boolean value for %s.', 'aitc-ai-tools' ), $field ) );
			return;
		}

		update_post_meta( $post_id, $meta_key, $parsed ? '1' : '0' );
	}

	private static function update_json_list_meta( $post_id, $data, $field, $meta_key, $overwrite_empty_fields ) {
		if ( ! array_key_exists( $field, $data ) ) {
			return;
		}

		$list = self::parse_list_field( $data[ $field ] );
		if ( ! empty( $list ) ) {
			update_post_meta( $post_id, $meta_key, wp_json_encode( array_values( $list ) ) );
		} elseif ( $overwrite_empty_fields ) {
			delete_post_meta( $post_id, $meta_key );
		}
	}

	private static function update_json_alternatives_meta( $post_id, $data, $field, $meta_key, $overwrite_empty_fields ) {
		if ( ! array_key_exists( $field, $data ) ) {
			return;
		}

		$raw = trim( $data[ $field ] );
		if ( $raw === '' ) {
			if ( $overwrite_empty_fields ) {
				delete_post_meta( $post_id, $meta_key );
			}
			return;
		}

		// Support both pipe-separated and comma-separated values
		// Detect separator: if pipes are present, prefer pipe; otherwise use comma
		if ( strpos( $raw, '|' ) !== false ) {
			$parts = array_map( 'trim', explode( '|', $raw ) );
		} else {
			$parts = array_map( 'trim', explode( ',', $raw ) );
		}

		$slugs = array();
		foreach ( $parts as $part ) {
			$slug = sanitize_title( $part );
			if ( $slug ) {
				$slugs[] = $slug;
			}
		}

		if ( ! empty( $slugs ) ) {
			update_post_meta( $post_id, $meta_key, wp_json_encode( array_values( $slugs ) ) );
		} elseif ( $overwrite_empty_fields ) {
			delete_post_meta( $post_id, $meta_key );
		}
	}

	private static function update_json_faqs_meta( $post_id, $data, $field, $meta_key, $overwrite_empty_fields, &$errors, $row_number ) {
		if ( ! array_key_exists( $field, $data ) ) {
			return;
		}

		$raw = trim( $data[ $field ] );
		if ( $raw === '' ) {
			if ( $overwrite_empty_fields ) {
				delete_post_meta( $post_id, $meta_key );
			}
			return;
		}

		$faqs = array();

		// Try JSON format first: [{"q":"...","a":"..."}]
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) && ! empty( $decoded ) ) {
			foreach ( $decoded as $faq ) {
				if ( ! is_array( $faq ) ) {
					continue;
				}
				$question = isset( $faq['q'] ) ? trim( $faq['q'] ) : '';
				$answer   = isset( $faq['a'] ) ? trim( $faq['a'] ) : '';
				if ( $question && $answer ) {
					$faqs[] = array( 'q' => $question, 'a' => $answer );
				}
			}
		}

		// Fallback: pipe-separated "Question? Answer | Question? Answer" format
		if ( empty( $faqs ) && strpos( $raw, '|' ) !== false ) {
			$parts = array_map( 'trim', explode( '|', $raw ) );
			foreach ( $parts as $part ) {
				if ( empty( $part ) ) {
					continue;
				}
				// Split on first "? " to separate question from answer
				$qmark_pos = strpos( $part, '? ' );
				if ( $qmark_pos !== false ) {
					$question = trim( substr( $part, 0, $qmark_pos + 1 ) ); // include the ?
					$answer   = trim( substr( $part, $qmark_pos + 2 ) );
					if ( $question && $answer ) {
						$faqs[] = array( 'q' => $question, 'a' => $answer );
					}
				}
			}
		}

		// Last fallback: pipe-separated items stored as simple Q&A pairs
		if ( empty( $faqs ) && ! empty( $raw ) ) {
			self::add_import_error( $errors, $row_number, __( 'Could not parse FAQs. Use JSON [{"q":"...","a":"..."}] or pipe-separated "Question? Answer | Question? Answer" format.', 'aitc-ai-tools' ) );
			return;
		}

		if ( ! empty( $faqs ) ) {
			update_post_meta( $post_id, $meta_key, wp_json_encode( $faqs ) );
		}
	}

	private static function parse_list_field( $value ) {
		$value = trim( (string) $value );
		if ( $value === '' ) {
			return array();
		}

		// Support pipe, newline, or comma as separators
		// Prefer pipe/newline first; fall back to comma if no pipes or newlines found
		if ( preg_match( "/\||\r\n|\n/", $value ) ) {
			$parts = preg_split( "/\r\n|\n|\|/", $value );
		} else {
			$parts = explode( ',', $value );
		}

		$parts = array_map( 'trim', $parts );
		$parts = array_filter( $parts, function( $part ) {
			return $part !== '';
		} );

		return array_values( $parts );
	}

	private static function parse_bool( $value ) {
		$value = strtolower( trim( (string) $value ) );
		$truthy = array( '1', 'true', 'yes' );
		$falsy  = array( '0', 'false', 'no' );

		if ( in_array( $value, $truthy, true ) ) {
			return true;
		}
		if ( in_array( $value, $falsy, true ) ) {
			return false;
		}

		return null;
	}

	private static function add_import_error( &$errors, $row_number, $message ) {
		if ( $row_number ) {
			$errors[] = sprintf( __( 'Row %d: %s', 'aitc-ai-tools' ), $row_number, $message );
		} else {
			$errors[] = $message;
		}
	}

	/**
	 * Ensure the CSV file is valid UTF-8.
	 *
	 * Excel and other Windows tools often save CSVs in Windows-1252 encoding.
	 * WordPress sanitize_text_field() returns '' for the entire string if it
	 * contains even one invalid UTF-8 byte (like Windows-1252 en-dash 0x96).
	 * This method detects the encoding and converts to UTF-8 if necessary.
	 */
	private static function ensure_utf8( $file ) {
		$content = file_get_contents( $file );
		if ( false === $content ) {
			return $file;
		}

		// Strip UTF-8 BOM if present
		if ( substr( $content, 0, 3 ) === "\xEF\xBB\xBF" ) {
			$content = substr( $content, 3 );
		}

		// Check if already valid UTF-8
		if ( function_exists( 'mb_check_encoding' ) && mb_check_encoding( $content, 'UTF-8' ) ) {
			// Already valid UTF-8 — still write back in case BOM was stripped
			file_put_contents( $file, $content );
			return $file;
		}

		// Try to detect and convert encoding
		if ( function_exists( 'mb_detect_encoding' ) ) {
			$detected = mb_detect_encoding( $content, array( 'UTF-8', 'Windows-1252', 'ISO-8859-1' ), true );
			if ( $detected && $detected !== 'UTF-8' ) {
				$content = mb_convert_encoding( $content, 'UTF-8', $detected );
			}
		} elseif ( function_exists( 'iconv' ) ) {
			// Fallback: assume Windows-1252 (superset of ISO-8859-1, common on Windows)
			$converted = @iconv( 'Windows-1252', 'UTF-8//TRANSLIT//IGNORE', $content );
			if ( $converted !== false ) {
				$content = $converted;
			}
		}

		// Write converted content back to temp file
		file_put_contents( $file, $content );
		return $file;
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
