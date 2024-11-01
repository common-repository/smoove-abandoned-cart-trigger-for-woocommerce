<?php
namespace SMAC;

class Post_Types{
	public function __construct(){
		add_action( 'init', [$this, 'register_post_types'] );
	}
	
	public function register_post_types() {
		/* Post type: Abandoned Cart */
		$labels = array(
			'name'               => _x( 'Abandoned Carts', 'post type general name', 'smac' ),
			'singular_name'      => _x( 'Abandoned Cart', 'post type singular name', 'smac' ),
			'menu_name'          => _x( 'Abandoned Carts', 'admin menu', 'smac' ),
			'name_admin_bar'     => _x( 'Abandoned Cart', 'add new on admin bar', 'smac' ),
			'add_new'            => _x( 'Add New', 'Abandoned Cart', 'smac' ),
			'add_new_item'       => __( 'Add New Abandoned Cart', 'smac' ),
			'new_item'           => __( 'New Abandoned Cart', 'smac' ),
			'edit_item'          => __( 'Edit Abandoned Cart', 'smac' ),
			'view_item'          => __( 'View Abandoned Cart', 'smac' ),
			'all_items'          => __( 'Abandoned Carts', 'smac' ),
			'search_items'       => __( 'Search Abandoned Cart', 'smac' ),
			'parent_item_colon'  => __( 'Parent Abandoned Carts:', 'smac' ),
			'not_found'          => __( 'No Abandoned Carts found.', 'smac' ),
			'not_found_in_trash' => __( 'No Abandoned Carts found in Trash.', 'smac' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'show_in_nav_menus'	 => false,
			'rewrite'            => ['slug' => 'smac-abandoned-cart'],
			'capability_type'	 => 'post',
			'capabilities'		 => [
				'edit_post' 		=> false,
				'read_post'			=> false,
				'create_posts' 		=> false,
				'publish_posts'		=> false,
			],
			'map_meta_cap' => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => SMAC_PLUGIN_DIR . '/assets/images/menu-icon.png',
			'supports'           => ['title', 'custom-fields']
		);

		register_post_type( 'smac-abandoned-cart', $args );

		/* Post type: Temp Cart */
		$labels = array(
			'name'               => _x( 'Temp Carts', 'post type general name', 'smac' ),
			'singular_name'      => _x( 'Temp Cart', 'post type singular name', 'smac' ),
			'menu_name'          => _x( 'Temp Carts', 'admin menu', 'smac' ),
			'name_admin_bar'     => _x( 'Temp Cart', 'add new on admin bar', 'smac' ),
			'add_new'            => _x( 'Add New', 'Temp Cart', 'smac' ),
			'add_new_item'       => __( 'Add New Temp Cart', 'smac' ),
			'new_item'           => __( 'New Temp Cart', 'smac' ),
			'edit_item'          => __( 'Edit Temp Cart', 'smac' ),
			'view_item'          => __( 'View Temp Cart', 'smac' ),
			'all_items'          => __( 'Temp Carts', 'smac' ),
			'search_items'       => __( 'Search Temp Cart', 'smac' ),
			'parent_item_colon'  => __( 'Parent Temp Carts:', 'smac' ),
			'not_found'          => __( 'No Temp Carts found.', 'smac' ),
			'not_found_in_trash' => __( 'No Temp Carts found in Trash.', 'smac' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'show_in_menu'       => false,
			// 'show_ui'            => true,
			// 'show_in_menu'       => 'edit.php?post_type=smac-abandoned-cart',
			'query_var'          => false,
			'show_in_nav_menus'	 => false,
			'rewrite'            => ['slug' => 'smac-temp-cart'],
			'capability_type'	 => 'post',
			'map_meta_cap' 		 => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => ['title', 'custom-fields']
		);

		register_post_type( 'smac-temp-cart', $args );
	}
}

new Post_Types();