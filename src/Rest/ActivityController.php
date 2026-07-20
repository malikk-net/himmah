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
 * Handles REST API endpoints for Himmah activity logging and points management.
 */
class ActivityController extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'himmah/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'log-activity';

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {
		// POST /wp-json/himmah/v1/log-activity
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'log_activity' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_endpoint_args(),
				),
			)
		);

		// Alternative route: POST /wp-json/himmah/v1/activities
		register_rest_route(
			$this->namespace,
			'/activities',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'log_activity' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_endpoint_args(),
				),
			)
		);
	}

	/**
	 * Check if user is logged in and authorized.
	 *
	 * @param WP_REST_Request $request
	 * @return bool|WP_Error
	 */
	public function check_permission( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'عذرًا، يجب عليك تسجيل الدخول لتسجيل التحديات.', 'himmah' ),
				array( 'status' => 401 )
			);
		}
		return true;
	}

	/**
	 * Get arguments definition for the endpoint.
	 *
	 * @return array
	 */
	public function get_endpoint_args() {
		return array(
			'challenge_id' => array(
				'required'          => true,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'is_numeric',
			),
			'points' => array(
				'required'          => false,
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'is_numeric',
			),
		);
	}

	/**
	 * Log activity and update user points.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function log_activity( $request ) {
		global $wpdb;

		$user_id      = get_current_user_id();
		$challenge_id = (int) $request->get_param( 'challenge_id' );
		$points       = (int) $request->get_param( 'points' );

		if ( $points <= 0 ) {
			$points = 10;
		}

		$table_name = $wpdb->prefix . 'himmah_activities';

		// 1. الحفظ في جدول قاعدة البيانات إذا كان الجدول موجوداً
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
			$wpdb->insert(
				$table_name,
				array(
					'user_id'      => $user_id,
					'challenge_id' => $challenge_id,
					'points'       => $points,
					'created_at'   => current_time( 'mysql' ),
				),
				array( '%d', '%d', '%d', '%s' )
			);
		}

		// 2. تحديث سجل التحديات المكتملة في meta للمستخدم
		$user_activities = get_user_meta( $user_id, 'himmah_completed_challenges', true );
		if ( ! is_array( $user_activities ) ) {
			$user_activities = array();
		}

		$user_activities[] = array(
			'challenge_id' => $challenge_id,
			'points'       => $points,
			'completed_at' => current_time( 'mysql' ),
		);
		update_user_meta( $user_id, 'himmah_completed_challenges', $user_activities );

		// 3. احتساب وإضافة النقاط الإجمالية
		$current_total_points = (int) get_user_meta( $user_id, 'himmah_total_points', true );
		$new_total_points     = $current_total_points + $points;
		update_user_meta( $user_id, 'himmah_total_points', $new_total_points );

		return new WP_REST_Response(
			array(
				'success'      => true,
				'message'      => __( 'تم تسجيل إنجاز التحدي بنجاح! 🎉', 'himmah' ),
				'challenge_id' => $challenge_id,
				'points_added' => $points,
				'total_points' => $new_total_points,
			),
			200
		);
	}
}