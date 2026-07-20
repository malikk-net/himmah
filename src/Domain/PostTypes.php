<?php
namespace MalikK\Himmah\Domain;

/**
 * فئة تسجيل القوائم وأنواع المحتوى المخصص (Custom Post Types) للإضافة
 */
class PostTypes {

    /**
     * تهيئة وتسجيل القوائم وأنواع المحتوى
     */
    public static function init() {
        add_action('admin_menu', [self::class, 'register_admin_menu']);
        add_action('init', [self::class, 'register_post_types']);
    }

    /**
     * إنشاء القائمة الرئيسية "هِمّة" في الشريط الجانبي
     */
    public static function register_admin_menu() {
        add_menu_page(
            'هِمّة',                       // عنوان الصفحة
            'هِمّة',                       // اسم القائمة
            'manage_options',              // الصلاحيات
            'himmah',                      // Slug القائمة
            [self::class, 'render_main_page'], // دالة عرض الصفحة الرئيسية
            'dashicons-awards',            // أيقونة القائمة
            25                             // ترتيب الظهور في الشريط الجانبي
        );
    }

    /**
     * الصفحة الرئيسية للوحة التحكم
     */
    public static function render_main_page() {
        echo '<div class="wrap">';
        echo '<h1>🏆 لوحة تحكم هِمّة</h1>';
        echo '<p>مرحباً بك! اختر من القائمة الفرعية لإدارة <strong>التحديات</strong>، <strong>الرحلات</strong>، أو <strong>الأوسمة</strong>.</p>';
        echo '</div>';
    }

    /**
     * تسجيل Post Types وجعلها تندرج تحت قائمة "هِمّة"
     */
    public static function register_post_types() {
        // 1. CPT التحديات (Challenges)
        register_post_type('himmah_challenge', [
            'labels' => [
                'name'               => 'التحديات',
                'singular_name'      => 'تحدي',
                'add_new'            => 'أضف تحدي جديد',
                'add_new_item'       => 'إضافة تحدي جديد',
                'edit_item'          => 'تعديل التحدي',
                'menu_name'          => 'التحديات',
            ],
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'show_in_menu'       => 'himmah', // تندرج تحت قائمة "هِمّة"
            'supports'           => ['title', 'editor', 'thumbnail', 'custom-fields'],
        ]);

        // 2. CPT الرحلات (Journeys)
        register_post_type('himmah_journey', [
            'labels' => [
                'name'               => 'الرحلات',
                'singular_name'      => 'رحلة',
                'add_new'            => 'أضف رحلة جديدة',
                'add_new_item'       => 'إضافة رحلة جديدة',
                'edit_item'          => 'تعديل الرحلة',
                'menu_name'          => 'الرحلات',
            ],
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'show_in_menu'       => 'himmah', // تندرج تحت قائمة "هِمّة"
            'supports'           => ['title', 'editor', 'thumbnail', 'custom-fields'],
        ]);

        // 3. CPT الأوسمة (Badges)
        register_post_type('himmah_badge', [
            'labels' => [
                'name'               => 'الأوسمة',
                'singular_name'      => 'وسام',
                'add_new'            => 'أضف وسام جديد',
                'add_new_item'       => 'إضافة وسام جديد',
                'edit_item'          => 'تعديل الوسام',
                'menu_name'          => 'الأوسمة',
            ],
            'public'             => true,
            'has_archive'        => false,
            'show_in_rest'       => true,
            'show_in_menu'       => 'himmah', // تندرج تحت قائمة "هِمّة"
            'supports'           => ['title', 'editor', 'thumbnail', 'custom-fields'],
        ]);
    }
}