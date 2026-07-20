<?php

namespace Himmah\Core;

use Himmah\Admin\MetaBoxes;
use Himmah\Domain\PostTypes;
use Himmah\Repositories\ChallengeRepository;
use Himmah\Rest\ActivityController;
use Himmah\Rest\DashboardController;
use Himmah\Rest\PrivacyController;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Plugin
 * Main core loader class for Himmah plugin.
 */
class Plugin {

	/**
	 * Instance holder.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_domain();
		$this->init_admin();
		$this->init_hooks();
	}

	/**
	 * Initialize domain models and post types.
	 */
	private function init_domain() {
		if ( class_exists( 'Himmah\Domain\PostTypes' ) ) {
			PostTypes::init();
		}
	}

	/**
	 * Initialize admin panels and meta boxes.
	 */
	private function init_admin() {
		if ( is_admin() && class_exists( 'Himmah\Admin\MetaBoxes' ) ) {
			MetaBoxes::init();
		}
	}

	/**
	 * Register WordPress action & filter hooks.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Register REST API controllers.
	 */
	public function register_rest_routes() {
		$activity_controller = new ActivityController();
		$activity_controller->register_routes();

		$dashboard_controller = new DashboardController();
		$dashboard_controller->register_routes();

		if ( class_exists( 'Himmah\Rest\PrivacyController' ) ) {
			$privacy_controller = new PrivacyController();
			$privacy_controller->register_routes();
		}
	}

	/**
	 * Register Gutenberg Blocks.
	 */
	public function register_blocks() {
		register_block_type(
			'himmah/challenge-list',
			array(
				'editor_script'   => 'himmah-challenge-list-script',
				'render_callback' => array( $this, 'render_challenge_list_block' ),
			)
		);

		register_block_type(
			'himmah/dashboard',
			array(
				'editor_script'   => 'himmah-dashboard-script',
				'render_callback' => array( $this, 'render_dashboard_block' ),
			)
		);
	}

