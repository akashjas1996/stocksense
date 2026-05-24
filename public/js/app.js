// ── Navigation progress bar ──────────────────────────────────────────────────
// Shows an amber bar at the top immediately on any link tap or form submit,
// giving instant feedback before the server responds.
(function () {
    const bar = document.createElement('div');
    bar.style.cssText = [
        'position:fixed', 'top:0', 'left:0', 'z-index:9999',
        'height:3px', 'width:0', 'background:#D97706',
        'transition:none', 'pointer-events:none',
    ].join(';');
    document.documentElement.appendChild(bar);

    function start() {
        // Reset to 0 instantly, then animate toward 92% over ~12s
        bar.style.transition = 'none';
        bar.style.width = '0';
        bar.style.opacity = '1';
        requestAnimationFrame(() => requestAnimationFrame(() => {
            bar.style.transition = 'width 12s cubic-bezier(0.05, 0.8, 0.4, 1)';
            bar.style.width = '92%';
        }));
    }

    document.addEventListener('click', e => {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href
            || href === '#'
            || href.startsWith('javascript:')
            || a.target === '_blank'
            || a.hasAttribute('download')
        ) return;
        start();
    }, { passive: true });

    document.addEventListener('submit', () => start(), { passive: true });
})();

// ── Touch responsiveness ─────────────────────────────────────────────────────
// Removes the 300 ms tap delay on older iOS for interactive elements.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a, button, .loc-card, .mall-card, .catalog-card').forEach(el => {
        el.style.touchAction = 'manipulation';
    });
});
