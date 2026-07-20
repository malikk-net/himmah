<?php
namespace MalikK\Himmah\Rest;

/**
 * فئة التحكم بمؤشرات ومسارات الـ REST API لإضافة هِمّة
 */
class ActivityController {

    /**
     * تهيئة وتسجيل نقاط النهاية (Endpoints)
     */
    public static function init() {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    /**
     * تسجيل مسارات API المخصصة
     */
    public static function register_routes() {
        $namespace = 'himmah/v1';

        // 1. مسار جلب الأنشطة والتحديات اليومية
        register_rest_route($namespace, '/activities', [
            [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_activities'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods'             => 'POST',
                'callback'            => [self::class, 'log_activity'],
                'permission_callback' => function () {
                    return is_user_logged_in();
                },
            ],
        ]);
    }

    /**
     * الاستجابة لطلب جلب الأنشطة
     */
    public static function get_activities($request) {
        return new \WP_REST_Response([
            'success' => true,
            'message' => 'Himmah API is working perfectly!',
            'data'    => []
        ], 200);
    }

    /**
     * الاستجابة لطلب تسجيل نشاط جديد
     */
    public static function log_activity($request) {
        $user_id = get_current_user_id();

        return new \WP_REST_Response([
            'success' => true,
            'message' => 'تم تسجيل النشاط بنجاح!',
            'user_id' => $user_id
        ], 200);
    }
}