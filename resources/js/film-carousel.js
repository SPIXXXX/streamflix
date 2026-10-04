const carousels = document.querySelectorAll('[data-film-carousel]');

carousels.forEach((carousel) => {
    const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
    const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
    const counter = carousel.querySelector('[data-carousel-counter]');

    if (slides.length < 2) {
        return;
    }

    let activeIndex = slides.findIndex((slide) => !slide.hidden);
    let autoplayTimer;
    let touchStartX = null;

    const showSlide = (index) => {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, slideIndex) => {
            const isActive = slideIndex === activeIndex;

            slide.hidden = !isActive;
            slide.setAttribute('aria-hidden', String(!isActive));
        });

        dots.forEach((dot, dotIndex) => {
            const isActive = dotIndex === activeIndex;

            dot.setAttribute('aria-current', String(isActive));
            dot.tabIndex = isActive ? 0 : -1;
        });

        if (counter) {
            counter.textContent = String(activeIndex + 1).padStart(2, '0');
        }
    };

    const stopAutoplay = () => {
        window.clearInterval(autoplayTimer);
        autoplayTimer = undefined;
    };

    const startAutoplay = () => {
        stopAutoplay();

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        autoplayTimer = window.setInterval(() => {
            if (!document.hidden && !carousel.matches(':hover') && !carousel.contains(document.activeElement)) {
                showSlide(activeIndex + 1);
            }
        }, 6500);
    };

    carousel.querySelector('[data-carousel-prev]')?.addEventListener('click', () => {
        showSlide(activeIndex - 1);
        startAutoplay();
    });

    carousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => {
        showSlide(activeIndex + 1);
        startAutoplay();
    });

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            showSlide(Number(dot.dataset.carouselDot));
            startAutoplay();
        });
    });

    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
    carousel.addEventListener('focusin', stopAutoplay);
    carousel.addEventListener('focusout', (event) => {
        if (!carousel.contains(event.relatedTarget)) {
            startAutoplay();
        }
    });
    carousel.addEventListener('pointerdown', (event) => {
        touchStartX = event.pointerType === 'touch' ? event.clientX : null;
    });
    carousel.addEventListener('pointerup', (event) => {
        if (touchStartX === null) {
            return;
        }

        const swipeDistance = event.clientX - touchStartX;
        touchStartX = null;

        if (Math.abs(swipeDistance) > 50) {
            showSlide(activeIndex + (swipeDistance < 0 ? 1 : -1));
            startAutoplay();
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoplay();
        } else {
            startAutoplay();
        }
    });

    startAutoplay();
});
