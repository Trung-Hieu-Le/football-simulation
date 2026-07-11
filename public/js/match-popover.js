document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.match-score-trigger').forEach((el) => {
        const template = el.querySelector('.match-preview-source');
        const content = template?.innerHTML.trim() ?? el.getAttribute('data-bs-content') ?? '';
        if (!content) {
            return;
        }

        new bootstrap.Popover(el, {
            html: true,
            content,
            sanitize: false,
            trigger: 'hover focus',
            placement: 'top',
        });
    });
});
