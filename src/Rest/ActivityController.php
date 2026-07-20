<?php
namespace MalikK\Himmah\Rest;

use MalikK\Himmah\Services\ActivityService;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Request;

/**
 * متحكم الـ REST API لإدارة وتسجيل الأنشطة
 */
class ActivityController extends WP_REST_Controller {

    protected $namespace = 'himmah/v1';
    protected $rest_base = 'me/activities';

    /**
     * تسجيل المسارات (Endpoints)
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'record_activity'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
    }

    /**
     * التحقق من الصلاحيات (يجب أن يكون المستخدم مسجلاً لدخوله)
     */
    public function permissions_check($request) {
        if (!is_user_logged_in()) {
            return new \WP_Error('himmah_unauthorized', 'يجب تسجيل الدخول لتنفيذ هذا الإجراء.', ['status' => 401]);
        }
        return true;
    }

    /**
     * معالجة طلب تسجيل النشاط
     */
    public function record_activity(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $params  = $request->get_json_params() ?: $request->get_body_params();

        // استخدام خدمة الأنشطة لتسجيل العملية
        $result = ActivityService::record_activity($user_id, $params);

        if (!$result) {
            return new WP_REST_Response([
                'success' => false,
                'error'   => [
                    'code'    => 'himmah_activity_failed',
                    'message' => 'تعذر تسجيل النشاط، قد يكون مسجلاً بالفعل لهذه الفترة.',
                ]
            ], 400);
        }

        // استجابة موحدة وفق معايير المشروع
        return new WP_REST_Response([
            'success' => true,
            'data'    => $result,
            'meta'    => [
                'api_version' => '1',
                'server_time' => current_time('mysql', 1),
            ]
        ], 200);
    }
}