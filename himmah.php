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

define('HIMMAH_VERSION', '1.0.0');
define('HIMMAH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HIMMAH_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * المحمّل التلقائي (Autoloader) الذكي والسامح بحالة الأحرف لسيرفرات Linux
 */
spl_autoload_register(function ($class) {
    $prefix = 'MalikK\\Himmah\\';
    $base_dir = HIMMAH_PLUGIN_DIR . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $parts = explode('\\', $relative_class);
    
    // اسم الملف كما هو بملف PHP (مثال: ActivityController.php)
    $filename = array_pop($parts) . '.php';

    // 1. تجربة المسار بحسب حالة الأحرف الأصلية
    $path_exact = $base_dir . (count($parts) ? implode('/', $parts) . '/' : '') . $filename;
    if (file_exists($path_exact)) {
        require_once $path_exact;
        return;
    }

    $relative_class = substr($class, $len);
    
    // 1. تجربة المسار الأصلي كما هو
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // 2. تجربة المسار باسم المجلد بحروف صغيرة (Linux case-sensitivity fallback)
    $parts = explode('\\', $relative_class);
    $filename = array_pop($parts);
    $dir_lower = implode('/', array_map('strtolower', $parts));
    
    $file_dir_lower = $base_dir . ($dir_lower ? $dir_lower . '/' : '') . $filename . '.php';
    if (file_exists($file_dir_lower)) {
        require_once $file_dir_lower;
        return;
    }

    // 3. تجربة المسار الكامل بحروف صغيرة
    $file_all_lower = $base_dir . strtolower(str_replace('\\', '/', $relative_class)) . '.php';
    if (file_exists($file_all_lower)) {
        require_once $file_all_lower;
        return;
    }
});

// تحميل Composer Autoloader إذا كان متوفراً
if (file_exists(HIMMAH_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once HIMMAH_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * دالة التفعيل الفاحصة لكشف الملف المسبب للخطأ فوراً
 */
function activate_himmah_plugin() {
    try {
        if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        // قائمة الفئات المطلوبة للتأكد من تحميلها بالترتيب
        $required_classes = [
            'MalikK\\Himmah\\Database\\Installer',
            'MalikK\\Himmah\\Core\\Plugin',
            'MalikK\\Himmah\\Domain\\PostTypes',
            'MalikK\\Himmah\\Rest\\ActivityController',
            'MalikK\\Himmah\\Blocks\\DashboardBlock',
            'MalikK\\Himmah\\Blocks\\ChallengeListBlock',
        ];

        foreach ($required_classes as $class_name) {
            if (!class_exists($class_name)) {
                wp_die(
                    '<div style="direction:rtl; font-family:sans-serif; padding:20px; background:#fff; border:2px solid #ef4444; border-radius:8px;">' .
                    '<h3 style="color:#dc2626; margin-top:0;">❌ فشل تحميل الفئة:</h3>' .
                    '<p>لم يتمكن Autoloader من العثور على: <code>' . esc_html($class_name) . '</code></p>' .
                    '<p>💡 <strong>الحل:</strong> تحقق من وجود الملف المخصص لهذه الفئة بداخل مجلد <code>src/</code> ومطابقة حالة الأحرف اسم الملف والمجلد.</p>' .
                    '</div>'
                );
            }
        }

        // تشغيل تثبيت قاعدة البيانات
        \MalikK\Himmah\Database\Installer::activate();

    } catch (\Throwable $e) {
        wp_die(
            '<div style="direction:rtl; font-family:sans-serif; padding:20px; background:#fff; border:2px solid #ef4444; border-radius:8px;">' .
            '<h3 style="color:#dc2626; margin-top:0;">🚨 حدث خطأ أثناء التفعيل:</h3>' .
            '<p><strong>الرسالة:</strong> <code>' . esc_html($e->getMessage()) . '</code></p>' .
            '<p><strong>الملف:</strong> <code>' . esc_html($e->getFile()) . '</code></p>' .
            '<p><strong>السطر:</strong> <code>' . esc_html($e->getLine()) . '</code></p>' .
            '</div>'
        );
    }
}
register_activation_hook(__FILE__, 'activate_himmah_plugin');