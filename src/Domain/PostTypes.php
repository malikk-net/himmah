<?php
namespace MalikK\Himmah\Domain;

/**
 * تسجيل أنواع المحتوى المخصصة والتصنيفات لإضافة هِمّة
 */
class PostTypes {

    /**
     * تسجيل كافة أنواع المحتوى المخصصة
     */
    public static function register() {
        self::register_challenges();
        self::register_programs();
        self::register_journeys();
        self::register_badges();
    }

    /**
     * نوع محتوى التحديات والعادات (hm_challenge)
     */
    private static function register_challenges() {
        $labels = [
            'name'               => 'التحديات والعادات',
            'singular_name'      => 'تحدي',
            'add_new'            => 'إضافة تحدٍ جديد',
            'add_new_item'       => 'إضافة تحدٍ جديد',
            'edit_item'          => 'تعديل التحدي',
            'new_item'           => 'تحدٍ جديد',
            'view_item'          => 'عرض التحدي',
            'search_items'       => 'البحث في التحديات',
            'not_found'          => 'لم يتم العثور على تحديات',
            'menu_name'          => 'تحديات هِمّة',
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'supports'            => ['title', 'editor', 'custom-fields'],
            'capability_type'     => 'post',
            'menu_icon'           => 'dashicons-yes-alt',
        ];

        register_post_type('hm_challenge', $args);
    }

    /**
     * نوع محتوى البرامج (hm_program)
     */
    private static function register_programs() {
        $labels = [
            'name'               => 'البرامج',
            'singular_name'      => 'برنامج',
            'add_new'            => 'إضافة برنامج جديد',
            'add_new_item'       => 'إضافة برنامج جديد',
            'edit_item'          => 'تعديل البرنامج',
            'menu_name'          => 'برامج هِمّة',
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'supports'            => ['title', 'editor', 'thumbnail'],
            'menu_icon'           => 'dashicons-category',
        ];

        register_post_type('hm_program', $args);
    }

    /**
     * نوع محتوى الرحلات (hm_journey)
     */
    private static function register_journeys() {
        $labels = [
            'name'               => 'الرحلات',
            'singular_name'      => 'رحلة',
            'add_new'            => 'إضافة رحلة جديدة',
            'add_new_item'       => 'إضافة رحلة جديدة',
            'edit_item'          => 'تعديل الرحلة',
            'menu_name'          => 'رحلات هِمّة',
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'supports'            => ['title', 'editor', 'thumbnail'],
            'menu_icon'           => 'dashicons-location-alt',
        ];

        register_post_type('hm_journey', $args);
    }

    /**
     * نوع محتوى الأوسمة (hm_badge)
     */
    private static function register_badges() {
        $labels = [
            'name'               => 'الأوسمة',
            'singular_name'      => 'وسام',
            'add_new'            => 'إضافة وسام جديد',
            'add_new_item'       => 'إضافة وسام جديد',
            'edit_item'          => 'تعديل الوسام',
            'menu_name'          => 'أوسمة هِمّة',
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'supports'            => ['title', 'editor', 'thumbnail'],
            'menu_icon'           => 'dashicons-awards',
        ];

        register_post_type('hm_badge', $args);
    }
}