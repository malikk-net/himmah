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
    exit; // منع الوصول المباشر للملف
}

// تعريف الثوابت الأساسية للإضافة
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
        require $file;
    }
});

// تحميل Composer Autoloader إذا كان متوفراً
if (file_exists(HIMMAH_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once HIMMAH_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * دالة التفعيل (تُنفّذ عند تفعيل الإضافة لإنشاء جداول قاعدة البيانات)
 */
function activate_himmah_plugin() {
    if (class_exists('MalikK\\Himmah\\Database\\Installer')) {
        \MalikK\Himmah\Database\Installer::activate();
    }
}
register_activation_hook(__FILE__, 'activate_himmah_plugin');

/**
 * دالة إلغاء التفعيل
 */
function deactivate_himmah_plugin() {
    // يمكن إضافة خطط تنظيف الخادم أو المجدولات هنا لاحقاً
}
register_deactivation_hook(__FILE__, 'deactivate_himmah_plugin');

/**
 * تشغيل الإضافة فور اكتمال تحميل إضافات ووردبريس
 */
add_action('plugins_loaded', function() {
    if (class_exists('MalikK\\Himmah\\Core\\Plugin')) {
        \MalikK\Himmah\Core\Plugin::init();
    }
});