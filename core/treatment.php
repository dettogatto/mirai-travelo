<?php


/*
 * register Treatment taxonomy
*/
if ( ! function_exists( 'mirai_register_treatment_taxonomy' ) ) {
	function mirai_register_treatment_taxonomy(){
		$labels = array(
				'name'              => _x( 'Treatments', 'taxonomy general name', 'miraitravelo' ),
				'singular_name'     => _x( 'Treatment', 'taxonomy singular name', 'miraitravelo' ),
				'menu_name'         => __( 'All Treatments', 'miraitravelo' ),
				'all_items'         => __( 'All Treatments', 'miraitravelo' ),
				'parent_item'                => null,
				'parent_item_colon'          => null,
				'new_item_name'     => __( 'New Treatment', 'miraitravelo' ),
				'add_new_item'      => __( 'Add New Treatment', 'miraitravelo' ),
				'edit_item'         => __( 'Edit Treatment', 'miraitravelo' ),
				'update_item'       => __( 'Update Treatment', 'miraitravelo' ),
				'separate_items_with_commas' => __( 'Separate treatments with commas', 'miraitravelo' ),
				'search_items'      => __( 'Search Treatments', 'miraitravelo' ),
				'add_or_remove_items'        => __( 'Add or remove treatments', 'miraitravelo' ),
				'choose_from_most_used'      => __( 'Choose from the most used treatments', 'miraitravelo' ),
				'not_found'                  => __( 'No treatment found.', 'miraitravelo' ),
			);
		$args = array(
				'labels'            => $labels,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'meta_box_cb'       => false
			);
		register_taxonomy( 'treatment', array( 'accommodation' ), $args );
	}
}

add_action( 'init', 'mirai_register_treatment_taxonomy', 1 );
