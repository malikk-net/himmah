<?php
namespace MalikK\Himmah\Database;

/**
 * فئة تثبيت وإنشاء جداول قاعدة البيانات الخاصة بإضافة هِمّة
 */
class Installer {

    /**
     * تشغيل عمليات التثبيت عند تفعيل الإضافة
     */
    public static function activate() {
        global $wpdb;

        // ⚠️ مهم جداً: استدعاء ملف upgrade.php لتوفير دالة dbDelta
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        // 1. جدول أنشطة وإنجازات المستخدمين
        $table_activity = $wpdb->prefix . 'himmah_user_activity';
        $sql_activity = "CREATE TABLE {$table_activity} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            challenge_id bigint(20) NOT NULL,
            points int(11) DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY challenge_id (challenge_id)
        ) {$charset_collate};";

        dbDelta($sql_activity);

        // حفظ رقم إصدار قاعدة البيانات
        update_option('himmah_db_version', HIMMAH_VERSION);
    }
}