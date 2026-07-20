<?php
namespace MalikK\Himmah\Database;

/**
 * فئة تهيئة وتثبيت قاعدة البيانات لإضافة هِمّة
 */
class Installer {

    /**
     * دالة التفعيل المباشرة عند تشغيل الإضافة
     */
    public static function activate() {
        self::run();
    }

    /**
     * تشغيل أوامر إنشاء وتحديث الجداول.
     */
    public static function run() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        self::create_activities_table($charset_collate);
        self::create_points_ledger_table($charset_collate);
        self::create_user_preferences_table($charset_collate);
        self::create_privacy_settings_table($charset_collate);
        self::create_user_stats_table($charset_collate);
        self::create_streaks_table($charset_collate);
        
        update_option('himmah_db_version', '0.1.0');
    }

    /**
     * إنشاء جدول الأنشطة (محرك النشاط)
     */
    private static function create_activities_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'himmah_activities';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            uuid varchar(36) NOT NULL,
            idempotency_key varchar(255) DEFAULT NULL,
            user_id bigint(20) unsigned NOT NULL,
            activity_type varchar(50) NOT NULL,
            object_type varchar(50) DEFAULT NULL,
            object_id bigint(20) unsigned DEFAULT NULL,
            activity_key varchar(100) NOT NULL,
            period_type varchar(50) DEFAULT NULL,
            period_key varchar(50) NOT NULL,
            local_date date NOT NULL,
            status varchar(20) NOT NULL,
            privacy_level varchar(20) NOT NULL,
            source varchar(50) DEFAULT NULL,
            points_eligible tinyint(1) DEFAULT 0,
            streak_eligible tinyint(1) DEFAULT 0,
            group_eligible tinyint(1) DEFAULT 0,
            completed_at_utc datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY unique_activity (user_id, activity_key, period_key),
            KEY user_local_date (user_id, local_date),
            KEY user_status_date (user_id, status, local_date)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * إنشاء جدول سجل النقاط (Append-only)
     */
    private static function create_points_ledger_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'himmah_points_ledger';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            activity_id bigint(20) unsigned DEFAULT NULL,
            points_type varchar(50) NOT NULL,
            amount int(11) NOT NULL,
            balance_after int(11) NOT NULL,
            reason_type varchar(50) DEFAULT NULL,
            reason_id bigint(20) unsigned DEFAULT NULL,
            is_adjustment tinyint(1) DEFAULT 0,
            created_by bigint(20) unsigned DEFAULT NULL,
            note text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_points_time (user_id, points_type, created_at),
            KEY points_activity (activity_id)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * إنشاء جدول تفضيلات المستخدم
     */
    private static function create_user_preferences_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'himmah_user_preferences';

        $sql = "CREATE TABLE $table_name (
            user_id bigint(20) unsigned NOT NULL,
            starting_level varchar(50) DEFAULT 'light',
            daily_goal_count int(11) DEFAULT 3,
            preferred_reminder_time time DEFAULT NULL,
            onboarding_completed tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (user_id)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * إنشاء جدول إعدادات الخصوصية
     */
    private static function create_privacy_settings_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'himmah_privacy_settings';

        $sql = "CREATE TABLE $table_name (
            user_id bigint(20) unsigned NOT NULL,
            default_privacy_level varchar(20) DEFAULT 'private',
            leaderboard_opt_in tinyint(1) DEFAULT 0,
            show_streak_on_profile tinyint(1) DEFAULT 0,
            show_badges_on_profile tinyint(1) DEFAULT 1,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (user_id)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * إنشاء جدول إحصائيات المستخدم
     */
    private static function create_user_stats_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'himmah_user_stats';

        $sql = "CREATE TABLE $table_name (
            user_id bigint(20) unsigned NOT NULL,
            total_activity_points int(11) DEFAULT 0,
            completed_challenges_count int(11) DEFAULT 0,
            completed_journeys_count int(11) DEFAULT 0,
            last_activity_date date DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (user_id)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * إنشاء جدول السلاسل وأيام الرحمة
     */
    private static function create_streaks_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'himmah_streaks';

        $sql = "CREATE TABLE $table_name (
            user_id bigint(20) unsigned NOT NULL,
            current_streak int(11) DEFAULT 0,
            longest_streak int(11) DEFAULT 0,
            mercy_days_balance int(11) DEFAULT 0,
            last_active_date date DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (user_id)
        ) $charset_collate;";

        dbDelta($sql);
    }
}