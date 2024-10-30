<?php
/*
Plugin Name: Covid-19 Statistics Displayer
Plugin URI: https://moduloinfo.ca/wordpress/
Description: This plugin allow you to display the latest covid statistics and previsions for the entire world using [covidstats] shortcode on the page where you want the stats to display.
Author: Carl Sansfacon
Version: 1.2
Author URI: https://moduloinfo.ca/
*/




function carlsansshowcovidstats() {
	if(!is_admin()){
		//include( plugin_dir_path( __FILE__ ) . 'covidsearch.php');
	ob_start();
	include  plugin_dir_path( __FILE__ ) . 'covidsearch.php';
	$string = ob_get_clean();
	return $string;
	}
return "";
}

function cscsdcustom_shortcode_scripts() {
    global $post;
    if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'covidstats') && !is_admin()) {
			$jspath =  plugin_dir_url( __DIR__ ) . 'covid-19-statistics-displayer/js/autocomplete.js';
			wp_register_script( 'csautocomplete', $jspath , '', '', true );
			wp_enqueue_script( 'csautocomplete' );
    }
}

function cscsdcustom_shortcode_styles(){
	global $post;
	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'covidstats') && !is_admin() ) {
		$cssautocomplete = plugin_dir_url( __DIR__ ) . 'covid-19-statistics-displayer/css/autocomplete.css';
		$cssadaptative = plugin_dir_url( __DIR__ ) . 'covid-19-statistics-displayer/css/adaptivetable.css';
		wp_enqueue_style(
						'csautocomplete',
						$cssautocomplete
		);
		wp_enqueue_style(
						'csadapatative',
						$cssadaptative
		);
	}
}

add_action( 'get_footer', 'cscsdcustom_shortcode_styles' );
add_action( 'wp_enqueue_scripts', 'cscsdcustom_shortcode_scripts');
add_shortcode('covidstats', 'carlsansshowcovidstats');
?>
