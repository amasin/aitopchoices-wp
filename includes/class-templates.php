<?php
/**
 * Template loader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AITC_Templates {

	/**
	 * Initialize hooks
	 */
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );
	}

	/**
	 * Load plugin templates
	 */
	public static function template_loader( $template ) {
		if ( is_singular( 'ai_tool' ) ) {
			$plugin_template = self::locate_template( 'single-ai_tool.php' );
			if ( $plugin_template ) {
				return $plugin_template;
			}
		}

		if ( is_post_type_archive( 'ai_tool' ) ) {
			$plugin_template = self::locate_template( 'archive-ai_tool.php' );
			if ( $plugin_template ) {
				return $plugin_template;
			}
		}

		if ( is_tax( 'ai_tool_category' ) ) {
			$plugin_template = self::locate_template( 'taxonomy-ai_tool_category.php' );
			if ( $plugin_template ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Locate template file
	 */
	private static function locate_template( $template_name ) {
		// Check theme first
		$theme_template = locate_template( array( 'aitc-ai-tools/' . $template_name, $template_name ) );
		if ( $theme_template ) {
			return $theme_template;
		}

		// Use plugin template
		$plugin_template = AITC_AI_TOOLS_PATH . 'templates/' . $template_name;
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		return false;
	}
}
