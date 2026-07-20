<?php
namespace MalikK\Himmah\Rest;

use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Request;

/**
 * متحكم الـ REST API لعرض بيانات لوحة اليوم والإحصائيات
 */
class DashboardController extends WP_REST_Controller {

    protected $namespace = 'himmah/v1';
    protected $rest_base = 'me/dashboard';

    /**
     * تسجيل المسارات
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_dashboard_data'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
    }

    /**
     * التحقق من تسجيل دخول المستخدم
     */
    public function permissions_check($request) {
        if (!is_user_logged_in()) {
            return new \WP_Error('himmah_unauthorized', 'يجب تسجيل الدخول لعرض بيانات اللوحة.', ['status' => 401]);
        }
        return true;
    }

    /**
     * جلب بيانات لوحة اليوم للمستخدم الحالي
     */
    public function get_dashboard_data(WP_REST_Request $request) {
        global $wpdb;
        $user_id    = get_current_user_id();
        $today_date = current_time('Y-m-d');

        // 1. جلب إحصائيات النقاط والإنجازات
        $stats_table = $wpdb->prefix . 'himmah_user_stats';
        $stats       = $wpdb->get_row(
            $wpdb->prepare("SELECT total_activity_points, completed_challenges_count FROM $stats_table WHERE user_id = %d", $user_id)
        );

        // 2. جلب بيانات السلسلة وأيام الرحمة
        $streaks_table = $wpdb->prefix . 'himmah_streaks';
        $streak        = $wpdb->get_row(
            $wpdb->prepare("SELECT current_streak, mercy_days_balance FROM $streaks_table WHERE user_id = %d", $user_id)
        );

        // 3. جلب تفضيلات الهدف اليومي
        $pref_table  = $wpdb->prefix . 'himmah_user_preferences';
        $preferences = $wpdb->get_row(
            $wpdb->prepare("SELECT daily_goal_count FROM $pref_table WHERE user_id = %d", $user_id)
        );

        $daily_goal = $preferences ? intval($preferences->daily_goal_count) : 3;

        // 4. جلب أنشطة اليوم المكتملة
        $activities_table = $wpdb->prefix . 'himmah_activities';
        $today_activities = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, uuid, activity_key, points_eligible, completed_at_utc 
                 FROM $activities_table 
                 WHERE user_id = %d AND local_date = %s AND status = 'completed'",
                $user_id, $today_date
            )
        );

        $completed_today_count = count($today_activities);
        $completion_percentage = $daily_goal > 0 ? min(100, round(($completed_today_count / $daily_goal) * 100)) : 0;

        $response_data = [
            'today_date' => $today_date,
            'summary'    => [
                'completion_percentage'   => $completion_percentage,
                'completed_goals_today'   => $completed_today_count,
                'daily_goal_target'       => $daily_goal,
                'total_activity_points'   => $stats ? intval($stats->total_activity_points) : 0,
                'completed_total_count'   => $stats ? intval($stats->completed_challenges_count) : 0,
                'current_streak'          => $streak ? intval($streak->current_streak) : 0,
                'mercy_days_balance'      => $streak ? intval($streak->mercy_days_balance) : 0,
            ],
            'today_activities' => $today_activities,
        ];

        return new WP_REST_Response([
            'success' => true,
            'data'    => $response_data,
            'meta'    => [
                'api_version' => '1',
                'server_time' => current_time('mysql', 1),
            ]
        ], 200);
    }
}