<?php

namespace Himmah\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Installer
 * Handles database table creation and activation setup for Himmah plugin.
 */
class Installer {

	/**
	 * Run installation tasks.
	 */
	public static function run() {
		self::create_tables();
		self::set_default_options();
	}

	/**
	 * Create custom database tables.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'himmah_activities';

		$sql = "CREATE TABLE $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			challenge_id bigint(20) UNSIGNED NOT NULL,
			points int(11) DEFAULT 10 NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY challenge_id (challenge_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Set default options and version tracker.
	 */
	private static function set_default_options() {
		if ( ! get_option( 'himmah_version' ) ) {
			add_option( 'himmah_version', '0.3.0' );
		} else {
			update_option( 'himmah_version', '0.3.0' );
		}
	}
}