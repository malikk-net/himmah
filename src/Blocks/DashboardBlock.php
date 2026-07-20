<?php
namespace MalikK\Himmah\Blocks;

/**
 * فئة تسجيل وعرض بلوك لوحة التحكم اليومية
 */
class DashboardBlock {

    public static function init() {
        add_action('init', [self::class, 'register_block']);
    }

    public static function register_block() {
        register_block_type('himmah/dashboard', [
            'title'           => 'لوحة هِمّة اليومية - Himmah',
            'description'     => 'عرض لوحة الإنجاز والأنشطة اليومية للمستخدم.',
            'icon'            => 'chart-line',
            'category'        => 'widgets',
            'keywords'        => ['himmah', 'هِمّة', 'dashboard', 'عادات'],
            'render_callback' => [self::class, 'render_frontend'],
        ]);
    }

    public static function render_frontend($attributes, $content) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return '<div class="himmah-block-alert">يرجى تسجيل الدخول لعرض لوحة هِمّة.</div>';
        }

        ob_start();
        ?>
        <div class="himmah-dashboard-container" style="padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff;">
            <h3>📊 لوحة إنجاز هِمّة</h3>
            <p>مرحباً بك! هذه اللوحة التفاعلية لاستعراض عاداتك اليومية وسلسلة إنجازاتك.</p>
        </div>
        <?php
        return ob_get_clean();
    }
}