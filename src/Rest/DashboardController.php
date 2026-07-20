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
 * Class DashboardController
 * Handles REST API endpoints for fetching Himmah user dashboard stats & progress.
 */
class DashboardController extends WP_REST_Controller {

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
	protected $rest_base = 'dashboard';

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {
		// GET /wp-json/himmah/v1/dashboard
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_dashboard_data' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Check if user is logged in.
	 *
	 * @param WP_REST_Request $request
	 * @return bool|WP_Error
	 */
	public function check_permission( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'عذرًا، يجب عليك تسجيل الدخول لعرض بيانات لوحة التحكم.', 'himmah' ),
				array( 'status' => 401 )
			);
		}
		return true;
	}

	/**
	 * Retrieve dashboard data for current user.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_dashboard_data( $request ) {
		$user_id      = get_current_user_id();
		$user_data    = get_userdata( $user_id );
		$total_points = (int) get_user_meta( $user_id, 'himmah_total_points', true );

		$completed_challenges = get_user_meta( $user_id, 'himmah_completed_challenges', true );
		if ( ! is_array( $completed_challenges ) ) {
			$completed_challenges = array();
		}

		// Calculate tier/level based on total points
		$tier = $this->calculate_user_tier( $total_points );

		$response = array(
			'success'           => true,
			'user_id'           => $user_id,
			'display_name'      => $user_data ? $user_data->display_name : '',
			'total_points'      => $total_points,
			'tier_name'         => $tier['name'],
			'tier_badge'        => $tier['badge'],
			'next_tier_points'  => $tier['next_points'],
			'completed_count'   => count( $completed_challenges ),
			'recent_activities' => array_slice( array_reverse( $completed_challenges ), 0, 5 ),
		);

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Determine user tier based on points.
	 *
	 * @param int $points
	 * @return array
	 */
	private function calculate_user_tier( $points ) {
		if ( $points >= 1000 ) {
			return array(
				'name'        => 'البلاتيني',
				'badge'       => '💎 البلاتيني',
				'next_points' => 0,
			);
		} elseif ( $points >= 500 ) {
			return array(
				'name'        => 'الذهبي',
				'badge'       => '🥇 الذهبي',
				'next_points' => 1000 - $points,
			);
		} elseif ( $points >= 200 ) {
			return array(
				'name'        => 'الفضي',
				'badge'       => '🥈 الفضي',
				'next_points' => 500 - $points,
			);
		}

		return array(
			'name'        => 'البرونزي',
			'badge'       => '🥉 البرونزي',
			'next_points' => 200 - $points,
		);
	}
}