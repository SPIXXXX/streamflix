const categoryControl = document.querySelector('[data-film-category-control]');
const navbars = ['client-navbar', 'admin-navbar']
    .map((id) => document.getElementById(id))
    .filter(Boolean);

if (categoryControl && navbars.length > 0) {
    const mobileViewport = window.matchMedia('(max-width: 767px)');

    const syncCategoryControl = () => {
        const menuIsOpen = navbars.some((navbar) => !navbar.classList.contains('hidden'));
        categoryControl.classList.toggle('hidden', mobileViewport.matches && menuIsOpen);
    };

    navbars.forEach((navbar) => {
        new MutationObserver(syncCategoryControl).observe(navbar, {
            attributes: true,
            attributeFilter: ['class'],
        });
    });

    mobileViewport.addEventListener('change', syncCategoryControl);
    syncCategoryControl();
}
