// -shaine: Initializes alerts, local timestamps, mobile navigation, and identity review.
// -shaine: Initializes page-level UI behavior after the document is ready.
document.addEventListener('DOMContentLoaded', () => {
    // -shaine: Adds accessible dismiss controls and timeout behavior to alerts.
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
        // -shaine: Dismisses the alert when the user activates its close button.
        dismissButton.addEventListener('click', () => dismissAlert(alert));
        alert.append(dismissButton);

        if (alert.classList.contains('alert-success') || alert.getAttribute('role') === 'status') {
            // -shaine: Automatically clears success/status messages after a short delay.
            window.setTimeout(() => dismissAlert(alert), 6500);
        }
    });

    // -shaine: Formats timestamps using the visitor's locale and time zone.
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

    // -shaine: Closes the responsive sidebar and its page overlay together.
    const closeMenu = () => {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('show');
    };

    // -shaine: Opens the mobile sidebar when the menu button is selected.
    menu?.addEventListener('click', () => {
        sidebar?.classList.add('open');
        overlay?.classList.add('show');
    });

    // -shaine: Closes the mobile sidebar when the user selects the backdrop.
    overlay?.addEventListener('click', closeMenu);

    // -shaine: Prevents the mobile menu state from persisting at desktop widths.
    window.addEventListener('resize', () => {
        // Sirad-i ang mobile navigation kon mobalik sa desktop layout. -shaine
        if (window.innerWidth > 900) closeMenu();
    });

    if (document.getElementById('identityCheck')) {
        // -shaine: Loads face comparison only on its page and reports unavailable support.
        import('./identity-match.js').catch(error => {
            console.error('On-device face comparison could not be loaded.', error);
            const matchStatus = document.getElementById('matchStatus');
            if (matchStatus) {
                matchStatus.textContent = 'Automatic comparison is unavailable. Please inspect both images and make the review decision manually.';
                matchStatus.className = 'match-unavailable';
            }
        });

        // -shaine: Animates an alert out once and removes it when the animation ends.
        function dismissAlert(alert) {
            if (!alert.isConnected || alert.classList.contains('is-leaving')) {
                return;
            }

            alert.classList.add('is-leaving');
            // -shaine: Removes the node only after its exit animation completes.
            alert.addEventListener('animationend', () => alert.remove(), { once: true });
        }
    }
});
