<?php
namespace MalikK\Himmah\Services;

/**
 * خدمة إدارة وتسجيل الأنشطة والنقاط لإضافة هِمّة
 */
class ActivityService {

    /**
     * تسجيل إنجاز جديد للمستخدم بطريقة آمنة تمنع التكرار
     *
     * @param int   $user_id     معرف المستخدم
     * @param array $data        بيانات النشاط (activity_type, activity_key, period_key, local_date, points, ...)
     * @return array|bool        بيانات النشاط المسجل أو false في حال الفشل
     */
    public static function record_activity($user_id, $data) {
        global $wpdb;

        // تجهيز المدخلات مع القيم الافتراضية
        $activity_type   = sanitize_text_field($data['activity_type'] ?? 'challenge');
        $activity_key    = sanitize_text_field($data['activity_key'] ?? '');
        $period_type     = sanitize_text_field($data['period_type'] ?? 'daily');
        $period_key      = sanitize_text_field($data['period_key'] ?? date('Y-m-d'));
        $local_date      = sanitize_text_field($data['local_date'] ?? date('Y-m-d'));
        $privacy_level   = sanitize_text_field($data['privacy_level'] ?? 'private');
        $points_amount   = isset($data['points']) ? intval($data['points']) : 0;
        $idempotency_key = sanitize_text_field($data['idempotency_key'] ?? wp_generate_uuid4());
        $uuid            = wp_generate_uuid4();

        if (empty($activity_key)) {
            return false;
        }

        $activities_table = $wpdb->prefix . 'himmah_activities';
        $ledger_table     = $wpdb->prefix . 'himmah_points_ledger';
        $stats_table      = $wpdb->prefix . 'himmah_user_stats';

        // بدأ معاملة قاعدة البيانات لضمان الجاهزية والاستمرارية
        $wpdb->query('START TRANSACTION');

        try {
            // 1. إدراج سجل النشاط
            $inserted = $wpdb->insert(
                $activities_table,
                [
                    'uuid'            => $uuid,
                    'idempotency_key' => $idempotency_key,
                    'user_id'         => $user_id,
                    'activity_type'   => $activity_type,
                    'object_type'     => sanitize_text_field($data['object_type'] ?? null),
                    'object_id'       => isset($data['object_id']) ? intval($data['object_id']) : null,
                    'activity_key'    => $activity_key,
                    'period_type'     => $period_type,
                    'period_key'      => $period_key,
                    'local_date'      => $local_date,
                    'status'          => 'completed',
                    'privacy_level'   => $privacy_level,
                    'source'          => sanitize_text_field($data['source'] ?? 'web'),
                    'points_eligible' => $points_amount > 0 ? 1 : 0,
                    'streak_eligible' => 1,
                    'completed_at_utc'=> current_time('mysql', 1),
                ],
                ['%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s']
            );

            // في حالة فشل الإدراج (مثلاً بسبب القيد الفريد لتكرار النشاط)
            if (!$inserted) {
                $wpdb->query('ROLLBACK');
                return false;
            }

            $activity_id = $wpdb->insert_id;
            $new_balance = 0;

            // 2. تحديث سجل النقاط وإدراج الحركة إذا كانت متوفرة
            if ($points_amount > 0) {
                // جلب رصيد النقاط الحالي
                $current_stats = $wpdb->get_row(
                    $wpdb->prepare("SELECT total_activity_points FROM $stats_table WHERE user_id = %d", $user_id)
                );
                
                $current_balance = $current_stats ? intval($current_stats->total_activity_points) : 0;
                $new_balance     = $current_balance + $points_amount;

                // إضافة حركة النقاط إلى السجل غير القابل للتعديل المباشر (Append-only)
                $wpdb->insert(
                    $ledger_table,
                    [
                        'user_id'       => $user_id,
                        'activity_id'   => $activity_id,
                        'points_type'   => 'activity',
                        'amount'        => $points_amount,
                        'balance_after' => $new_balance,
                        'reason_type'   => 'activity_completion',
                        'reason_id'     => $activity_id,
                        'is_adjustment' => 0,
                        'created_at'    => current_time('mysql', 1),
                    ],
                    ['%d', '%d', '%s', '%d', '%d', '%s', '%d', '%d', '%s']
                );

                // 3. تحديث الإحصائيات التراكمية للمستخدم
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO $stats_table (user_id, total_activity_points, completed_challenges_count, last_activity_date)
                         VALUES (%d, %d, 1, %s)
                         ON DUPLICATE KEY UPDATE
                            total_activity_points = total_activity_points + %d,
                            completed_challenges_count = completed_challenges_count + 1,
                            last_activity_date = %s",
                        $user_id, $points_amount, $local_date, $points_amount, $local_date
                    )
                );
            }

            // تأكيد العملية وتطبيق التغييرات
            $wpdb->query('COMMIT');

            return [
                'activity_id'  => $activity_id,
                'uuid'         => $uuid,
                'user_id'      => $user_id,
                'activity_key' => $activity_key,
                'status'       => 'completed',
                'points_earned'=> $points_amount,
                'new_balance'  => $new_balance,
            ];

        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }
}