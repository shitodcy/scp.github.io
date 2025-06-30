
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('#imageCarousel');
    if (!carousel) return;
    
    document.querySelectorAll('#imageCarousel .carousel-item').forEach(item => {
        item.classList.add('loading');
    });
    
    const carouselImages = document.querySelectorAll('#imageCarousel .carousel-item img');
    
    carouselImages.forEach(img => {
        img.onload = function() {
            const carouselItem = this.closest('.carousel-item');
            
            carouselItem.classList.remove('loading');
            
            const width = this.naturalWidth;
            const height = this.naturalHeight;
            const ratio = width / height;
            
            carouselItem.classList.remove('landscape-mode', 'portrait-mode', 'square-mode', 'wide-landscape');
            
            if (ratio >= 2.0) {
                carouselItem.classList.add('wide-landscape');
                this.style.objectFit = 'cover';
            } else if (ratio >= 1.3) {
                carouselItem.classList.add('landscape-mode');
                this.style.objectFit = 'cover';
            } else if (ratio <= 0.8) {
                carouselItem.classList.add('portrait-mode');
                this.style.objectFit = 'contain';
            } else {
                carouselItem.classList.add('square-mode');
                this.style.objectFit = 'cover';
            }
            
            const containerWidth = carouselItem.offsetWidth;
            if (width < containerWidth * 0.8) {
                this.style.objectFit = 'contain';
                this.style.background = 'rgba(0,0,0,0.1)';
            }
        };
        
        if (img.complete) {
            let event = new Event('load');
            img.dispatchEvent(event);
        }
    });
    
    function checkMobile() {
        const isMobile = window.matchMedia("(max-width: 768px)").matches;
        if (isMobile) {
            document.body.classList.add('mobile-view');
            
            if (typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
                const carouselInstance = bootstrap.Carousel.getInstance(carousel);
                if (carouselInstance) {
                    carouselInstance.options.interval = 4000; 
                }
            }
        } else {
            document.body.classList.remove('mobile-view');
        }
    }

    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    if (typeof bootstrap !== 'undefined') {
        carousel.addEventListener('slide.bs.carousel', function(e) {
            const activeSlide = carousel.querySelector('.carousel-item.active');
            const nextSlide = e.relatedTarget;
            
            if (e.direction === 'left') {
                nextSlide.classList.add('sliding-next');
                setTimeout(() => nextSlide.classList.remove('sliding-next'), 700);
            } else {
                nextSlide.classList.add('sliding-prev');
                setTimeout(() => nextSlide.classList.remove('sliding-prev'), 700);
            }
        });
    }
    
    function animateCurrentImage() {
        const currentImage = carousel.querySelector('.carousel-item.active img');
        if (!currentImage) return;
        
        const effects = [
            'scale(1.05)',
            'scale(1.03) translateX(1%)',
            'scale(1.03) translateY(-1%)'
        ];
        
        let currentEffect = 0;
        
        if (!document.body.classList.contains('mobile-view')) {
            setInterval(() => {
                currentImage.style.transform = effects[currentEffect];
                currentEffect = (currentEffect + 1) % effects.length;
            }, 4000);
        }
    }
    
    setTimeout(animateCurrentImage, 1000);
    
    function fixBackgroundForMobile() {
        if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
            const homeSection = document.getElementById('home');
            if (homeSection) {
                homeSection.style.backgroundAttachment = 'scroll';
            }
        }
    }
    
    fixBackgroundForMobile();
});