	/**
	 * Enqueue scripts and localize data for Frontend.
	 */
	public function enqueue_frontend_assets() {
		$plugin_file = defined( 'HIMMAH_PLUGIN_FILE' ) ? HIMMAH_PLUGIN_FILE : dirname( __DIR__, 2 ) . '/himmah.php';
		$version     = defined( 'HIMMAH_VERSION' ) ? HIMMAH_VERSION : '0.3.0';

		wp_enqueue_script(
			'himmah-challenge-list-script',
			plugins_url( 'assets/js/challenge-list-block.js', $plugin_file ),
			array( 'wp-blocks', 'wp-element' ),
			$version,
			true
		);

		wp_localize_script(
			'himmah-challenge-list-script',
			'himmahData',
			array(
				'root'  => esc_url_raw( rest_url( 'himmah/v1/' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Enqueue scripts for Block Editor (Gutenberg).
	 */
	public function enqueue_editor_assets() {
		$plugin_file = defined( 'HIMMAH_PLUGIN_FILE' ) ? HIMMAH_PLUGIN_FILE : dirname( __DIR__, 2 ) . '/himmah.php';
		$version     = defined( 'HIMMAH_VERSION' ) ? HIMMAH_VERSION : '0.3.0';

		wp_enqueue_script(
			'himmah-dashboard-script',
			plugins_url( 'assets/js/dashboard-block.js', $plugin_file ),
			array( 'wp-blocks', 'wp-element' ),
			$version,
			true
		);
	}

	/**
	 * Render Challenge List Block Callback.
	 */
	public function render_challenge_list_block( $attributes ) {
		if ( ! is_user_logged_in() ) {
			return '<div class="himmah-box" style="padding:15px; background:#fff3cd; border-radius:8px; text-align:center;"><p style="margin:0; color:#856404;">يرجى تسجيل الدخول للوصول إلى قائمة التحديات اليومية.</p></div>';
		}

		$user_id = get_current_user_id();
		$points  = (int) get_user_meta( $user_id, 'himmah_total_points', true );

		$completed_challenges = get_user_meta( $user_id, 'himmah_completed_challenges', true );
		if ( ! is_array( $completed_challenges ) ) {
			$completed_challenges = array();
		}

		// جلب التحديات الفعلية عبر الـ Repository
		$challenges = array();
		if ( class_exists( 'Himmah\Repositories\ChallengeRepository' ) ) {
			$repo       = new ChallengeRepository();
			$challenges = $repo->get_active_challenges( 5 );
		}

		// التحدي الافتراضي في حال عدم وجود تحديات مضافة
		if ( empty( $challenges ) || ! is_array( $challenges ) ) {
			$challenges = array(
				array(
					'id'      => 1,
					'title'   => 'إنجاز تحدي هِمّة اليومي',
					'points'  => 10,
					'content' => '',
				),
			);
		}

		ob_start();
		?>
		<div class="himmah-challenge-container" style="background:#f0fdf4; padding:20px; border-radius:12px; border:1px solid #10b981; direction:rtl; font-family:sans-serif;">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
				<h3 style="margin:0; color:#047857;">🎯 التحديات اليومية</h3>
				<span class="himmah-points-badge" style="background:#10b981; color:#fff; padding:6px 14px; border-radius:20px; font-weight:bold; font-size:14px;">
					<?php echo esc_html( $points ); ?> نقطة
				</span>
			</div>
			
			<div class="himmah-challenges-list" style="display:flex; flex-direction:column; gap:10px;">
				<?php foreach ( $challenges as $challenge ) : 
					$ch_id        = isset( $challenge['id'] ) ? (int) $challenge['id'] : 1;
					$ch_title     = isset( $challenge['title'] ) ? $challenge['title'] : '';
					$ch_points    = isset( $challenge['points'] ) ? (int) $challenge['points'] : 10;
					$ch_content   = isset( $challenge['content'] ) ? $challenge['content'] : '';
					$is_completed = in_array( $ch_id, array_map( 'intval', $completed_challenges ), true );
				?>
					<div class="himmah-challenge-item" style="background:#fff; padding:14px; border-radius:8px; display:flex; justify-content:space-between; align-items:center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
						<div>
							<span style="font-weight:600; color:#1f2937; display:block;"><?php echo esc_html( $ch_title ); ?> (+<?php echo esc_html( $ch_points ); ?> نقاط)</span>
							<?php if ( ! empty( $ch_content ) ) : ?>
								<small style="color:#6b7280;"><?php echo esc_html( $ch_content ); ?></small>
							<?php endif; ?>
						</div>
						<?php if ( $is_completed ) : ?>
							<button disabled style="background:#6b7280; color:#fff; border:none; padding:8px 18px; border-radius:6px; font-weight:bold;">
								تم الإنجاز ✅
							</button>
						<?php else : ?>
							<button class="himmah-complete-btn" data-challenge-id="<?php echo esc_attr( $ch_id ); ?>" style="background:#047857; color:#fff; border:none; padding:8px 18px; border-radius:6px; cursor:pointer; font-weight:bold; transition: background 0.3s;">
								إنجاز التحدي
							</button>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render Dashboard Block Callback.
	 */
	public function render_dashboard_block( $attributes ) {
		if ( ! is_user_logged_in() ) {
			return '<div class="himmah-box" style="padding:15px; background:#fff3cd; border-radius:8px; text-align:center;"><p style="margin:0; color:#856404;">يرجى تسجيل الدخول لمشاهدة لوحة التحكم.</p></div>';
		}

		$user_id      = get_current_user_id();
		$total_points = (int) get_user_meta( $user_id, 'himmah_total_points', true );

		ob_start();
		?>
		<div class="himmah-dashboard-container" style="background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #e2e8f0; direction:rtl; font-family:sans-serif;">
			<h3 style="margin:0 0 10px 0; color:#1e293b;">📊 لوحة هِمّة اليومية</h3>
			<p style="margin:0; color:#475569; font-size:16px;">
				إجمالي نقاطك الحالي: <strong class="himmah-points-badge" style="color:#10b981; font-size:18px;"><?php echo esc_html( $total_points ); ?> نقطة</strong>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}
}