document.addEventListener('DOMContentLoaded', () => {
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
    }
});
