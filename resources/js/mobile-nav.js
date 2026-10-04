const clientNavbar = document.getElementById('client-navbar');
const categoryControl = document.querySelector('[data-film-category-control]');

if (clientNavbar && categoryControl) {
    const mobileViewport = window.matchMedia('(max-width: 767px)');

    const syncCategoryControl = () => {
        const menuIsOpen = !clientNavbar.classList.contains('hidden');
        categoryControl.classList.toggle('hidden', mobileViewport.matches && menuIsOpen);
    };

    new MutationObserver(syncCategoryControl).observe(clientNavbar, {
        attributes: true,
        attributeFilter: ['class'],
    });

    mobileViewport.addEventListener('change', syncCategoryControl);
    syncCategoryControl();
}
