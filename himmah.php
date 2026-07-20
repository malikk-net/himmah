<?php
/**
 * Plugin Name:       Himmah - هِمّة
 * Plugin URI:        https://github.com/malikk/himmah
 * Description:       إضافة هِمّة لإدارة التحديات والأنشطة والتتبع.
 * Version:           1.0.0
 * Author:            Malik
 * Text Domain:       himmah
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

// منع الوصول المباشر للملف
if (!defined('ABSPATH')) {
    exit;
}

// 1. تعريف الثوابت الأساسية للإضافة
define('HIMMAH_VERSION', '1.0.0');
define('HIMMAH_FILE', __FILE__);
define('HIMMAH_DIR', plugin_dir_path(__FILE__));
define('HIMMAH_URL', plugin_dir_url(__FILE__));

// 2. إعداد الـ Autoloader (دعم Composer والـ Fallback التلقائي)
if (file_exists(HIMMAH_DIR . 'vendor/autoload.php')) {
    require_once HIMMAH_DIR . 'vendor/autoload.php';
} else {
    // Autoloader احتياطي للنمط PSR-4 في حال عدم استخدام Composer مباشرة
    spl_autoload_register(function ($class) {
        $prefix = 'MalikK\\Himmah\\';
        $base_dir = HIMMAH_DIR . 'src/';
        $len = strlen($prefix);

        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });
}

/**
 * 3. دالة تفعيل الإضافة (مع حماية الحزم المكررة والتتبع)
 */
if (!function_exists('activate_himmah_plugin')) {
    function activate_himmah_plugin() {
        try {
            if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            }

            // تشغيل تثبيت قاعدة البيانات
            $installer_class = 'MalikK\\Himmah\\Database\\Installer';
            if (class_exists($installer_class)) {
                $installer_class::activate();
            }
        } catch (\Throwable $e) {
            error_log('Himmah Activation Error: ' . $e->getMessage());
        }
    }
}
register_activation_hook(__FILE__, 'activate_himmah_plugin');

/**
 * 4. دالة إلغاء تفعيل الإضافة
 */
if (!function_exists('deactivate_himmah_plugin')) {
    function deactivate_himmah_plugin() {
        // تنظيف المهام المجدولة (Cron Jobs) أو Cache إن وجد
    }
}
register_deactivation_hook(__FILE__, 'deactivate_himmah_plugin');

/**
 * 5. تهيئة وتشغيل الإضافة عند اكتمال تحميل الإضافات
 */
if (!function_exists('run_himmah_plugin')) {
    function run_himmah_plugin() {
        $main_class = 'MalikK\\Himmah\\Core\\Plugin';
        if (class_exists($main_class)) {
            $main_class::instance()->init();
        }
    }
}
add_action('plugins_loaded', 'run_himmah_plugin');