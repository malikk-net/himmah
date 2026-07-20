<?php
/**
 * Plugin Name:       هِمّة - Himmah
 * Plugin URI:        https://github.com/malikk-net/himmah
 * Description:       إضافة هِمّة لبناء العادات وتتبع الأنشطة والإنجازات اليومية.
 * Version:           1.0.0
 * Author:            MalikK
 * Author URI:        https://github.com/malikk-net
 * Text Domain:       himmah
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// كاشف الأخطاء المباشر للإضافة (في حال وجود أي خطأ فادح)
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        wp_die(
            '<div style="direction:rtl; font-family:sans-serif; background:#fff; padding:20px; border:2px solid #ef4444; border-radius:8px;">' .
            '<h3 style="color:#dc2626; margin-top:0;">🚨 تم كشف الخطأ الفادح:</h3>' .
            '<p><strong>الرسالة:</strong> <code style="color:#b91c1c;">' . esc_html($error['message']) . '</code></p>' .
            '<p><strong>الملف المسبب:</strong> <code>' . esc_html($error['file']) . '</code></p>' .
            '<p><strong>رقم السطر:</strong> <code>' . esc_html($error['line']) . '</code></p>' .
            '</div>'
        );
    }
});

define('HIMMAH_VERSION', '1.0.0');
define('HIMMAH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HIMMAH_PLUGIN_URL', plugin_dir_url(__FILE__));

// تسجيل مسجل الفئات التلقائي (Autoloader)
spl_autoload_register(function ($class) {
    $prefix = 'MalikK\\Himmah\\';
    $base_dir = HIMMAH_PLUGIN_DIR . 'src/';

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

// تحميل Composer Autoloader إذا كان متوفراً
if (file_exists(HIMMAH_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once HIMMAH_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * دالة التفعيل مع صيد الأخطاء وإظهارها فوراً
 */
function activate_himmah_plugin() {
    try {
        $installer_class = 'MalikK\\Himmah\\Database\\Installer';
        if (class_exists($installer_class)) {
            $installer_class::activate();
        } else {
            wp_die('❌ <strong>خطأ في التحميل:</strong> لم يتم العثور على الفئة <code>' . $installer_class . '</code>. تحقق من مسار مجلد src وحالة الأحرف (Case-sensitivity).');
        }
    } catch (\Throwable $e) {
        wp_die(
            '<h3>❌ حدث خطأ أثناء تفعيل إضافة هِمّة:</h3>' .
            '<p><strong>الرسالة:</strong> ' . $e->getMessage() . '</p>' .
            '<p><strong>الملف:</strong> ' . $e->getFile() . '</p>' .
            '<p><strong>السطر:</strong> ' . $e->getLine() . '</p>'
        );
    }
}
register_activation_hook(__FILE__, 'activate_himmah_plugin');

function deactivate_himmah_plugin() {
    // خطط التنظيف إن وجدت
}
register_deactivation_hook(__FILE__, 'deactivate_himmah_plugin');

add_action('plugins_loaded', function() {
    if (class_exists('MalikK\\Himmah\\Core\\Plugin')) {
        \MalikK\Himmah\Core\Plugin::init();
    }
});