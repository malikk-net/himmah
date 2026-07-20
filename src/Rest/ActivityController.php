<?php
namespace MalikK\Himmah\Rest;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;

class ActivityController extends WP_REST_Controller {

    protected $namespace = 'himmah/v1';

    public function register_routes() {
        // 1. مسار تسجيل الإنجاز وحساب النقاط الكلية (POST)
        register_rest_route($this->namespace, '/log-activity', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'log_activity'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        // 2. مسار جلب النقاط والتحديات المنجزة اليوم فور تحفيز الشاشة (GET)
        register_rest_route($this->namespace, '/user-stats', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_user_stats'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
    }

    public function check_permission() {
        return is_user_logged_in();
    }

    public function log_activity(WP_REST_Request $request) {
        global $wpdb;
        $user_id      = get_current_user_id();
        $challenge_id = intval($request->get_param('challenge_id'));
        $points       = intval($request->get_param('points') ?: 10);

        if (!$challenge_id) {
            return new \WP_Error('invalid_data', 'رقم التحدي مفقود', ['status' => 400]);
        }

        $table = $wpdb->prefix . 'himmah_user_activity';

        // أدخل السجل بداخل الجدول
        $inserted = $wpdb->insert(
            $table,
            [
                'user_id'      => $user_id,
                'challenge_id' => $challenge_id,
                'points'       => $points,
                'created_at'   => current_time('mysql'),
            ],
            ['%d', '%d', '%d', '%s']
        );

        if (!$inserted) {
            return new \WP_Error('db_error', 'فشل حفظ الإنجاز في قاعدة البيانات', ['status' => 500]);
        }

        // احسب مجموع النقاط الحالي للمستخدم
        $total_points = (int) $wpdb->get_var(
            $wpdb->prepare("SELECT SUM(points) FROM {$table} WHERE user_id = %d", $user_id)
        );

        return rest_ensure_response([
            'success'      => true,
            'message'      => 'تم تسجيل الإنجاز بنجاح',
            'added_points' => $points,
            'total_points' => $total_points,
            'challenge_id' => $challenge_id,
        ]);
    }

    public function get_user_stats(WP_REST_Request $request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $table   = $wpdb->prefix . 'himmah_user_activity';

        // إجمالي النقاط
        $total_points = (int) $wpdb->get_var(
            $wpdb->prepare("SELECT SUM(points) FROM {$table} WHERE user_id = %d", $user_id)
        );

        // التحديات المنجزة اليوم فقط
        $today = current_time('Y-m-d');
        $completed_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT challenge_id FROM {$table} WHERE user_id = %d AND DATE(created_at) = %s",
                $user_id,
                $today
            )
        );

        return rest_ensure_response([
            'success'         => true,
            'total_points'    => $total_points,
            'completed_today' => array_map('intval', $completed_ids)
        ]);
    }
}