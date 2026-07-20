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
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'Server returned status ' + response.status);
                }
                return data;
            });
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
                alert('حدث خطأ: ' + (data.message || 'يرجى المحاولة لاحقاً.'));
                btn.disabled = false;
                btn.textContent = 'إنجاز التحدي';
            }
        })
        .catch(error => {
            console.error('Himmah Error:', error);
            alert(error.message || 'حدث خطأ في الاتصال بالخادم.');
            btn.disabled = false;
            btn.textContent = 'إنجاز التحدي';
        });
    }
});