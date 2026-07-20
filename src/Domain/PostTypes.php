<?php
namespace MalikK\Himmah\Domain;

/**
 * فئة تسجيل أنواع المحتوى المخصص (Custom Post Types) للإضافة
 */
class PostTypes {

    /**
     * تهيئة وتسجيل أنواع المحتوى
     */
    public static function init() {
        add_action('init', [self::class, 'register_post_types']);
    }

    /**
     * تسجيل Post Types الخاصة بالتحديات والرحلات والأوسمة
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
                'menu_name'          => 'تحديات هِمّة',
            ],
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-awards',
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
                'menu_name'          => 'رحلات هِمّة',
            ],
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-location-alt',
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
                'menu_name'          => 'أوسمة هِمّة',
            ],
            'public'             => true,
            'has_archive'        => false,
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-shield',
            'supports'           => ['title', 'editor', 'thumbnail', 'custom-fields'],
        ]);
    }
}