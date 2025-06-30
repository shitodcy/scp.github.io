/**
 * Enhanced Responsive Image Handling for Home Carousel
 * This script automatically detects image dimensions and optimizes display
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize carousel with better mobile support
    const carousel = document.querySelector('#imageCarousel');
    if (!carousel) return;
    
    // Mark images as loading initially
    document.querySelectorAll('#imageCarousel .carousel-item').forEach(item => {
        item.classList.add('loading');
    });
    
    // Process all carousel images
    const carouselImages = document.querySelectorAll('#imageCarousel .carousel-item img');
    
    carouselImages.forEach(img => {
        // When each image loads
        img.onload = function() {
            const carouselItem = this.closest('.carousel-item');
            
            // Remove loading state
            carouselItem.classList.remove('loading');
            
            // Get natural dimensions
            const width = this.naturalWidth;
            const height = this.naturalHeight;
            const ratio = width / height;
            
            // Reset all classes first
            carouselItem.classList.remove('landscape-mode', 'portrait-mode', 'square-mode', 'wide-landscape');
            
            // Apply appropriate class based on aspect ratio
            if (ratio >= 2.0) {
                // Very wide landscape (panorama)
                carouselItem.classList.add('wide-landscape');
                this.style.objectFit = 'cover';
            } else if (ratio >= 1.3) {
                // Standard landscape
                carouselItem.classList.add('landscape-mode');
                this.style.objectFit = 'cover';
            } else if (ratio <= 0.8) {
                // Portrait
                carouselItem.classList.add('portrait-mode');
                this.style.objectFit = 'contain';
            } else {
                // Near square
                carouselItem.classList.add('square-mode');
                this.style.objectFit = 'cover';
            }
            
            // Check if image is too small for container and adjust
            const containerWidth = carouselItem.offsetWidth;
            if (width < containerWidth * 0.8) {
                this.style.objectFit = 'contain';
                this.style.background = 'rgba(0,0,0,0.1)';
            }
        };
        
        // Force load event if image is cached
        if (img.complete) {
            let event = new Event('load');
            img.dispatchEvent(event);
        }
    });
    
    // Enhanced mobile detection
    function checkMobile() {
        const isMobile = window.matchMedia("(max-width: 768px)").matches;
        if (isMobile) {
            document.body.classList.add('mobile-view');
            
            // Adjust carousel settings for mobile
            if (typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
                const carouselInstance = bootstrap.Carousel.getInstance(carousel);
                if (carouselInstance) {
                    carouselInstance.options.interval = 4000; // Slightly faster on mobile
                }
            }
        } else {
            document.body.classList.remove('mobile-view');
        }
    }
    
    // Run on load and resize
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    // Add smooth transition effects between slides
    if (typeof bootstrap !== 'undefined') {
        carousel.addEventListener('slide.bs.carousel', function(e) {
            const activeSlide = carousel.querySelector('.carousel-item.active');
            const nextSlide = e.relatedTarget;
            
            // Add slide direction classes for better animation
            if (e.direction === 'left') {
                nextSlide.classList.add('sliding-next');
                setTimeout(() => nextSlide.classList.remove('sliding-next'), 700);
            } else {
                nextSlide.classList.add('sliding-prev');
                setTimeout(() => nextSlide.classList.remove('sliding-prev'), 700);
            }
        });
    }
    
    // Add subtle animation to currently visible image
    function animateCurrentImage() {
        const currentImage = carousel.querySelector('.carousel-item.active img');
        if (!currentImage) return;
        
        // Apply subtle zoom or pan effect
        const effects = [
            'scale(1.05)',
            'scale(1.03) translateX(1%)',
            'scale(1.03) translateY(-1%)'
        ];
        
        let currentEffect = 0;
        
        // Only start animation if not on mobile
        if (!document.body.classList.contains('mobile-view')) {
            setInterval(() => {
                currentImage.style.transform = effects[currentEffect];
                currentEffect = (currentEffect + 1) % effects.length;
            }, 4000);
        }
    }
    
    // Start animations after a short delay
    setTimeout(animateCurrentImage, 1000);
    
    // Fix for touch devices that struggle with fixed backgrounds
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