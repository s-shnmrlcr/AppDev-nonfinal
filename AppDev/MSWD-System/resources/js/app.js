document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.app-shell .alert[role="alert"], .app-shell .alert[role="status"]').forEach(alert => {
        const content = document.createElement('div');
        content.className = 'alert-content';
        while (alert.firstChild) {
            content.append(alert.firstChild);
        }
        alert.append(content);

        const dismissButton = document.createElement('button');
        dismissButton.type = 'button';
        dismissButton.className = 'alert-dismiss';
        dismissButton.setAttribute('aria-label', 'Dismiss notification');
        dismissButton.textContent = '×';
        dismissButton.addEventListener('click', () => dismissAlert(alert));
        alert.append(dismissButton);

        if (alert.classList.contains('alert-success') || alert.getAttribute('role') === 'status') {
            window.setTimeout(() => dismissAlert(alert), 6500);
        }
    });

    document.querySelectorAll('time[data-local-datetime]').forEach(timeElement => {
        const date = new Date(timeElement.dateTime);
        if (Number.isNaN(date.getTime())) {
            return;
        }

        timeElement.textContent = new Intl.DateTimeFormat(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
        }).format(date);
    });

    const sidebar = document.getElementById('sidebar');
    const menu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileOverlay');

    const closeMenu = () => {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('show');
    };

    menu?.addEventListener('click', () => {
        sidebar?.classList.add('open');
        overlay?.classList.add('show');
    });

    overlay?.addEventListener('click', closeMenu);

    window.addEventListener('resize', () => {
        // Sirad-i ang mobile navigation kon mobalik sa desktop layout. -shaine
        if (window.innerWidth > 900) closeMenu();
    });

    if (document.getElementById('identityCheck')) {
        import('./identity-match.js').catch(error => {
            console.error('On-device face comparison could not be loaded.', error);
            const matchStatus = document.getElementById('matchStatus');
            if (matchStatus) {
                matchStatus.textContent = 'Automatic comparison is unavailable. Please inspect both images and make the review decision manually.';
                matchStatus.className = 'match-unavailable';
            }
        });

        function dismissAlert(alert) {
            if (!alert.isConnected || alert.classList.contains('is-leaving')) {
                return;
            }

            alert.classList.add('is-leaving');
            alert.addEventListener('animationend', () => alert.remove(), { once: true });
        }
    }
});
