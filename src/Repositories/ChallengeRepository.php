<?php

namespace Himmah\Repositories;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class ChallengeRepository
 * Handles data queries for Himmah challenges custom post type.
 */
class ChallengeRepository {

	/**
	 * Get active daily challenges.
	 *
	 * @param int $limit
	 * @return array
	 */
	public function get_active_challenges( $limit = 5 ) {
		$args = array(
			'post_type'      => 'himmah_challenge',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query      = new WP_Query( $args );
		$challenges = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				$points  = get_post_meta( $post_id, '_himmah_points', true );

				$challenges[] = array(
					'id'      => $post_id,
					'title'   => get_the_title(),
					'content' => get_the_excerpt(),
					'points'  => $points ? (int) $points : 10,
				);
			}
			wp_reset_postdata();
		}

		return $challenges;
	}
}