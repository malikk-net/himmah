<?php

namespace Himmah\Rest;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class ActivityController
 * Handles logging user challenge activities via REST API.
 */
class ActivityController extends WP_REST_Controller {

	protected $namespace = 'himmah/v1';
	protected $rest_base = 'log-activity';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'log_activity' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	public function check_permission( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'عذرًا، يجب تسجيل الدخول لتسجيل الإنجاز.', 'himmah' ),
				array( 'status' => 401 )
			);
		}
		return true;
	}

	public function log_activity( $request ) {
		try {
			global $wpdb;

			// تنظيف أي مخزن مؤقت للمخرجات فوراً لمنع تلوث الـ JSON
			if ( ob_get_length() ) {
				ob_clean();
			}

			$user_id      = get_current_user_id();
			$params       = $request->get_json_params();
			$challenge_id = isset( $params['challenge_id'] ) ? absint( $params['challenge_id'] ) : absint( $request->get_param( 'challenge_id' ) );

			if ( ! $challenge_id ) {
				return new WP_Error( 'invalid_challenge', __( 'معرف التحدي غير صالح.', 'himmah' ), array( 'status' => 400 ) );
			}

			// التحقق مما إذا كان المستخدم قد أتم هذا التحدي مسبقاً
			$completed = get_user_meta( $user_id, 'himmah_completed_challenges', true );
			if ( ! is_array( $completed ) ) {
				$completed = array();
			}
			if ( in_array( $challenge_id, array_map( 'intval', $completed ), true ) ) {
				return new WP_Error( 'already_completed', __( 'لقد قمت بإنجاز هذا التحدي مسبقاً.', 'himmah' ), array( 'status' => 400 ) );
			}

			// جلب نقاط التحدي أو القيمة الافتراضية 10
			$points = (int) get_post_meta( $challenge_id, '_himmah_points', true );
			if ( $points <= 0 ) {
				$points = 10;
			}

			$table_name = $wpdb->prefix . 'himmah_activities';

			// التأكد من وجود الجدول
			if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
				if ( class_exists( 'Himmah\Database\Installer' ) ) {
					\Himmah\Database\Installer::run();
				}
			}

			// إدخال الحركة في الجدول
			$inserted = $wpdb->insert(
				$table_name,
				array(
					'user_id'      => $user_id,
					'challenge_id' => $challenge_id,
					'points'       => $points,
					'created_at'   => current_time( 'mysql' ),
				),
				array( '%d', '%d', '%d', '%s' )
			);

			if ( false === $inserted ) {
				return new WP_Error( 'db_error', __( 'حدث خطأ أثناء حفظ النشاط في قاعدة البيانات: ', 'himmah' ) . $wpdb->last_error, array( 'status' => 500 ) );
			}

			// تحديث إجمالي نقاط المستخدم
			$total_points = (int) get_user_meta( $user_id, 'himmah_total_points', true );
			$total_points += $points;
			update_user_meta( $user_id, 'himmah_total_points', $total_points );

			// تحديث قائمة التحديات المكتملة
			$completed[] = $challenge_id;
			update_user_meta( $user_id, 'himmah_completed_challenges', $completed );

			$response = new WP_REST_Response(
				array(
					'success'      => true,
					'message'      => __( 'تم تسجيل إنجاز التحدي بنجاح!', 'himmah' ),
					'total_points' => $total_points,
					'challenge_id' => $challenge_id,
				),
				200
			);

			$response->header( 'Content-Type', 'application/json; charset=utf-8' );
			return $response;

		} catch ( \Throwable $e ) {
			return new WP_Error( 'server_exception', $e->getMessage(), array( 'status' => 500 ) );
		}
	}
}