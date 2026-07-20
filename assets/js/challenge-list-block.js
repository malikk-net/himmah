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
    document.querySelectorAll('.himmah-complete-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var challengeId = this.getAttribute('data-challenge-id');
            var btn = this;
            btn.disabled = true;
            btn.innerText = 'جاري التسجيل...';

            var nonce = (typeof wpApiSettings !== 'undefined') ? wpApiSettings.nonce : '';

            fetch('/wp-json/himmah/v1/activities', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify({ challenge_id: challengeId })
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    btn.innerText = '✅ تم الإنجاز';
                    btn.style.backgroundColor = '#10b981';
                    btn.style.color = '#ffffff';
                } else {
                    alert(data.message || 'يرجى تسجيل الدخول لتسجيل الإنجاز.');
                    btn.disabled = false;
                    btn.innerText = 'إنجاز التحدي';
                }
            })
            .catch(function (err) {
                console.error(err);
                alert('حدث خطأ أثناء الاتصال بالخادم.');
                btn.disabled = false;
                btn.innerText = 'إنجاز التحدي';
            });
        });
    });
});