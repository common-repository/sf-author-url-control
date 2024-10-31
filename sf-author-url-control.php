<?php
/*
 * Plugin Name: SF Author Url Control
 * Plugin URI: https://www.screenfeed.fr/auturl/
 * Description: Customize the url of your registered users profile.
 * Version: 1.2
 * Author: Grégory Viguier
 * Author URI: https://www.screenfeed.fr/greg/
 * License: GPLv3
 * License URI: https://www.screenfeed.fr/gpl-v3.txt
 * Text Domain: sf-author-url-control
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Cheatin\' uh?' );
}

// Check WordPress Version.
global $wp_version;

if ( version_compare( $wp_version, '3.0' ) < 0 ) {
	return;
}

/*------------------------------------------------------------------------------------------------*/
/* !CONSTANTS =================================================================================== */
/*------------------------------------------------------------------------------------------------*/

define( 'SF_AUC_VERSION',    '1.2' );
define( 'SF_AUC_FILE',       __FILE__ );
define( 'SF_AUC_PLUGIN_DIR', plugin_dir_path( SF_AUC_FILE ) );
define( 'SF_AUC_BASENAME',   plugin_basename( SF_AUC_FILE ) );


/*------------------------------------------------------------------------------------------------*/
/* !INIT ======================================================================================== */
/*------------------------------------------------------------------------------------------------*/

// Change the "author" base.

add_action( 'init', 'sf_auc_author_base' );

function sf_auc_author_base() {
	global $wp_rewrite;

	if ( ! $wp_rewrite || ! is_object( $wp_rewrite ) ) {
		return;
	}

	$wp_rewrite->author_base = sf_auc_get_author_base();
}


// Get the "author" base.

function sf_auc_get_author_base() {
	global $wp_rewrite;

	$front       = ! empty( $wp_rewrite ) ? trim( $wp_rewrite->front, '/' ) . '/' : 'blog/';
	$author_base = trim( get_option( 'author_base', '' ), '/' );
	// Compat old version of the plugin.
	$author_base = strpos( $author_base, $front ) === 0 ? substr( $author_base, 0, strlen( $front ) ) : $author_base;
	$author_base = sanitize_title( $author_base );
	$author_base = $author_base && trim( $front, '/' ) !== $author_base ? $author_base : 'author';

	return $author_base;
}


/*------------------------------------------------------------------------------------------------*/
/* !INCLUDES ==================================================================================== */
/*------------------------------------------------------------------------------------------------*/

if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	include( SF_AUC_PLUGIN_DIR . '/inc/admin.php' );
}
