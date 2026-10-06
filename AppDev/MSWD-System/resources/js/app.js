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
        if (window.innerWidth > 900) closeMenu();
    });
});
