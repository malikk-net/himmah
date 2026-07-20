<?php
namespace MalikK\Himmah\Blocks;

/**
 * تسجيل وعرض بلوك "لوحة اليوم" لإضافة هِمّة
 */
class DashboardBlock {

    /**
     * تسجيل البلوك في ووردبريس
     */
    public static function register() {
        register_block_type('himmah/today-dashboard', [
            'render_callback' => [self::class, 'render'],
            'attributes'      => [
                'show_summary' => [
                    'type'    => 'boolean',
                    'default' => true,
                ],
            ],
        ]);
    }

    /**
     * عرض محتوى البلوك في الواجهة الأمامية (Render Callback)
     */
    public static function render($attributes) {
        if (!is_user_logged_in()) {
            return '<div class="himmah-dashboard-guest" style="padding: 20px; background: #F7F2E7; border-radius: 12px; text-align: center; color: #173C33;">
                <h3>مرحباً بك في هِمّة</h3>
                <p>يرجى تسجيل الدخول لمتابعة أهدافك اليومية وتقدمك.</p>
            </div>';
        }

        $user_id = get_current_user_id();
        $user    = get_userdata($user_id);
        
        // استدعاء بيانات اللوحة مباشرة
        $controller = new \MalikK\Himmah\Rest\DashboardController();
        $request    = new \WP_REST_Request('GET', '/himmah/v1/me/dashboard');
        $response   = $controller->get_dashboard_data($request);
        $data       = $response->get_data()['data'] ?? [];

        $summary    = $data['summary'] ?? [];
        $percentage = $summary['completion_percentage'] ?? 0;
        $points     = $summary['total_activity_points'] ?? 0;
        $streak     = $summary['current_streak'] ?? 0;

        ob_start();
        ?>
        <div class="himmah-dashboard-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: inherit; direction: rtl;">
            <!-- Header Section -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h2 style="margin: 0; color: #194B3D; font-size: 1.4rem;">أهلاً بك، <?php echo esc_html($user->display_name); ?> 👋</h2>
                    <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;"><?php echo esc_html(date_i18n('l، j F Y')); ?></p>
                </div>
                <div style="background: #F7F2E7; padding: 8px 16px; border-radius: 20px; color: #194B3D; font-weight: bold; font-size: 0.9rem;">
                    🔥 السلسلة: <?php echo intval($streak); ?> يوم
                </div>
            </div>

            <!-- Progress Bar -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 6px; color: #173C33;">
                    <span>إنجاز اليوم</span>
                    <span><strong><?php echo intval($percentage); ?>%</strong></span>
                </div>
                <div style="width: 100%; background: #e2e8f0; height: 10px; border-radius: 5px; overflow: hidden;">
                    <div style="width: <?php echo intval($percentage); ?>%; background: #194B3D; height: 100%; transition: width 0.3s ease;"></div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; text-align: center;">
                <div style="background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid #f1f5f9;">
                    <span style="font-size: 0.8rem; color: #64748b;">الأهداف المكتملة</span>
                    <div style="font-size: 1.2rem; font-weight: bold; color: #194B3D; margin-top: 4px;">
                        <?php echo intval($summary['completed_goals_today'] ?? 0); ?> / <?php echo intval($summary['daily_goal_target'] ?? 3); ?>
                    </div>
                </div>
                <div style="background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid #f1f5f9;">
                    <span style="font-size: 0.8rem; color: #64748b;">نقاط النشاط</span>
                    <div style="font-size: 1.2rem; font-weight: bold; color: #C89952; margin-top: 4px;">
                        ⭐ <?php echo intval($points); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}