(function (blocks, element) {
    var el = element.createElement;

    if (blocks && blocks.registerBlockType) {
        blocks.registerBlockType('himmah/challenge-list', {
            title: 'قائمة التحديات اليومية - هِمّة',
            icon: 'list-view',
            category: 'widgets',
            keywords: ['himmah', 'هِمّة', 'تحديات', 'challenges'],
            edit: function () {
                return el(
                    'div',
                    {
                        style: {
                            padding: '20px',
                            border: '2px dashed #10b981',
                            borderRadius: '10px',
                            background: '#f0fdf4',
                            textAlign: 'center',
                            direction: 'rtl',
                            fontFamily: 'sans-serif'
                        }
                    },
                    el('h4', { style: { margin: '0 0 8px 0', color: '#047857' } }, '📋 قائمة التحديات اليومية (معاينة المحرر)'),
                    el('p', { style: { margin: 0, color: '#065f46', fontSize: '14px' } }, 'ستظهر هنا التحديات النشطة للمستخدم مع إمكانية تعليمها كـ "مكتملة" تفاعلياً.')
                );
            },
            save: function () {
                return null;
            },
        });
    }
})(window.wp.blocks, window.wp.element);

// التفاعل في الواجهة الأمامية عند الضغط على زر الإنجاز
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
        // اقتناص زر الإنجاز في الصفحة
        var btn = e.target.closest('.himmah-complete-btn, [data-challenge-id]');
        if (!btn) return;

        e.preventDefault();

        var challengeId = btn.getAttribute('data-challenge-id') || 1;
        var originalText = btn.innerText;

        btn.disabled = true;
        btn.innerText = 'جاري التسجيل...';

        // الحصول على مفتاح الأمان ورابط الـ API
        var nonce = (typeof himmahData !== 'undefined' && himmahData.nonce) 
            ? himmahData.nonce 
            : ((typeof wpApiSettings !== 'undefined') ? wpApiSettings.nonce : '');

        var apiUrl = (typeof himmahData !== 'undefined' && himmahData.root) 
            ? himmahData.root + 'log-activity' 
            : '/wp-json/himmah/v1/log-activity';

        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce
            },
            body: JSON.stringify({ 
                challenge_id: challengeId,
                points: 10 
            })
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success) {
                // 1. تغيير زر الإنجاز إلى حالة المكتمل
                btn.innerText = '✅ تم الإنجاز';
                btn.style.backgroundColor = '#10b981';
                btn.style.color = '#ffffff';

                // 2. تحديث إجمالي النقاط في الشاشة فوراً
                var newTotal = data.total_points !== undefined ? data.total_points : data.points;
                if (newTotal !== undefined) {
                    var pointsBadges = document.querySelectorAll('.himmah-points-badge, [class*="points"]');
                    pointsBadges.forEach(function (badge) {
                        badge.innerText = newTotal + ' نقطة';
                    });
                }
            } else {
                alert(data.message || 'يرجى تسجيل الدخول لتسجيل الإنجاز.');
                btn.disabled = false;
                btn.innerText = originalText;
            }
        })
        .catch(function (err) {
            console.error(err);
            alert('حدث خطأ أثناء الاتصال بالخادم.');
            btn.disabled = false;
            btn.innerText = originalText;
        });
    });
});