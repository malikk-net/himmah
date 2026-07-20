<?php
namespace MalikK\Himmah\Core;

/**
 * فئة النواة الرئيسية لإدارة الإضافة
 */
class Plugin {

    private static $instance = null;

    /**
     * الحصول على نسخة واحدة من الفئة (Singleton Pattern)
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * تهيئة الإضافة
     */
    public static function init() {
        $plugin = self::instance();
        $plugin->register_hooks();
    }

    /**
     * تسجيل الخطافات (Hooks) الخاصة بالإضافة
     */
    private function register_hooks() {
        add_action('init', [$this, 'setup_capabilities']);
    }

    /**
     * إضافة الصلاحيات المخصصة لمدير الموقع
     */
    public function setup_capabilities() {
        $role = get_role('administrator');
        if ($role) {
            $capabilities = [
                'himmah_manage_settings',
                'himmah_manage_challenges',
                'himmah_manage_journeys',
                'himmah_manage_programs',
                'himmah_manage_badges',
                'himmah_manage_groups',
                'himmah_manage_points',
                'himmah_view_reports',
            ];

            foreach ($capabilities as $cap) {
                if (!$role->has_cap($cap)) {
                    $role->add_cap($cap);
                }
            }
        }
    }
}