<?php
/**
 * Plugin Name: Himmah - هِمّة
 * Plugin URI:  https://malik-k.com
 * Description: إضافة هِمّة لإدارة التحديات اليومية ونظام النقاط والربط التفاعلي.
 * Version:     0.3.0
 * Author:      Malik
 * Text Domain: himmah
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// 1. تعريف ثوابت الإضافة الرئيسية
define( 'HIMMAH_VERSION', '0.3.0' );
define( 'HIMMAH_PLUGIN_FILE', __FILE__ );
define( 'HIMMAH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HIMMAH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// 2. المحمل التلقائي للكلاسات (Composer / PSR-4 Autoloader)
if ( file_exists( HIMMAH_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once HIMMAH_PLUGIN_DIR . 'vendor/autoload.php';
} else {
	spl_autoload_register( function ( $class ) {
		$prefix   = 'Himmah\\';
		$base_dir = HIMMAH_PLUGIN_DIR . 'src/';
		$len      = strlen( $prefix );

		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	} );
}

// 3. خطة تفعيل الإضافة وإنشاء الجداول
register_activation_hook( __FILE__, array( 'Himmah\\Database\\Installer', 'run' ) );

// 4. تشغيل الإضافة عند اكتمال تحميل ووردبريس
add_action( 'plugins_loaded', function () {
	if ( class_exists( 'Himmah\\Core\\Plugin' ) ) {
		\Himmah\Core\Plugin::get_instance();
	}
} );