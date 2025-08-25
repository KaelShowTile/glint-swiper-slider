function glint_swiper_init() {
    document.querySelectorAll('.swiper').forEach(swiperEl => {
        // Extract slider ID from class name
        const classes = swiperEl.className.split(' ');
        const sliderClass = classes.find(cls => cls.startsWith('glint-swiper-'));
        if (!sliderClass) return;

        const gapSize = swiperEl.dataset.gap ? parseInt(swiperEl.dataset.gap) : 0;
        
        // Initialize Swiper
        new Swiper(swiperEl, {
            slidesPerView: 'auto',
            spaceBetween: gapSize,
            scrollbar: {
                el: swiperEl.querySelector('.swiper-scrollbar'),
                draggable: true,
                hide: false,
                snapOnRelease: true
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            // Add these for better height management
            autoHeight: false,
            height: parseInt(swiperEl.style.height) || null,
        });
    });
}