<?php

namespace Himmah\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class MetaBoxes
 * Handles custom meta boxes for Himmah post types in WordPress Admin.
 */
class MetaBoxes {

	/**
	 * Register hooks for Meta Boxes.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_challenge_meta_boxes' ) );
		add_action( 'save_post_himmah_challenge', array( __CLASS__, 'save_challenge_meta' ) );
	}

	/**
	 * Add Meta Box for Himmah Challenge Post Type.
	 */
	public static function add_challenge_meta_boxes() {
		add_meta_box(
			'himmah_challenge_settings',
			__( 'إعدادات التحدي - هِمّة', 'himmah' ),
			array( __CLASS__, 'render_challenge_meta_box' ),
			'himmah_challenge',
			'side',
			'high'
		);
	}

	/**
	 * Render Meta Box HTML Content.
	 *
	 * @param \WP_Post $post
	 */
	public static function render_challenge_meta_box( $post ) {
		wp_nonce_field( 'himmah_save_challenge_meta', 'himmah_challenge_meta_nonce' );

		$points = get_post_meta( $post->ID, '_himmah_points', true );
		if ( '' === $points ) {
			$points = 10; // القيمة الافتراضية
		}
		?>
		<div style="padding: 5px 0;">
			<label for="himmah_points_field" style="display:block; font-weight:bold; margin-bottom: 8px;">
				<?php esc_html_e( 'عدد نقاط التحدي:', 'himmah' ); ?>
			</label>
			<input 
				type="number" 
				id="himmah_points_field" 
				name="himmah_points" 
				value="<?php echo esc_attr( $points ); ?>" 
				min="1" 
				step="1" 
				style="width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #ccc;" 
			/>
			<p style="margin-top: 6px; color: #666; font-size: 12px;">
				<?php esc_html_e( 'المكافأة التي يحصل عليها المستخدم عند إنجاز هذا التحدي.', 'himmah' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save Meta Box Data on Post Save.
	 *
	 * @param int $post_id
	 */
	public static function save_challenge_meta( $post_id ) {
		// 1. التحقق من مفتاح الأمان (Nonce)
		if ( ! isset( $_POST['himmah_challenge_meta_nonce'] ) || ! wp_verify_nonce( $_POST['himmah_challenge_meta_nonce'], 'himmah_save_challenge_meta' ) ) {
			return;
		}

		// 2. تجنب الحفظ التلقائي (Autosave)
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// 3. التحقق من صلاحيات المستخدم
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// 4. حفظ أو تحديث قيمة النقاط
		if ( isset( $_POST['himmah_points'] ) ) {
			$points = absint( $_POST['himmah_points'] );
			if ( $points <= 0 ) {
				$points = 10;
			}
			update_post_meta( $post_id, '_himmah_points', $points );
		}
	}
}