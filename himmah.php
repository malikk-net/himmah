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

// 2. المحمل التلقائي للكلاسات (Autoloader)
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

// 3. تضمين ملف التنصيب مباشرة لمنع أخطاء التفعيل الفادحة
require_once HIMMAH_PLUGIN_DIR . 'src/Database/Installer.php';

// 4. خطة تفعيل الإضافة وإنشاء الجداول
register_activation_hook( __FILE__, array( 'Himmah\\Database\\Installer', 'run' ) );

// 5. تشغيل الإضافة عند اكتمال تحميل ووردبريس
add_action( 'plugins_loaded', function () {
	if ( class_exists( 'Himmah\\Core\\Plugin' ) ) {
		\Himmah\Core\Plugin::get_instance();
	}
} );
add_action( 'rest_api_init', function () {
    register_rest_route( 'himmah/v1', '/log-activity', array(
        'methods'             => 'POST',
        'callback'            => 'himmah_handle_log_activity',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ) );
});

function himmah_handle_log_activity( WP_REST_Request $request ) {
    global $wpdb;

    try {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return new WP_Error( 'rest_not_logged_in', 'يجب تسجيل الدخول أولاً', array( 'status' => 401 ) );
        }

        $params       = $request->get_json_params();
        $challenge_id = isset( $params['challenge_id'] ) ? intval( $params['challenge_id'] ) : 0;

        if ( ! $challenge_id ) {
            return new WP_Error( 'rest_invalid_param', 'معرف التحدي مفقود أو غير صالح', array( 'status' => 400 ) );
        }

        $table_name = $wpdb->prefix . 'himmah_user_logs';

        // التحقق من عدم تسجيل نفس التحدي مسبقاً في نفس اليوم
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND challenge_id = %d AND DATE(logged_at) = CURDATE()",
            $user_id,
            $challenge_id
        ));

        if ( $exists > 0 ) {
            return new WP_Error( 'already_logged', 'لقد قمت بتسجيل هذا التحدي مسبقاً اليوم', array( 'status' => 400 ) );
        }

        // إدخال السجل الجديد في قاعدة البيانات
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'user_id'      => $user_id,
                'challenge_id' => $challenge_id,
                'logged_at'    => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%s' )
        );

        if ( false === $inserted ) {
            throw new Exception( 'خطأ في قاعدة البيانات: ' . $wpdb->last_error );
        }

        return rest_ensure_response( array(
            'success' => true,
            'message' => 'تم تسجيل الإنجاز بنجاح',
        ) );

    } catch ( Exception $e ) {
        return new WP_Error( 'server_error', $e->getMessage(), array( 'status' => 500 ) );
    }
}