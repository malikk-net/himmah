<?php
namespace MalikK\Himmah\Blocks;

/**
 * فئة تسجيل وعرض بلوك قائمة التحديات اليومية
 */
class ChallengeListBlock {

    public static function init() {
        add_action('init', [self::class, 'register_block']);
    }

    public static function register_block() {
        if (!function_exists('register_block_type')) {
            return;
        }

        // تسجيل سكريبت البلوك للمحرر والواجهة
        wp_register_script(
            'himmah-challenge-list-js',
            HIMMAH_PLUGIN_URL . 'assets/js/challenge-list-block.js',
            ['wp-blocks', 'wp-element', 'wp-components'],
            HIMMAH_VERSION,
            true
        );

        wp_localize_script('himmah-challenge-list-js', 'wpApiSettings', [
            'nonce' => wp_create_nonce('wp_rest')
        ]);

        register_block_type('himmah/challenge-list', [
            'editor_script'   => 'himmah-challenge-list-js',
            'render_callback' => [self::class, 'render_frontend'],
        ]);

        // شورت كود احتياطي
        add_shortcode('himmah_challenges', [self::class, 'render_frontend']);
    }

    public static function render_frontend($attributes = [], $content = null) {
        wp_enqueue_script('himmah-challenge-list-js');

        $args = [
            'post_type'      => 'himmah_challenge',
            'posts_per_page' => 10,
            'post_status'    => 'publish',
        ];

        $challenges = get_posts($args);

        ob_start();
        ?>
        <div class="himmah-challenges-container" style="padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); font-family: sans-serif; direction: rtl; margin: 15px 0;">
            <h3 style="margin-top: 0; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; font-size: 1.2rem;">📋 التحديات اليومية</h3>
            
            <?php if (empty($challenges)): ?>
                <p style="color: #64748b; font-size: 0.95rem;">لا توجد تحديات متاحة حالياً. أضف تحديات من لوحة التحكم: <strong>هِمّة ← التحديات</strong>.</p>
            <?php else: ?>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($challenges as $challenge): ?>
                        <li style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                            <div>
                                <strong style="color: #0f172a; font-size: 1rem; display: block;"><?php echo esc_html($challenge->post_title); ?></strong>
                                <span style="color: #64748b; font-size: 0.85rem;"><?php echo esc_html(wp_strip_all_tags($challenge->post_content)); ?></span>
                            </div>
                            <button class="himmah-complete-btn" data-challenge-id="<?php echo esc_attr($challenge->ID); ?>" style="background-color: #4f46e5; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.85rem; transition: background 0.2s;">
                                تم الإنجاز
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}