<?php
/**
 * Plugin Name: AI Top Choices - AI Tools
 * Plugin URI: https://aitopchoices.com
 * Description: Custom post type and taxonomies for AI Tools catalogue with ratings, reviews, schema, and CSV import.
 * Version: 1.1.0
 * Author: AI Top Choices
 * Author URI: https://aitopchoices.com
 * Text Domain: aitc-ai-tools
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'AITC_AI_TOOLS_VERSION', '1.1.0' );
define( 'AITC_AI_TOOLS_FILE', __FILE__ );
define( 'AITC_AI_TOOLS_PATH', plugin_dir_path( __FILE__ ) );
define( 'AITC_AI_TOOLS_URL', plugin_dir_url( __FILE__ ) );

// Include core files
require_once AITC_AI_TOOLS_PATH . 'includes/class-post-types.php';
require_once AITC_AI_TOOLS_PATH . 'includes/class-taxonomies.php';
require_once AITC_AI_TOOLS_PATH . 'includes/class-meta-boxes.php';
require_once AITC_AI_TOOLS_PATH . 'includes/class-ratings.php';
require_once AITC_AI_TOOLS_PATH . 'includes/class-schema.php';
require_once AITC_AI_TOOLS_PATH . 'includes/class-templates.php';
require_once AITC_AI_TOOLS_PATH . 'admin/class-settings.php';
require_once AITC_AI_TOOLS_PATH . 'admin/class-csv-importer.php';
require_once AITC_AI_TOOLS_PATH . 'admin/class-ratings-admin.php';

/**
 * Plugin activation hook
 */
function aitc_ai_tools_activate() {
	// Register post types and taxonomies
	AITC_Post_Types::register();
	AITC_Taxonomies::register();
	AITC_Taxonomies::seed_default_terms();

	// Create ratings table
	AITC_Ratings::create_table();

	// Flush rewrite rules
	flush_rewrite_rules();

	// Set default options
	if ( ! get_option( 'aitc_ai_tools_schema_enabled' ) ) {
		update_option( 'aitc_ai_tools_schema_enabled', '1' );
	}
}
register_activation_hook( __FILE__, 'aitc_ai_tools_activate' );

/**
 * Plugin deactivation hook
 */
function aitc_ai_tools_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'aitc_ai_tools_deactivate' );

/**
 * Register post types and taxonomies on 'init' hook
 * This must run on 'init' because register_post_type requires $wp_rewrite to be available
 */
function aitc_ai_tools_register_types() {
	AITC_Post_Types::register();
	AITC_Taxonomies::register();
}
add_action( 'init', 'aitc_ai_tools_register_types' );

/**
 * Initialize plugin components on 'init' hook (after post types are registered)
 */
function aitc_ai_tools_init() {
	// Initialize meta boxes
	AITC_Meta_Boxes::init();

	// Initialize ratings
	AITC_Ratings::init();

	// Initialize schema
	AITC_Schema::init();

	// Initialize templates
	AITC_Templates::init();

	// Initialize admin
	if ( is_admin() ) {
		AITC_Settings::init();
		AITC_CSV_Importer::init();
		AITC_Ratings_Admin::init();
	}
}
add_action( 'init', 'aitc_ai_tools_init', 20 );
