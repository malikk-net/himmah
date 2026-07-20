<?php

namespace Himmah\Domain;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class PostTypes
 * Registers custom post types for Himmah plugin.
 */
class PostTypes {

	/**
	 * Register hooks for Custom Post Types.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_challenge_post_type' ) );
	}

	/**
	 * Register "himmah_challenge" Custom Post Type.
	 */
	public static function register_challenge_post_type() {
		$labels = array(
			'name'               => __( 'التحديات', 'himmah' ),
			'singular_name'      => __( 'تحدي', 'himmah' ),
			'menu_name'          => __( 'تحديات هِمّة', 'himmah' ),
			'all_items'          => __( 'جميع التحديات', 'himmah' ),
			'add_new'            => __( 'إضافة تحدي جديد', 'himmah' ),
			'add_new_item'       => __( 'إضافة تحدي جديد', 'himmah' ),
			'edit_item'          => __( 'تعديل التحدي', 'himmah' ),
			'new_item'           => __( 'تحدي جديد', 'himmah' ),
			'view_item'          => __( 'عرض التحدي', 'himmah' ),
			'search_items'       => __( 'البحث في التحديات', 'himmah' ),
			'not_found'          => __( 'لم يتم العثور على تحديات', 'himmah' ),
			'not_found_in_trash' => __( 'لا توجد تحديات في سلة المهملات', 'himmah' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'has_archive'         => true,
			'publicly_queryable'  => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'challenges' ),
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'show_in_rest'        => true, // تمكين محرر Gutenberg و REST API
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-flag',
		);

		register_post_type( 'himmah_challenge', $args );
	}
}