<?php
/**
 * Settings page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AITC_Settings {

	/**
	 * Initialize hooks
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Add admin submenu
	 */
	public static function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=ai_tool',
			__( 'Settings', 'aitc-ai-tools' ),
			__( 'Settings', 'aitc-ai-tools' ),
			'manage_options',
			'aitc-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Register settings
	 */
	public static function register_settings() {
		register_setting( 'aitc_ai_tools_settings', 'aitc_ai_tools_schema_enabled' );
		register_setting( 'aitc_ai_tools_settings', 'aitc_ai_tools_overwrite_empty_fields' );
	}

	/**
	 * Render settings page
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'aitc-ai-tools' ) );
		}

		if ( isset( $_POST['aitc_save_settings'] ) && check_admin_referer( 'aitc_settings_nonce' ) ) {
			update_option( 'aitc_ai_tools_schema_enabled', isset( $_POST['schema_enabled'] ) ? '1' : '0' );
			update_option( 'aitc_ai_tools_overwrite_empty_fields', isset( $_POST['overwrite_empty_fields'] ) ? '1' : '0' );
			echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Settings saved.', 'aitc-ai-tools' ) . '</p></div>';
		}

		$schema_enabled = get_option( 'aitc_ai_tools_schema_enabled', '1' );
		$overwrite_empty_fields = get_option( 'aitc_ai_tools_overwrite_empty_fields', '0' );
		?>

		<div class="wrap">
			<h1><?php esc_html_e( 'AI Tools Settings', 'aitc-ai-tools' ); ?></h1>

			<form method="post" action="">
				<?php wp_nonce_field( 'aitc_settings_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Schema Output', 'aitc-ai-tools' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" name="schema_enabled" value="1" <?php checked( $schema_enabled, '1' ); ?>>
								<?php esc_html_e( 'Enable JSON-LD schema output', 'aitc-ai-tools' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Disable this if you are using RankMath or another SEO plugin that generates schema markup to avoid duplication.', 'aitc-ai-tools' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php esc_html_e( 'CSV Import Behavior', 'aitc-ai-tools' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" name="overwrite_empty_fields" value="1" <?php checked( $overwrite_empty_fields, '1' ); ?>>
								<?php esc_html_e( 'Overwrite empty fields when importing CSV', 'aitc-ai-tools' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'If enabled, blank CSV cells will clear existing values. If disabled, blank cells are ignored for partial updates.', 'aitc-ai-tools' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" name="aitc_save_settings" class="button button-primary">
						<?php esc_html_e( 'Save Settings', 'aitc-ai-tools' ); ?>
					</button>
				</p>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Plugin Information', 'aitc-ai-tools' ); ?></h2>
			<table class="widefat">
				<tbody>
					<tr>
						<td><strong><?php esc_html_e( 'Version:', 'aitc-ai-tools' ); ?></strong></td>
						<td><?php echo esc_html( AITC_AI_TOOLS_VERSION ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Database Table:', 'aitc-ai-tools' ); ?></strong></td>
						<td>
							<?php
							global $wpdb;
							$table_name = $wpdb->prefix . 'aitc_tool_ratings';
							$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name;
							if ( $table_exists ) {
								echo '<span style="color: green;">✓ ' . esc_html__( 'Installed', 'aitc-ai-tools' ) . '</span>';
							} else {
								echo '<span style="color: red;">✗ ' . esc_html__( 'Not found', 'aitc-ai-tools' ) . '</span>';
							}
							?>
						</td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Total AI Tools:', 'aitc-ai-tools' ); ?></strong></td>
						<td><?php echo number_format_i18n( wp_count_posts( 'ai_tool' )->publish ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Total Reviews:', 'aitc-ai-tools' ); ?></strong></td>
						<td>
							<?php
							if ( $table_exists ) {
								$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
								echo number_format_i18n( $count );
							} else {
								echo '—';
							}
							?>
						</td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Pending Reviews:', 'aitc-ai-tools' ); ?></strong></td>
						<td>
							<?php
							if ( $table_exists ) {
								$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE status = 'pending'" );
								echo number_format_i18n( $count );
							} else {
								echo '—';
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<?php
	}
}
