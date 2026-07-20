(function (blocks, element) {
    var el = element.createElement;

    blocks.registerBlockType('himmah/dashboard', {
        title: 'لوحة هِمّة اليومية',
        icon: 'chart-line',
        category: 'widgets',
        keywords: ['himmah', 'هِمّة', 'dashboard', 'عادات'],
        edit: function () {
            return el(
                'div',
                {
                    style: {
                        padding: '20px',
                        border: '2px dashed #6366f1',
                        borderRadius: '10px',
                        background: '#f8fafc',
                        textAlign: 'center',
                        direction: 'rtl',
                        fontFamily: 'sans-serif'
                    }
                },
                el('h4', { style: { margin: '0 0 8px 0', color: '#4338ca' } }, '📊 لوحة إنجاز هِمّة (معاينة المحرر)'),
                el('p', { style: { margin: 0, color: '#64748b', fontSize: '14px' } }, 'سيتم عرض اللوحة التفاعلية والنقاط للمستخدمين في الواجهة الأمامية للموقع.')
            );
        },
        save: function () {
            return null; // Dynamic block rendered in PHP
        },
    });
})(window.wp.blocks, window.wp.element);