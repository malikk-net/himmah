document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.himmah-record-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const currentButton = this;
            const challengeId = currentButton.dataset.challengeId;

            if (!challengeId) return;

            currentButton.textContent = 'جاري التسجيل...';
            currentButton.disabled = true;

            const restUrl = typeof himmahData !== 'undefined' ? himmahData.restUrl : '/wp-json/himmah/v1/';
            const nonce = typeof himmahData !== 'undefined' ? himmahData.nonce : '';

            fetch(restUrl + 'log-activity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify({ challenge_id: challengeId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentButton.textContent = 'تم الإنجاز ✓';
                } else {
                    // سيظهر هنا نص خطأ قاعدة البيانات الحقيقي بوضوح تام
                    alert('تنبيه: ' + (data.message || 'فشل التسجيل'));
                    currentButton.textContent = 'تسجيل';
                    currentButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('حدث خطأ في الاتصال بالخادم.');
                currentButton.textContent = 'تسجيل';
                currentButton.disabled = false;
            });
        });
    });
});