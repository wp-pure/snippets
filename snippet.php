<?php

/*
Plugin Name: Snippets
Plugin URI: https://github.com/wp-pure/snippets
Description: Snippets of reusable html that can easily be included in pages, posts, or even templates using shortcodes.
Version: 0.0.4
Author: James Pederson
Author URI: https://jpederson.com
License: GPL2
*/


// snippet custom post type function
function snippet_post_type() { 

	// register our custom post type
	register_post_type( 'snippet',

		// custom post type information
		array(
			'labels' => array(
				'name' => __( 'Snippets', 'ptheme' ), /* This is the Title of the Group */
				'singular_name' => __( 'Snippet', 'ptheme' ), /* This is the individual type */
				'all_items' => __( 'All Snippets', 'ptheme' ), /* the all items menu item */
				'add_new' => __( 'Add New', 'ptheme' ), /* The add new menu item */
				'add_new_item' => __( 'Add New Snippet', 'ptheme' ), /* Add New Display Title */
				'edit' => __( 'Edit', 'ptheme' ), /* Edit Dialog */
				'edit_item' => __( 'Edit Snippets', 'ptheme' ), /* Edit Display Title */
				'new_item' => __( 'New Snippet', 'ptheme' ), /* New Display Title */
				'view_item' => __( 'View Snippet', 'ptheme' ), /* View Display Title */
				'search_items' => __( 'Search Snippets', 'ptheme' ), /* Search Custom Type Title */ 
				'not_found' =>  __( 'Nothing found in the database.', 'ptheme' ), /* This displays if there are no entries yet */ 
				'not_found_in_trash' => __( 'Nothing found in Trash', 'ptheme' ), /* This displays if there is nothing in the trash */
				'parent_item_colon' => ''
			),
			'description' => __( 'Manage Snippets.', 'ptheme' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 25, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => 'dashicons-media-code', /* the icon for the custom post type menu */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array( 'title', 'editor', 'revisions' )
		)

	);

}
add_action( 'init', 'snippet_post_type');



// function to handle the snippet shortcode
function snippet_shortcode( $atts ) {

	if ( !isset( $atts['content_filter'] ) ) {
		$atts['content_filter'] = true;
	}

	// if we have a slug specified
	if ( isset( $atts['slug'] ) ) {

		// let's set the args for our select
		$args = array(
		  'name'        => $atts['slug'],
		  'post_type'   => 'snippet',
		  'numberposts' => 1
		);

		// get the snippet
		$snippets = get_posts( $args );

		// check to make sure it's not empty
		if ( isset( $snippets[0] ) ) {

			// store snippet content
			$snippet_content = $snippets[0]->post_content;

			// if the content contains script tags or style tags, return as is
			if ( stristr( $snippet_content, '<script' ) || stristr( $snippet_content, '<style' ) ) {

				// return the content
				return $snippet_content;

			} else {

				// if there's no script tags to mess up, process with wpautop and return formatted
				if ( !$atts['content_filter'] ) {
					return $snippet_content;
				} else {
					return apply_filters( 'the_content', $snippet_content );
				}

			}


		} else {

			// return nothing if the snippet doesn't exist
			return '';
		}
		
	}
}
add_shortcode( 'snippet', 'snippet_shortcode' );



// function to handle the snippet shortcode
function get_snippet( $slug, $filter_content = true ) {

	// if we have a slug specified
	if ( isset( $slug ) ) {

		// let's set the args for our select
		$args = array(
		  'name'        => $slug,
		  'post_type'   => 'snippet',
		  'numberposts' => 1
		);

		// get the snippet
		$snippets = get_posts( $args );

		// check to make sure it's not empty
		if ( isset( $snippets[0] ) ) {

			// store snippet content
			$snippet_content = $snippets[0]->post_content;

			// if the content contains script tags or style tags, return as is
			if ( stristr( $snippet_content, '<script' ) || stristr( $snippet_content, '<style' ) ) {

				// return the content
				return $snippet_content;

			} else {

				// if there's no script tags to mess up, process with wpautop and return formatted
				if ( !$filter_content ) {
					return $snippet_content;
				} else {
					return apply_filters( 'the_content', $snippet_content );
				}

			}

		} else {

			// return nothing if the snippet doesn't exist
			return '';
		}
		
	}
}



// custom admin listing column to output the shortcode for each snipper
function snippet_columns( $columns ) {

	// don't include the data column (it's really not necessary)
	unset($columns['date']);

	// add a shortcode column
	$columns['snippet_shortcode'] = 'Shortcode';

	// return the columns list
	return $columns;
}
add_filter( 'manage_snippet_posts_columns', 'snippet_columns' );



// populate the new column with the shortcode so users can easily copy it
function snippet_columns_content( $column, $post_id ) {

	// only affect the appropriate column
    if ( $column === 'snippet_shortcode' ) {

    	// get the slug
    	$slug = get_post_field( 'post_name', $post_id );
		
		// output the shortcode for easy copying
		echo '[snippet slug="' . $slug . '"]';

	}

}
add_action( 'manage_posts_custom_column' , 'snippet_columns_content', 10, 2 );



// remove data filtering from snippets admin listing
function snippet_remove_date_filter( $months ) {

	// get post type
    global $typenow;

    // only remove this for snippet post type
    if ( $typenow == 'snippet' ) {
        return array();
    }

    // otherwise keep date filter
    return $months;
}
add_filter( 'months_dropdown_results', 'snippet_remove_date_filter' );



// sort snippets by title in the dashboard listing.
function snippet_order( $query ) {

	// get post type
    global $typenow;

    // only remove this for snippet post type
    if ( $typenow == 'snippet' ) {
		$query->set( 'order' , 'asc' );
		$query->set( 'orderby', 'title');
		return;
	}
}
add_action( 'pre_get_posts', 'snippet_order', 1 );


