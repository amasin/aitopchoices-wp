<?php
/**
 * Meta boxes for AI Tool post type
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AITC_Meta_Boxes {

	/**
	 * Initialize hooks
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_ai_tool', array( __CLASS__, 'save_tool_content_meta' ), 10, 2 );
		add_action( 'save_post_ai_tool', array( __CLASS__, 'save_pricing_meta' ), 10, 2 );
		add_action( 'save_post_ai_tool', array( __CLASS__, 'save_editorial_meta' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Read a meta value with _aitc_ prefix preferred, falling back to legacy _ prefix.
	 */
	private static function get_meta( $post_id, $key ) {
		$value = get_post_meta( $post_id, '_aitc_' . $key, true );
		if ( ! $value ) {
			$value = get_post_meta( $post_id, '_' . $key, true );
		}
		return $value;
	}

	/**
	 * Save a meta value to both _aitc_ and legacy _ prefixed keys.
	 */
	private static function set_meta( $post_id, $key, $value ) {
		update_post_meta( $post_id, '_aitc_' . $key, $value );
		update_post_meta( $post_id, '_' . $key, $value );
	}

	/**
	 * Delete a meta value from both prefixed keys.
	 */
	private static function delete_meta( $post_id, $key ) {
		delete_post_meta( $post_id, '_aitc_' . $key );
		delete_post_meta( $post_id, '_' . $key );
	}

	/**
	 * Decode a JSON array meta value into a newline-separated string for textarea display.
	 */
	private static function json_to_lines( $value ) {
		if ( empty( $value ) ) {
			return '';
		}
		$decoded = json_decode( $value, true );
		if ( is_array( $decoded ) ) {
			// Repair broken Unicode escapes (wp_unslash strips \ from \uXXXX)
			$decoded = array_map( function( $item ) {
				if ( is_string( $item ) ) {
					return preg_replace_callback( '/\bu([0-9a-fA-F]{4})\b/', function( $m ) {
						$char = json_decode( '"\\u' . $m[1] . '"' );
						return ( $char !== null ) ? $char : $m[0];
					}, $item );
				}
				return $item;
			}, $decoded );
			return implode( "\n", $decoded );
		}
		// Already plain text (newline-separated)
		return $value;
	}

	/**
	 * Encode a newline-separated textarea value into a JSON array string.
	 */
	private static function lines_to_json( $value ) {
		$lines = preg_split( "/\r\n|\n|\r/", trim( $value ) );
		$lines = array_map( 'trim', $lines );
		$lines = array_filter( $lines, function( $line ) {
			return $line !== '';
		} );
		$lines = array_values( $lines );
		if ( empty( $lines ) ) {
			return '';
		}
		return wp_json_encode( $lines, JSON_UNESCAPED_UNICODE );
	}

	/**
	 * Add meta boxes
	 */
	public static function add_meta_boxes() {
		add_meta_box(
			'aitc_tool_content_meta',
			__( 'Tool Content', 'aitc-ai-tools' ),
			array( __CLASS__, 'render_tool_content_meta_box' ),
			'ai_tool',
			'normal',
			'high'
		);

		add_meta_box(
			'aitc_pricing_meta',
			__( 'Pricing Information', 'aitc-ai-tools' ),
			array( __CLASS__, 'render_pricing_meta_box' ),
			'ai_tool',
			'normal',
			'high'
		);

		add_meta_box(
			'aitc_editorial_meta',
			__( 'Editorial Review', 'aitc-ai-tools' ),
			array( __CLASS__, 'render_editorial_meta_box' ),
			'ai_tool',
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue admin scripts
	 */
	public static function enqueue_admin_scripts( $hook ) {
		if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
			global $post;
			if ( $post && 'ai_tool' === $post->post_type ) {
				wp_enqueue_style( 'aitc-admin', AITC_AI_TOOLS_URL . 'assets/css/admin.css', array(), AITC_AI_TOOLS_VERSION );
				wp_enqueue_script( 'aitc-admin', AITC_AI_TOOLS_URL . 'assets/js/admin.js', array( 'jquery' ), AITC_AI_TOOLS_VERSION, true );
			}
		}
	}

	/**
	 * Render tool content meta box (overview, features, use cases, pros, cons, alternatives, FAQs)
	 */
	public static function render_tool_content_meta_box( $post ) {
		wp_nonce_field( 'aitc_tool_content_nonce', 'aitc_tool_content_nonce' );

		$overview       = self::get_meta( $post->ID, 'overview' );
		$key_features   = self::json_to_lines( self::get_meta( $post->ID, 'key_features' ) );
		$best_use_cases = self::json_to_lines( self::get_meta( $post->ID, 'best_use_cases' ) );
		$pros           = self::json_to_lines( self::get_meta( $post->ID, 'pros' ) );
		$cons           = self::json_to_lines( self::get_meta( $post->ID, 'cons' ) );
		$pricing_notes  = self::get_meta( $post->ID, 'pricing_notes' );

		// Alternatives: JSON array of slugs → comma-separated display
		$alternatives_raw = self::get_meta( $post->ID, 'alternatives' );
		$alternatives     = '';
		if ( $alternatives_raw ) {
			$decoded = json_decode( $alternatives_raw, true );
			if ( is_array( $decoded ) ) {
				$alternatives = implode( ', ', $decoded );
			} else {
				$alternatives = $alternatives_raw;
			}
		}

		// FAQs: JSON array of {q,a}
		$faqs_raw = self::get_meta( $post->ID, 'faqs' );
		$faqs     = array();
		if ( $faqs_raw ) {
			$decoded = json_decode( $faqs_raw, true );
			if ( is_array( $decoded ) ) {
				$faqs = $decoded;
			}
		}
		?>

		<div class="aitc-meta-box">
			<p>
				<label for="aitc_overview"><strong><?php esc_html_e( 'Overview:', 'aitc-ai-tools' ); ?></strong></label><br>
				<textarea id="aitc_overview" name="aitc_overview" rows="4" class="widefat"><?php echo esc_textarea( $overview ); ?></textarea>
				<span class="description"><?php esc_html_e( 'Short overview of the tool. HTML allowed.', 'aitc-ai-tools' ); ?></span>
			</p>

			<p>
				<label for="aitc_key_features"><strong><?php esc_html_e( 'Key Features (one per line):', 'aitc-ai-tools' ); ?></strong></label><br>
				<textarea id="aitc_key_features" name="aitc_key_features" rows="6" class="widefat"><?php echo esc_textarea( $key_features ); ?></textarea>
			</p>

			<p>
				<label for="aitc_best_use_cases"><strong><?php esc_html_e( 'Best Use Cases (one per line):', 'aitc-ai-tools' ); ?></strong></label><br>
				<textarea id="aitc_best_use_cases" name="aitc_best_use_cases" rows="4" class="widefat"><?php echo esc_textarea( $best_use_cases ); ?></textarea>
			</p>

			<p>
				<label for="aitc_pros"><strong><?php esc_html_e( 'Pros (one per line):', 'aitc-ai-tools' ); ?></strong></label><br>
				<textarea id="aitc_pros" name="aitc_pros" rows="5" class="widefat"><?php echo esc_textarea( $pros ); ?></textarea>
			</p>

			<p>
				<label for="aitc_cons"><strong><?php esc_html_e( 'Cons (one per line):', 'aitc-ai-tools' ); ?></strong></label><br>
				<textarea id="aitc_cons" name="aitc_cons" rows="5" class="widefat"><?php echo esc_textarea( $cons ); ?></textarea>
			</p>

			<p>
				<label for="aitc_alternatives"><strong><?php esc_html_e( 'Alternatives (comma-separated slugs):', 'aitc-ai-tools' ); ?></strong></label><br>
				<input type="text" id="aitc_alternatives" name="aitc_alternatives" value="<?php echo esc_attr( $alternatives ); ?>" class="widefat" placeholder="semrush, moz-pro, ubersuggest">
				<span class="description"><?php esc_html_e( 'Tool slugs, comma-separated. These link to other tools in the directory.', 'aitc-ai-tools' ); ?></span>
			</p>

			<p>
				<label for="aitc_pricing_notes"><strong><?php esc_html_e( 'Pricing Notes:', 'aitc-ai-tools' ); ?></strong></label><br>
				<textarea id="aitc_pricing_notes" name="aitc_pricing_notes" rows="3" class="widefat"><?php echo esc_textarea( $pricing_notes ); ?></textarea>
				<span class="description"><?php esc_html_e( 'e.g. "Starts at $99/month. Pricing varies by plan limits."', 'aitc-ai-tools' ); ?></span>
			</p>

			<hr>
			<h4><?php esc_html_e( 'FAQs', 'aitc-ai-tools' ); ?></h4>
			<p class="description"><?php esc_html_e( 'Add frequently asked questions about this tool.', 'aitc-ai-tools' ); ?></p>

			<div id="aitc-faqs-container">
				<?php if ( ! empty( $faqs ) ) : ?>
					<?php foreach ( $faqs as $i => $faq ) : ?>
						<div class="aitc-faq-row" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; background: #fafafa;">
							<p>
								<label><strong><?php esc_html_e( 'Question:', 'aitc-ai-tools' ); ?></strong></label><br>
								<input type="text" name="aitc_faq_q[]" value="<?php echo esc_attr( $faq['q'] ?? '' ); ?>" class="widefat">
							</p>
							<p>
								<label><strong><?php esc_html_e( 'Answer:', 'aitc-ai-tools' ); ?></strong></label><br>
								<textarea name="aitc_faq_a[]" rows="2" class="widefat"><?php echo esc_textarea( $faq['a'] ?? '' ); ?></textarea>
							</p>
							<button type="button" class="button aitc-remove-faq"><?php esc_html_e( 'Remove', 'aitc-ai-tools' ); ?></button>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<button type="button" class="button" id="aitc-add-faq"><?php esc_html_e( '+ Add FAQ', 'aitc-ai-tools' ); ?></button>

			<script>
			jQuery(function($) {
				$('#aitc-add-faq').on('click', function() {
					var html = '<div class="aitc-faq-row" style="border:1px solid #ddd;padding:10px;margin-bottom:10px;background:#fafafa;">'
						+ '<p><label><strong><?php echo esc_js( __( 'Question:', 'aitc-ai-tools' ) ); ?></strong></label><br>'
						+ '<input type="text" name="aitc_faq_q[]" value="" class="widefat"></p>'
						+ '<p><label><strong><?php echo esc_js( __( 'Answer:', 'aitc-ai-tools' ) ); ?></strong></label><br>'
						+ '<textarea name="aitc_faq_a[]" rows="2" class="widefat"></textarea></p>'
						+ '<button type="button" class="button aitc-remove-faq"><?php echo esc_js( __( 'Remove', 'aitc-ai-tools' ) ); ?></button>'
						+ '</div>';
					$('#aitc-faqs-container').append(html);
				});
				$(document).on('click', '.aitc-remove-faq', function() {
					$(this).closest('.aitc-faq-row').remove();
				});
			});
			</script>
		</div>

		<?php
	}

	/**
	 * Save tool content meta
	 */
	public static function save_tool_content_meta( $post_id, $post ) {
		if ( ! isset( $_POST['aitc_tool_content_nonce'] ) || ! wp_verify_nonce( $_POST['aitc_tool_content_nonce'], 'aitc_tool_content_nonce' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Overview (HTML allowed)
		if ( isset( $_POST['aitc_overview'] ) ) {
			$value = wp_kses_post( $_POST['aitc_overview'] );
			if ( $value ) {
				self::set_meta( $post_id, 'overview', $value );
			} else {
				self::delete_meta( $post_id, 'overview' );
			}
		}

		// JSON list fields (one per line → JSON array)
		$list_fields = array( 'key_features', 'best_use_cases', 'pros', 'cons' );
		foreach ( $list_fields as $field ) {
			$post_key = 'aitc_' . $field;
			if ( isset( $_POST[ $post_key ] ) ) {
				$json = self::lines_to_json( $_POST[ $post_key ] );
				if ( $json ) {
					self::set_meta( $post_id, $field, $json );
				} else {
					self::delete_meta( $post_id, $field );
				}
			}
		}

		// Alternatives (comma-separated → JSON array of slugs)
		if ( isset( $_POST['aitc_alternatives'] ) ) {
			$raw   = sanitize_text_field( $_POST['aitc_alternatives'] );
			$parts = array_map( 'trim', explode( ',', $raw ) );
			$slugs = array();
			foreach ( $parts as $part ) {
				$slug = sanitize_title( $part );
				if ( $slug ) {
					$slugs[] = $slug;
				}
			}
			if ( ! empty( $slugs ) ) {
				self::set_meta( $post_id, 'alternatives', wp_json_encode( array_values( $slugs ), JSON_UNESCAPED_UNICODE ) );
			} else {
				self::delete_meta( $post_id, 'alternatives' );
			}
		}

		// Pricing notes (HTML allowed)
		if ( isset( $_POST['aitc_pricing_notes'] ) ) {
			$value = wp_kses_post( $_POST['aitc_pricing_notes'] );
			if ( $value ) {
				self::set_meta( $post_id, 'pricing_notes', $value );
			} else {
				self::delete_meta( $post_id, 'pricing_notes' );
			}
		}

		// FAQs (repeater → JSON array of {q,a})
		if ( isset( $_POST['aitc_faq_q'] ) && isset( $_POST['aitc_faq_a'] ) ) {
			$questions = $_POST['aitc_faq_q'];
			$answers   = $_POST['aitc_faq_a'];
			$faqs      = array();

			for ( $i = 0; $i < count( $questions ); $i++ ) {
				$q = sanitize_text_field( trim( $questions[ $i ] ) );
				$a = sanitize_textarea_field( trim( $answers[ $i ] ) );
				if ( $q && $a ) {
					$faqs[] = array( 'q' => $q, 'a' => $a );
				}
			}

			if ( ! empty( $faqs ) ) {
				self::set_meta( $post_id, 'faqs', wp_json_encode( $faqs, JSON_UNESCAPED_UNICODE ) );
			} else {
				self::delete_meta( $post_id, 'faqs' );
			}
		}
	}

	/**
	 * Render pricing meta box
	 */
	public static function render_pricing_meta_box( $post ) {
		wp_nonce_field( 'aitc_pricing_meta_nonce', 'aitc_pricing_meta_nonce' );

		$pricing_model      = self::get_meta( $post->ID, 'pricing_model' );
		$price_type         = get_post_meta( $post->ID, '_price_type', true );
		$price_single       = get_post_meta( $post->ID, '_price_single_amount', true );
		$price_range_low    = get_post_meta( $post->ID, '_price_range_low', true );
		$price_range_high   = get_post_meta( $post->ID, '_price_range_high', true );
		$billing_unit       = get_post_meta( $post->ID, '_billing_unit', true );
		$has_free_plan      = get_post_meta( $post->ID, '_has_free_plan', true );
		if ( ! $has_free_plan ) {
			$has_free_plan = self::get_meta( $post->ID, 'free_plan_available' );
		}
		$has_free_trial     = get_post_meta( $post->ID, '_has_free_trial', true );
		$trial_days         = get_post_meta( $post->ID, '_trial_days', true );
		$pricing_page_url   = get_post_meta( $post->ID, '_pricing_page_url', true );
		if ( ! $pricing_page_url ) {
			$pricing_page_url = self::get_meta( $post->ID, 'pricing_url' );
		}
		$official_url       = get_post_meta( $post->ID, '_official_url', true );
		$pricing_tiers_json = get_post_meta( $post->ID, '_pricing_tiers_json', true );
		?>

		<div class="aitc-meta-box">
			<p>
				<label for="official_url"><strong><?php esc_html_e( 'Official URL:', 'aitc-ai-tools' ); ?></strong></label><br>
				<input type="url" id="official_url" name="official_url" value="<?php echo esc_url( $official_url ); ?>" class="widefat" placeholder="https://example.com">
			</p>

			<p>
				<label for="pricing_page_url"><strong><?php esc_html_e( 'Pricing Page URL:', 'aitc-ai-tools' ); ?></strong></label><br>
				<input type="url" id="pricing_page_url" name="pricing_page_url" value="<?php echo esc_url( $pricing_page_url ); ?>" class="widefat" placeholder="https://example.com/pricing">
			</p>

			<p>
				<label for="pricing_model"><strong><?php esc_html_e( 'Pricing Model:', 'aitc-ai-tools' ); ?></strong></label><br>
				<select id="pricing_model" name="pricing_model" class="widefat">
					<option value=""><?php esc_html_e( '-- Select --', 'aitc-ai-tools' ); ?></option>
					<option value="free" <?php selected( $pricing_model, 'free' ); ?>><?php esc_html_e( 'Free', 'aitc-ai-tools' ); ?></option>
					<option value="freemium" <?php selected( $pricing_model, 'freemium' ); ?>><?php esc_html_e( 'Freemium', 'aitc-ai-tools' ); ?></option>
					<option value="paid" <?php selected( $pricing_model, 'paid' ); ?>><?php esc_html_e( 'Paid', 'aitc-ai-tools' ); ?></option>
					<option value="usage" <?php selected( $pricing_model, 'usage' ); ?>><?php esc_html_e( 'Usage-based', 'aitc-ai-tools' ); ?></option>
					<option value="one_time" <?php selected( $pricing_model, 'one_time' ); ?>><?php esc_html_e( 'One-time', 'aitc-ai-tools' ); ?></option>
					<option value="enterprise" <?php selected( $pricing_model, 'enterprise' ); ?>><?php esc_html_e( 'Enterprise', 'aitc-ai-tools' ); ?></option>
					<option value="open_source" <?php selected( $pricing_model, 'open_source' ); ?>><?php esc_html_e( 'Open Source', 'aitc-ai-tools' ); ?></option>
				</select>
			</p>

			<p>
				<label for="price_type"><strong><?php esc_html_e( 'Price Type:', 'aitc-ai-tools' ); ?></strong></label><br>
				<select id="price_type" name="price_type" class="widefat">
					<option value="none" <?php selected( $price_type, 'none' ); ?>><?php esc_html_e( 'None', 'aitc-ai-tools' ); ?></option>
					<option value="single" <?php selected( $price_type, 'single' ); ?>><?php esc_html_e( 'Single Price', 'aitc-ai-tools' ); ?></option>
					<option value="range" <?php selected( $price_type, 'range' ); ?>><?php esc_html_e( 'Price Range', 'aitc-ai-tools' ); ?></option>
					<option value="tiers" <?php selected( $price_type, 'tiers' ); ?>><?php esc_html_e( 'Tiered Pricing', 'aitc-ai-tools' ); ?></option>
				</select>
			</p>

			<div class="aitc-price-single" style="<?php echo $price_type === 'single' ? '' : 'display:none;'; ?>">
				<p>
					<label for="price_single_amount"><strong><?php esc_html_e( 'Price (USD):', 'aitc-ai-tools' ); ?></strong></label><br>
					<input type="number" id="price_single_amount" name="price_single_amount" value="<?php echo esc_attr( $price_single ); ?>" step="0.01" min="0" class="small-text">
				</p>
			</div>

			<div class="aitc-price-range" style="<?php echo $price_type === 'range' ? '' : 'display:none;'; ?>">
				<p>
					<label for="price_range_low"><strong><?php esc_html_e( 'Price Range Low (USD):', 'aitc-ai-tools' ); ?></strong></label><br>
					<input type="number" id="price_range_low" name="price_range_low" value="<?php echo esc_attr( $price_range_low ); ?>" step="0.01" min="0" class="small-text">
				</p>
				<p>
					<label for="price_range_high"><strong><?php esc_html_e( 'Price Range High (USD):', 'aitc-ai-tools' ); ?></strong></label><br>
					<input type="number" id="price_range_high" name="price_range_high" value="<?php echo esc_attr( $price_range_high ); ?>" step="0.01" min="0" class="small-text">
				</p>
			</div>

			<div class="aitc-price-tiers" style="<?php echo $price_type === 'tiers' ? '' : 'display:none;'; ?>">
				<p>
					<label for="pricing_tiers_json"><strong><?php esc_html_e( 'Pricing Tiers (JSON):', 'aitc-ai-tools' ); ?></strong></label><br>
					<textarea id="pricing_tiers_json" name="pricing_tiers_json" rows="8" class="widefat code"><?php echo esc_textarea( $pricing_tiers_json ); ?></textarea>
					<span class="description">
						<?php esc_html_e( 'Format: [{"name":"Starter","amount":19,"currency":"USD","unit":"month","notes":"..."}]', 'aitc-ai-tools' ); ?>
					</span>
				</p>
			</div>

			<p>
				<label for="billing_unit"><strong><?php esc_html_e( 'Billing Unit:', 'aitc-ai-tools' ); ?></strong></label><br>
				<select id="billing_unit" name="billing_unit" class="widefat">
					<option value=""><?php esc_html_e( '-- Select --', 'aitc-ai-tools' ); ?></option>
					<option value="month" <?php selected( $billing_unit, 'month' ); ?>><?php esc_html_e( 'Month', 'aitc-ai-tools' ); ?></option>
					<option value="year" <?php selected( $billing_unit, 'year' ); ?>><?php esc_html_e( 'Year', 'aitc-ai-tools' ); ?></option>
					<option value="one_time" <?php selected( $billing_unit, 'one_time' ); ?>><?php esc_html_e( 'One-time', 'aitc-ai-tools' ); ?></option>
					<option value="seat_month" <?php selected( $billing_unit, 'seat_month' ); ?>><?php esc_html_e( 'Per seat/month', 'aitc-ai-tools' ); ?></option>
					<option value="seat_year" <?php selected( $billing_unit, 'seat_year' ); ?>><?php esc_html_e( 'Per seat/year', 'aitc-ai-tools' ); ?></option>
					<option value="usage" <?php selected( $billing_unit, 'usage' ); ?>><?php esc_html_e( 'Usage-based', 'aitc-ai-tools' ); ?></option>
				</select>
			</p>

			<p>
				<label>
					<input type="checkbox" name="has_free_plan" value="1" <?php checked( $has_free_plan, '1' ); ?>>
					<?php esc_html_e( 'Has Free Plan', 'aitc-ai-tools' ); ?>
				</label>
			</p>

			<p>
				<label>
					<input type="checkbox" name="has_free_trial" value="1" <?php checked( $has_free_trial, '1' ); ?>>
					<?php esc_html_e( 'Has Free Trial', 'aitc-ai-tools' ); ?>
				</label>
			</p>

			<p>
				<label for="trial_days"><strong><?php esc_html_e( 'Trial Days:', 'aitc-ai-tools' ); ?></strong></label><br>
				<input type="number" id="trial_days" name="trial_days" value="<?php echo esc_attr( $trial_days ); ?>" min="0" class="small-text">
			</p>
		</div>

		<?php
	}

	/**
	 * Render editorial review meta box
	 */
	public static function render_editorial_meta_box( $post ) {
		wp_nonce_field( 'aitc_editorial_meta_nonce', 'aitc_editorial_meta_nonce' );

		$editor_rating  = get_post_meta( $post->ID, '_editor_rating_value', true );
		$editor_summary = get_post_meta( $post->ID, '_editor_review_summary', true );
		$editor_pros    = get_post_meta( $post->ID, '_editor_pros', true );
		$editor_cons    = get_post_meta( $post->ID, '_editor_cons', true );
		$editor_features = get_post_meta( $post->ID, '_editor_features', true );
		?>

		<div class="aitc-meta-box">
			<p>
				<label for="editor_rating_value"><strong><?php esc_html_e( 'Editor Rating (1.0 - 5.0):', 'aitc-ai-tools' ); ?></strong></label><br>
				<input type="number" id="editor_rating_value" name="editor_rating_value" value="<?php echo esc_attr( $editor_rating ); ?>" step="0.1" min="1.0" max="5.0" class="small-text">
			</p>

			<p>
				<label for="editor_review_summary"><strong><?php esc_html_e( 'Review Summary:', 'aitc-ai-tools' ); ?></strong></label><br>
				<textarea id="editor_review_summary" name="editor_review_summary" rows="4" class="widefat"><?php echo esc_textarea( $editor_summary ); ?></textarea>
			</p>

			<p>
				<label for="editor_pros"><strong><?php esc_html_e( 'Pros (one per line):', 'aitc-ai-tools' ); ?></strong></label><br>
				<textarea id="editor_pros" name="editor_pros" rows="5" class="widefat"><?php echo esc_textarea( $editor_pros ); ?></textarea>
			</p>

			<p>
				<label for="editor_cons"><strong><?php esc_html_e( 'Cons (one per line):', 'aitc-ai-tools' ); ?></strong></label><br>
				<textarea id="editor_cons" name="editor_cons" rows="5" class="widefat"><?php echo esc_textarea( $editor_cons ); ?></textarea>
			</p>

			<p>
				<label for="editor_features"><strong><?php esc_html_e( 'Key Features (one per line):', 'aitc-ai-tools' ); ?></strong></label><br>
				<textarea id="editor_features" name="editor_features" rows="5" class="widefat"><?php echo esc_textarea( $editor_features ); ?></textarea>
			</p>
		</div>

		<?php
	}

	/**
	 * Save pricing meta
	 */
	public static function save_pricing_meta( $post_id, $post ) {
		if ( ! isset( $_POST['aitc_pricing_meta_nonce'] ) || ! wp_verify_nonce( $_POST['aitc_pricing_meta_nonce'], 'aitc_pricing_meta_nonce' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save fields to both legacy (_) and new (_aitc_) keys
		$fields = array(
			'official_url'        => 'esc_url_raw',
			'pricing_page_url'    => 'esc_url_raw',
			'pricing_model'       => 'sanitize_text_field',
			'price_type'          => 'sanitize_text_field',
			'price_single_amount' => 'floatval',
			'price_range_low'     => 'floatval',
			'price_range_high'    => 'floatval',
			'billing_unit'        => 'sanitize_text_field',
			'trial_days'          => 'absint',
			'pricing_tiers_json'  => 'sanitize_textarea_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) && $_POST[ $field ] !== '' ) {
				$value = call_user_func( $sanitize_callback, $_POST[ $field ] );
				update_post_meta( $post_id, '_' . $field, $value );
				update_post_meta( $post_id, '_aitc_' . $field, $value );
			} else {
				delete_post_meta( $post_id, '_' . $field );
				delete_post_meta( $post_id, '_aitc_' . $field );
			}
		}

		// Checkboxes — sync both key variants
		$free_plan = isset( $_POST['has_free_plan'] ) ? '1' : '0';
		update_post_meta( $post_id, '_has_free_plan', $free_plan );
		update_post_meta( $post_id, '_aitc_free_plan_available', $free_plan );

		$free_trial = isset( $_POST['has_free_trial'] ) ? '1' : '0';
		update_post_meta( $post_id, '_has_free_trial', $free_trial );
	}

	/**
	 * Save editorial meta
	 */
	public static function save_editorial_meta( $post_id, $post ) {
		if ( ! isset( $_POST['aitc_editorial_meta_nonce'] ) || ! wp_verify_nonce( $_POST['aitc_editorial_meta_nonce'], 'aitc_editorial_meta_nonce' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'editor_rating_value'   => 'floatval',
			'editor_review_summary' => 'sanitize_textarea_field',
			'editor_pros'           => 'sanitize_textarea_field',
			'editor_cons'           => 'sanitize_textarea_field',
			'editor_features'       => 'sanitize_textarea_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) && $_POST[ $field ] !== '' ) {
				update_post_meta( $post_id, '_' . $field, call_user_func( $sanitize_callback, $_POST[ $field ] ) );
			} else {
				delete_post_meta( $post_id, '_' . $field );
			}
		}
	}
}
