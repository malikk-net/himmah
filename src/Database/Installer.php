<?php

namespace Himmah\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Installer
 * Handles database tables creation for Himmah plugin.
 */
class Installer {

	/**
	 * Run database installation.
	 */
	public static function run() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'himmah_activities';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL auto_increment,
			user_id bigint(20) unsigned NOT NULL,
			challenge_id bigint(20) unsigned NOT NULL,
			points int(11) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY challenge_id (challenge_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}