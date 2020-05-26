<?php
/**
 * If uninstall.php is not called by WordPress, die.
 *
 * @package RainbowAddressBar
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

global $wpdb;

// Remove options from option table.
delete_option( 'rab-switch' );
delete_option( 'rab-amp-switch' );
delete_option( 'rab-post-type' );
delete_option( 'rab-color' );

// Remove postmeta from postmeta table.
$postmeta_table = $wpdb->prefix . 'postmeta';
$wpdb->delete( $postmeta_table, array( 'meta_key' => 'rab-color' ) ); /* db call ok; no-cache ok; slow query ok */
