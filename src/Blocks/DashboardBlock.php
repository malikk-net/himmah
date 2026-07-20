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
        if (!function_exists('register_block_type')) {
            return;
        }

        // تسجيل سكريبت المحرر الخاص بالبلوك
        wp_register_script(
            'himmah-dashboard-block-js',
            HIMMAH_PLUGIN_URL . 'assets/js/dashboard-block.js',
            ['wp-blocks', 'wp-element'],
            HIMMAH_VERSION
        );

        // تسجيل المكوّن وربط السكريبت والدالة البرمجية
        register_block_type('himmah/dashboard', [
            'editor_script'   => 'himmah-dashboard-block-js',
            'render_callback' => [self::class, 'render_frontend'],
        ]);
    }

    public static function render_frontend($attributes, $content) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return '<div style="padding:15px; background:#fff3cd; color:#856404; border-radius:8px; font-family:sans-serif; direction:rtl;">⚠️ يرجى تسجيل الدخول لعرض لوحة هِمّة.</div>';
        }

        ob_start();
        ?>
        <div class="himmah-dashboard-container" style="padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); font-family: sans-serif; direction: rtl; margin: 15px 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 16px;">
                <h3 style="margin: 0; color: #1e293b; font-size: 1.25rem;">📊 لوحة إنجاز هِمّة</h3>
                <span style="background: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold;">0 نقطة</span>
            </div>
            <p style="color: #64748b; margin: 0 0 12px 0;">مرحباً بك! هذه لوحتك التفاعلية لمتابعة الأنشطة والسلاسل اليومية.</p>
        </div>
        <?php
        return ob_get_clean();
    }
}