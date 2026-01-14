<?php
/**
 * Register custom post types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AITC_Post_Types {

	/**
	 * Register AI Tool post type
	 */
	public static function register() {
		$labels = array(
			'name'                  => _x( 'AI Tools', 'Post type general name', 'aitc-ai-tools' ),
			'singular_name'         => _x( 'AI Tool', 'Post type singular name', 'aitc-ai-tools' ),
			'menu_name'             => _x( 'AI Tools', 'Admin Menu text', 'aitc-ai-tools' ),
			'name_admin_bar'        => _x( 'AI Tool', 'Add New on Toolbar', 'aitc-ai-tools' ),
			'add_new'               => __( 'Add New', 'aitc-ai-tools' ),
			'add_new_item'          => __( 'Add New AI Tool', 'aitc-ai-tools' ),
			'new_item'              => __( 'New AI Tool', 'aitc-ai-tools' ),
			'edit_item'             => __( 'Edit AI Tool', 'aitc-ai-tools' ),
			'view_item'             => __( 'View AI Tool', 'aitc-ai-tools' ),
			'all_items'             => __( 'All AI Tools', 'aitc-ai-tools' ),
			'search_items'          => __( 'Search AI Tools', 'aitc-ai-tools' ),
			'parent_item_colon'     => __( 'Parent AI Tools:', 'aitc-ai-tools' ),
			'not_found'             => __( 'No AI tools found.', 'aitc-ai-tools' ),
			'not_found_in_trash'    => __( 'No AI tools found in Trash.', 'aitc-ai-tools' ),
			'featured_image'        => _x( 'Tool Logo', 'Overrides the "Featured Image" phrase', 'aitc-ai-tools' ),
			'set_featured_image'    => _x( 'Set tool logo', 'Overrides the "Set featured image" phrase', 'aitc-ai-tools' ),
			'remove_featured_image' => _x( 'Remove tool logo', 'Overrides the "Remove featured image" phrase', 'aitc-ai-tools' ),
			'use_featured_image'    => _x( 'Use as tool logo', 'Overrides the "Use as featured image" phrase', 'aitc-ai-tools' ),
			'archives'              => _x( 'AI Tool archives', 'The post type archive label used in nav menus', 'aitc-ai-tools' ),
			'insert_into_item'      => _x( 'Insert into AI tool', 'Overrides the "Insert into post" phrase', 'aitc-ai-tools' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this AI tool', 'Overrides the "Uploaded to this post" phrase', 'aitc-ai-tools' ),
			'filter_items_list'     => _x( 'Filter AI tools list', 'Screen reader text for the filter links', 'aitc-ai-tools' ),
			'items_list_navigation' => _x( 'AI tools list navigation', 'Screen reader text for the pagination', 'aitc-ai-tools' ),
			'items_list'            => _x( 'AI tools list', 'Screen reader text for the items list', 'aitc-ai-tools' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'ai-tools' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-superhero-alt',
			'show_in_rest'       => true,
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author' ),
		);

		register_post_type( 'ai_tool', $args );
	}
}
