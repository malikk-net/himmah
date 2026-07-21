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
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        currentButton.textContent = 'تم الإنجاز ✓';
                    } else {
                        alert('تنبيه: ' + (data.message || 'فشل التسجيل'));
                        currentButton.textContent = 'تسجيل';
                        currentButton.disabled = false;
                    }
                } catch (err) {
                    console.error("استجابة الخادم ليست JSON صالحاً:", text);
                    alert("حدث خطأ برمجياً في الخادم.");
                    currentButton.textContent = 'تسجيل';
                    currentButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                currentButton.textContent = 'تسجيل';
                currentButton.disabled = false;
            });
        });
    });
});