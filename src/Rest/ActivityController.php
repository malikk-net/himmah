<?php
namespace MalikK\Himmah\Rest;

/**
 * فئة التحكم بمؤشرات ومسارات الـ REST API لإضافة هِمّة
 */
class ActivityController {

    public static function init() {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes() {
        $namespace = 'himmah/v1';

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

    public static function get_activities($request) {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'يرجى تسجيل الدخول لعرض الأنشطة.'
            ], 401);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'himmah_user_activity';
        $today = current_time('Y-m-d');

        $activities = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d AND DATE(created_at) = %s",
                $user_id,
                $today
            )
        );

        $total_points = (int) get_user_meta($user_id, 'himmah_total_points', true);

        return new \WP_REST_Response([
            'success'      => true,
            'user_id'      => $user_id,
            'total_points' => $total_points,
            'activities'   => $activities
        ], 200);
    }

    public static function log_activity($request) {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'عذراً، يجب تسجيل الدخول لتأكيد الإنجاز.'
            ], 401);
        }

        $params = $request->get_json_params();
        $challenge_id = isset($params['challenge_id']) ? intval($params['challenge_id']) : 0;

        if (!$challenge_id) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'معرّف التحدي غير صحيح.'
            ], 400);
        }

        global $wpdb;
        $activity_table = $wpdb->prefix . 'himmah_user_activity';
        $today = current_time('Y-m-d');

        $already_completed = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$activity_table} WHERE user_id = %d AND challenge_id = %d AND DATE(created_at) = %s",
                $user_id,
                $challenge_id,
                $today
            )
        );

        if ($already_completed > 0) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'لقد قمت بإنجاز هذا التحدي اليوم بالفعل! 🌟'
            ], 200);
        }

        $points_earned = 10;
        $inserted = $wpdb->insert(
            $activity_table,
            [
                'user_id'      => $user_id,
                'challenge_id' => $challenge_id,
                'points'       => $points_earned,
                'created_at'   => current_time('mysql'),
            ],
            ['%d', '%d', '%d', '%s']
        );

        if (!$inserted) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الإنجاز في قاعدة البيانات.'
            ], 500);
        }

        $current_points = (int) get_user_meta($user_id, 'himmah_total_points', true);
        $new_points = $current_points + $points_earned;
        update_user_meta($user_id, 'himmah_total_points', $new_points);

        return new \WP_REST_Response([
            'success'      => true,
            'message'      => 'كفو! تم تسجيل الإنجاز وإضافة 10 نقاط لحسابك 🎉',
            'points_earned' => $points_earned,
            'total_points'  => $new_points,
        ], 200);
    }
}