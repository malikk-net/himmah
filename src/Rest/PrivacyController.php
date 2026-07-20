<?php
namespace MalikK\Himmah\Rest;

use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Request;

/**
 * متحكم الـ REST API لإدارة إعدادات الخصوصية
 */
class PrivacyController extends WP_REST_Controller {

    protected $namespace = 'himmah/v1';
    protected $rest_base = 'me/privacy';

    /**
     * تسجيل المسارات
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'get_privacy_settings'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'update_privacy_settings'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
    }

    /**
     * التحقق من تسجيل دخول المستخدم
     */
    public function permissions_check($request) {
        if (!is_user_logged_in()) {
            return new \WP_Error('himmah_unauthorized', 'يجب تسجيل الدخول للوصول إلى إعدادات الخصوصية.', ['status' => 401]);
        }
        return true;
    }

    /**
     * جلب إعدادات الخصوصية للمستخدم
     */
    public function get_privacy_settings(WP_REST_Request $request) {
        global $wpdb;
        $user_id     = get_current_user_id();
        $table_name  = $wpdb->prefix . 'himmah_privacy_settings';

        $settings = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id)
        );

        // القيم الافتراضية في حال عدم وجود سجل سابق (الخصوصية أولاً)
        if (!$settings) {
            $settings = (object) [
                'user_id'                => $user_id,
                'default_privacy_level'  => 'private',
                'leaderboard_opt_in'     => 0,
                'show_streak_on_profile' => 0,
                'show_badges_on_profile' => 1,
            ];
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'default_privacy_level'  => $settings->default_privacy_level,
                'leaderboard_opt_in'     => (bool) $settings->leaderboard_opt_in,
                'show_streak_on_profile' => (bool) $settings->show_streak_on_profile,
                'show_badges_on_profile' => (bool) $settings->show_badges_on_profile,
            ],
            'meta'    => [
                'api_version' => '1',
                'server_time' => current_time('mysql', 1),
            ]
        ], 200);
    }

    /**
     * تحديث إعدادات الخصوصية للمستخدم
     */
    public function update_privacy_settings(WP_REST_Request $request) {
        global $wpdb;
        $user_id    = get_current_user_id();
        $params     = $request->get_json_params() ?: $request->get_body_params();
        $table_name = $wpdb->prefix . 'himmah_privacy_settings';

        $default_privacy  = sanitize_text_field($params['default_privacy_level'] ?? 'private');
        $leaderboard_opt  = isset($params['leaderboard_opt_in']) ? (int) (bool) $params['leaderboard_opt_in'] : 0;
        $show_streak      = isset($params['show_streak_on_profile']) ? (int) (bool) $params['show_streak_on_profile'] : 0;
        $show_badges      = isset($params['show_badges_on_profile']) ? (int) (bool) $params['show_badges_on_profile'] : 1;

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO $table_name (user_id, default_privacy_level, leaderboard_opt_in, show_streak_on_profile, show_badges_on_profile)
                 VALUES (%d, %s, %d, %d, %d)
                 ON DUPLICATE KEY UPDATE
                    default_privacy_level = VALUES(default_privacy_level),
                    leaderboard_opt_in = VALUES(leaderboard_opt_in),
                    show_streak_on_profile = VALUES(show_streak_on_profile),
                    show_badges_on_profile = VALUES(show_badges_on_profile)",
                $user_id, $default_privacy, $leaderboard_opt, $show_streak, $show_badges
            )
        );

        return new WP_REST_Response([
            'success' => true,
            'data'    => [
                'default_privacy_level'  => $default_privacy,
                'leaderboard_opt_in'     => (bool) $leaderboard_opt,
                'show_streak_on_profile' => (bool) $show_streak,
                'show_badges_on_profile' => (bool) $show_badges,
            ],
            'meta'    => [
                'api_version' => '1',
                'server_time' => current_time('mysql', 1),
            ]
        ], 200);
    }
}