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
document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('himmah-complete-btn')) {
        const btn = e.target;
        const challengeId = btn.getAttribute('data-challenge-id');
        
        btn.disabled = true;
        btn.textContent = 'جاري التسجيل...';

        fetch(himmahData.root + 'log-activity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': himmahData.nonce
            },
            body: JSON.stringify({
                challenge_id: challengeId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server returned status ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                btn.textContent = 'تم الإنجاز ✅';
                btn.style.background = '#6b7280';
                
                // تحديث شارة النقاط في الصفحة فورياً
                const pointsBadges = document.querySelectorAll('.himmah-points-badge');
                pointsBadges.forEach(badge => {
                    if (data.total_points !== undefined) {
                        badge.textContent = data.total_points + ' نقطة';
                    }
                });
            } else {
                alert('حدث خطأ أثناء إنجاز التحدي.');
                btn.disabled = false;
                btn.textContent = 'إنجاز التحدي';
            }
        })
        .catch(error => {
            console.error('Himmah Error:', error);
            alert('حدث خطأ في الاتصال بالخادم. يرجى المحاولة مرة أخرى.');
            btn.disabled = false;
            btn.textContent = 'إنجاز التحدي';
        });
    }
});