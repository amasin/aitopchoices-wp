<?php
/**
 * Register taxonomies and seed default terms
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AITC_Taxonomies {

	/**
	 * Register all taxonomies
	 */
	public static function register() {
		self::register_category();
		self::register_use_case();
		self::register_platform();
		self::register_pricing_model();
		self::register_billing_unit();
	}

	/**
	 * Register hierarchical category taxonomy
	 */
	private static function register_category() {
		$labels = array(
			'name'              => _x( 'AI Tool Categories', 'taxonomy general name', 'aitc-ai-tools' ),
			'singular_name'     => _x( 'AI Tool Category', 'taxonomy singular name', 'aitc-ai-tools' ),
			'search_items'      => __( 'Search Categories', 'aitc-ai-tools' ),
			'all_items'         => __( 'All Categories', 'aitc-ai-tools' ),
			'parent_item'       => __( 'Parent Category', 'aitc-ai-tools' ),
			'parent_item_colon' => __( 'Parent Category:', 'aitc-ai-tools' ),
			'edit_item'         => __( 'Edit Category', 'aitc-ai-tools' ),
			'update_item'       => __( 'Update Category', 'aitc-ai-tools' ),
			'add_new_item'      => __( 'Add New Category', 'aitc-ai-tools' ),
			'new_item_name'     => __( 'New Category Name', 'aitc-ai-tools' ),
			'menu_name'         => __( 'Categories', 'aitc-ai-tools' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'ai-tools' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'ai_tool_category', array( 'ai_tool' ), $args );
	}

	/**
	 * Register use case taxonomy
	 */
	private static function register_use_case() {
		$labels = array(
			'name'                       => _x( 'Use Cases', 'taxonomy general name', 'aitc-ai-tools' ),
			'singular_name'              => _x( 'Use Case', 'taxonomy singular name', 'aitc-ai-tools' ),
			'search_items'               => __( 'Search Use Cases', 'aitc-ai-tools' ),
			'popular_items'              => __( 'Popular Use Cases', 'aitc-ai-tools' ),
			'all_items'                  => __( 'All Use Cases', 'aitc-ai-tools' ),
			'edit_item'                  => __( 'Edit Use Case', 'aitc-ai-tools' ),
			'update_item'                => __( 'Update Use Case', 'aitc-ai-tools' ),
			'add_new_item'               => __( 'Add New Use Case', 'aitc-ai-tools' ),
			'new_item_name'              => __( 'New Use Case Name', 'aitc-ai-tools' ),
			'separate_items_with_commas' => __( 'Separate use cases with commas', 'aitc-ai-tools' ),
			'add_or_remove_items'        => __( 'Add or remove use cases', 'aitc-ai-tools' ),
			'choose_from_most_used'      => __( 'Choose from the most used use cases', 'aitc-ai-tools' ),
			'menu_name'                  => __( 'Use Cases', 'aitc-ai-tools' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'ai-use-case' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'ai_use_case', array( 'ai_tool' ), $args );
	}

	/**
	 * Register platform taxonomy
	 */
	private static function register_platform() {
		$labels = array(
			'name'                       => _x( 'Platforms', 'taxonomy general name', 'aitc-ai-tools' ),
			'singular_name'              => _x( 'Platform', 'taxonomy singular name', 'aitc-ai-tools' ),
			'search_items'               => __( 'Search Platforms', 'aitc-ai-tools' ),
			'popular_items'              => __( 'Popular Platforms', 'aitc-ai-tools' ),
			'all_items'                  => __( 'All Platforms', 'aitc-ai-tools' ),
			'edit_item'                  => __( 'Edit Platform', 'aitc-ai-tools' ),
			'update_item'                => __( 'Update Platform', 'aitc-ai-tools' ),
			'add_new_item'               => __( 'Add New Platform', 'aitc-ai-tools' ),
			'new_item_name'              => __( 'New Platform Name', 'aitc-ai-tools' ),
			'separate_items_with_commas' => __( 'Separate platforms with commas', 'aitc-ai-tools' ),
			'add_or_remove_items'        => __( 'Add or remove platforms', 'aitc-ai-tools' ),
			'choose_from_most_used'      => __( 'Choose from the most used platforms', 'aitc-ai-tools' ),
			'menu_name'                  => __( 'Platforms', 'aitc-ai-tools' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'ai-platform' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'ai_platform', array( 'ai_tool' ), $args );
	}

	/**
	 * Register pricing model taxonomy
	 */
	private static function register_pricing_model() {
		$labels = array(
			'name'                       => _x( 'Pricing Models', 'taxonomy general name', 'aitc-ai-tools' ),
			'singular_name'              => _x( 'Pricing Model', 'taxonomy singular name', 'aitc-ai-tools' ),
			'search_items'               => __( 'Search Pricing Models', 'aitc-ai-tools' ),
			'popular_items'              => __( 'Popular Pricing Models', 'aitc-ai-tools' ),
			'all_items'                  => __( 'All Pricing Models', 'aitc-ai-tools' ),
			'edit_item'                  => __( 'Edit Pricing Model', 'aitc-ai-tools' ),
			'update_item'                => __( 'Update Pricing Model', 'aitc-ai-tools' ),
			'add_new_item'               => __( 'Add New Pricing Model', 'aitc-ai-tools' ),
			'new_item_name'              => __( 'New Pricing Model Name', 'aitc-ai-tools' ),
			'separate_items_with_commas' => __( 'Separate pricing models with commas', 'aitc-ai-tools' ),
			'add_or_remove_items'        => __( 'Add or remove pricing models', 'aitc-ai-tools' ),
			'choose_from_most_used'      => __( 'Choose from the most used pricing models', 'aitc-ai-tools' ),
			'menu_name'                  => __( 'Pricing Models', 'aitc-ai-tools' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'pricing-model' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'ai_pricing_model', array( 'ai_tool' ), $args );
	}

	/**
	 * Register billing unit taxonomy
	 */
	private static function register_billing_unit() {
		$labels = array(
			'name'                       => _x( 'Billing Units', 'taxonomy general name', 'aitc-ai-tools' ),
			'singular_name'              => _x( 'Billing Unit', 'taxonomy singular name', 'aitc-ai-tools' ),
			'search_items'               => __( 'Search Billing Units', 'aitc-ai-tools' ),
			'popular_items'              => __( 'Popular Billing Units', 'aitc-ai-tools' ),
			'all_items'                  => __( 'All Billing Units', 'aitc-ai-tools' ),
			'edit_item'                  => __( 'Edit Billing Unit', 'aitc-ai-tools' ),
			'update_item'                => __( 'Update Billing Unit', 'aitc-ai-tools' ),
			'add_new_item'               => __( 'Add New Billing Unit', 'aitc-ai-tools' ),
			'new_item_name'              => __( 'New Billing Unit Name', 'aitc-ai-tools' ),
			'separate_items_with_commas' => __( 'Separate billing units with commas', 'aitc-ai-tools' ),
			'add_or_remove_items'        => __( 'Add or remove billing units', 'aitc-ai-tools' ),
			'choose_from_most_used'      => __( 'Choose from the most used billing units', 'aitc-ai-tools' ),
			'menu_name'                  => __( 'Billing Units', 'aitc-ai-tools' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'billing-unit' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'ai_billing_unit', array( 'ai_tool' ), $args );
	}

	/**
	 * Seed default terms on activation
	 */
	public static function seed_default_terms() {
		// Create parent category
		$parent_term = term_exists( 'AI Tools', 'ai_tool_category' );
		if ( ! $parent_term ) {
			$parent_term = wp_insert_term( 'AI Tools', 'ai_tool_category' );
		}
		$parent_id = is_array( $parent_term ) ? $parent_term['term_id'] : $parent_term;

		// Category tree
		$categories = array(
			'writing'              => 'Writing',
			'image-design'         => 'Image & Design',
			'video'                => 'Video',
			'coding'               => 'Coding',
			'marketing'            => 'Marketing',
			'productivity'         => 'Productivity',
			'audio-voice'          => 'Audio & Voice',
			'chatbots'             => 'Chatbots',
			'business'             => 'Business',
			'data-analytics'       => 'Data & Analytics',
			'education'            => 'Education',
			'automation-agents'    => 'Automation & Agents',
			'research-platforms'   => 'Research Platforms',
			'aggregators'          => 'Aggregators',
		);

		foreach ( $categories as $slug => $name ) {
			if ( ! term_exists( $slug, 'ai_tool_category' ) ) {
				wp_insert_term(
					$name,
					'ai_tool_category',
					array(
						'slug'   => $slug,
						'parent' => $parent_id,
					)
				);
			}
		}

		// Pricing model terms
		$pricing_models = array(
			'free'        => 'Free',
			'freemium'    => 'Freemium',
			'paid'        => 'Paid',
			'usage-based' => 'Usage-based',
			'one-time'    => 'One-time',
			'enterprise'  => 'Enterprise',
			'open-source' => 'Open-source',
		);

		foreach ( $pricing_models as $slug => $name ) {
			if ( ! term_exists( $slug, 'ai_pricing_model' ) ) {
				wp_insert_term(
					$name,
					'ai_pricing_model',
					array( 'slug' => $slug )
				);
			}
		}

		// Billing unit terms
		$billing_units = array(
			'month'      => 'month',
			'year'       => 'year',
			'one_time'   => 'one_time',
			'seat_month' => 'seat_month',
			'seat_year'  => 'seat_year',
			'usage'      => 'usage',
		);

		foreach ( $billing_units as $slug => $name ) {
			if ( ! term_exists( $slug, 'ai_billing_unit' ) ) {
				wp_insert_term(
					$name,
					'ai_billing_unit',
					array( 'slug' => $slug )
				);
			}
		}
	}
}
