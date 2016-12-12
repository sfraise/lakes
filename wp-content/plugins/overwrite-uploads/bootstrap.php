<?php
/*
Plugin Name: Overwrite Uploads
Description: Overwrites uploaded files that already exist, instead of storing multiple copies.
Version:     1.1
Author:      Ian Dunn
Author URI:  http://iandunn.name
License:     GPLv2
*/

if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )
	die( 'Access denied.' );

define( 'OVUP_REQUIRED_PHP_VERSION', '5.2.4' );    // Because of WP minimum requirements
define( 'OVUP_REQUIRED_WP_VERSION',  '2.9' );      // Because of wp_handle_upload_prefilter

/**
 * Checks if the system requirements are met
 * @return bool True if system requirements are met, false if not
 */
function ovup_requirements_met() {
	global $wp_version;
	
	if ( version_compare( PHP_VERSION, OVUP_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, OVUP_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	if ( class_exists( 'OverwriteUploads' ) ) {
		return false;
	}

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function ovup_requirements_error() {
	global $wp_version;

	require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( ovup_requirements_met() ) {
	require_once( dirname( __FILE__ ) . '/classes/overwrite-uploads.php' );
	
	$GLOBALS['ovup'] = new OverwriteUploads();
		
} else {
	add_action( 'admin_notices', 'ovup_requirements_error' );
}