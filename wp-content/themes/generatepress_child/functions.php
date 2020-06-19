<?php
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */

function generatepress_child_enqueue_scripts() {
	if ( is_rtl() ) {
		wp_enqueue_style( 'generatepress-rtl', trailingslashit( get_template_directory_uri() ) . 'rtl.css' );
		wp_enqueue_style( 'uikit-rtl', trailingslashit( get_template_directory_uri() ) . '/uikit/css/uikit-rtl.css' );
		// wp_enqueue_style( 'uikit-rtl-min', trailingslashit( get_template_directory_uri() ) . 'uikit/css/uikit-rtl.min.css' );
	}

	wp_enqueue_style( 'uikit', get_stylesheet_directory_uri() . '/uikit/css/uikit.css' );
	wp_enqueue_style( 'style-custom', get_stylesheet_directory_uri() . '/style-custom.css' );
	// wp_enqueue_style( 'uikit-min', get_stylesheet_directory_uri() . 'uikit/css/uikit.min.css' );
}
add_action( 'wp_enqueue_scripts', 'generatepress_child_enqueue_scripts', 100 );


function cumulus_additional_scripts_footer() {
    wp_enqueue_script( 'uikit-icons', get_stylesheet_directory_uri() . '/uikit/js/uikit-icons.js', array(), false, true );
    // wp_enqueue_script( 'uikit-icons-min', get_stylesheet_directory_uri() . '/uikit/js/uikit-icons.min.js', array(), false, true );
    wp_enqueue_script( 'uikit-js', get_stylesheet_directory_uri() . '/uikit/js/uikit.js', array(), false, true );
    // wp_enqueue_script( 'uikit-js', get_stylesheet_directory_uri() . '/uikit/js/uikit.min.js', array(), false, true );

}

add_action( 'wp_enqueue_scripts', 'cumulus_additional_scripts_footer' );

function sort_by_date_acf( $value, $post_id, $field ) {

	// vars
	$order = array();
	// bail early if no value
	if( empty($value) ) {

		return $value;
	}

	// populate order
	$sub_filed = get_sub_field('godzina');
	foreach( $value as $i => $sub_filed ) {

		$order[ $i ] = $sub_filed['field_5e9b2ca5a7ad3'];

		echo $sub_filed['field_5e9b2ca5a7ad3'];
	}

	// multisort
	array_multisort( $order, SORT_ASC, $value );

	// return
	return $value;

}

add_filter('acf/load_value/name=wydarzenia', 'sort_by_date_acf', 10, 3);