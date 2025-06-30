document.addEventListener('DOMContentLoaded', function() {
  // Throttle function - membatasi frekuensi eksekusi fungsi
  function throttle(func, delay) {
    let lastCall = 0;
    return function(...args) {
      const now = new Date().getTime();
      if (now - lastCall < delay) {
        return;
      }
      lastCall = now;
      return func(...args);
    }
  }
  
  // IntersectionObserver untuk animasi scroll
  function setupScrollAnimations() {
    // Gunakan Intersection Observer untuk performa yang lebih baik
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // Tambahkan delay berdasarkan data-delay attribute
          const element = entry.target;
          const delay = element.dataset.delay || 0;
          
          // Gunakan requestAnimationFrame untuk animasi yang lebih halus
          requestAnimationFrame(() => {
            setTimeout(() => {
              element.classList.add('active');
            }, delay);
          });
        }
      });
    }, { 
      threshold: 0.15, // Trigger saat 15% elemen terlihat
      rootMargin: '0px 0px -10% 0px' // Offset sedikit
    });
    
    // Observe semua elemen dengan class slide-up
    const elements = document.querySelectorAll('.slide-up');
    elements.forEach(element => {
      observer.observe(element);
    });
    
    return observer; // Return observer untuk bisa di-disconnect nanti
  }

  // Setup auto-scroll untuk scrollable containers dengan performa yang lebih baik
  function setupAutoScroll() {
    const scrollContainer = document.getElementById('scrollContainer');
    if (!scrollContainer) return null;
    
    const scrollText = document.getElementById('scrollText');
    if (!scrollText) return null;
    
    // Deteksi apakah ini perangkat mobile
    const isMobile = window.innerWidth <= 768;
    
    // Variables untuk autoscroll
    let scrollActive = false;
    let scrollSpeed = isMobile ? 0.3 : 0.5; // Lebih lambat di mobile
    let scrollPaused = false;
    let lastScrollTop = 0;
    let scrollTimer = null;
    let isScrolling = false;
    
    // Mulai auto-scroll setelah delay
    const startScrollTimer = setTimeout(() => {
      scrollActive = true;
      autoScroll();
    }, isMobile ? 2500 : 2000); // Delay lebih lama di mobile
    
    // Fungsi autoscroll yang dioptimasi
    function autoScroll() {
      if (!scrollActive || scrollPaused || !document.hasFocus()) return;
      
      // Cek apakah container masih ada di DOM (untuk menghindari error)
      if (!document.body.contains(scrollContainer)) {
        cancelAnimationFrame(scrollTimer);
        return;
      }
      
      // Hanya scroll jika belum sampai di bawah
      const scrollMax = scrollContainer.scrollHeight - scrollContainer.clientHeight;
      
      if (scrollContainer.scrollTop < scrollMax) {
        scrollContainer.scrollTop += scrollSpeed;
        
        // Hanya highlight paragraf yang visible jika tidak sedang scrolling
        if (!isScrolling) {
          // Optimasi dengan throttling
          throttledHighlightParagraphs();
        }
        
        // Lanjutkan animasi dengan requestAnimationFrame untuk performa yang lebih baik
        scrollTimer = requestAnimationFrame(autoScroll);
      } else {
        // Saat sampai di bawah, reset setelah jeda
        setTimeout(() => {
          // Smooth scroll kembali ke atas
          scrollToTop();
        }, isMobile ? 3000 : 5000); // Waktu tunggu lebih pendek di mobile
      }
    }
    
    // Throttle fungsi highlight untuk mengurangi beban CPU
    const throttledHighlightParagraphs = throttle(highlightVisibleParagraphs, 150);
    
    // Smooth scroll to top dengan performa yang dioptimasi
    function scrollToTop() {
      scrollPaused = true;
      isScrolling = true;
      
      const start = scrollContainer.scrollTop;
      const startTime = performance.now();
      const duration = isMobile ? 1500 : 2000; // Lebih cepat di mobile
      
      function scrollStep(timestamp) {
        const elapsed = timestamp - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Ease out cubic - membuat animasi lebih halus
        const easeProgress = 1 - Math.pow(1 - progress, 3);
        
        scrollContainer.scrollTop = start * (1 - easeProgress);
        
        if (progress < 1) {
          requestAnimationFrame(scrollStep);
        } else {
          // Reset dan restart
          isScrolling = false;
          setTimeout(() => {
            scrollPaused = false;
            autoScroll();
          }, isMobile ? 1500 : 2000); // Tunggu lebih singkat di mobile
        }
      }
      
      requestAnimationFrame(scrollStep);
    }
    
    // Fungsi highlight paragraf yang lebih efisien
    function highlightVisibleParagraphs() {
      // Cek apakah masih ada di DOM
      if (!document.body.contains(scrollContainer)) return;
      
      const paragraphs = scrollText.querySelectorAll('p');
      const containerRect = scrollContainer.getBoundingClientRect();
      
      paragraphs.forEach(p => {
        const rect = p.getBoundingClientRect();
        
        // Cek apakah paragraf terlihat
        const isVisible = (
          rect.top < containerRect.bottom - 10 && 
          rect.bottom > containerRect.top + 10
        );
        
        // Optimasi performa dengan classList daripada style manipulation
        if (isVisible) {
          // Opacities berdasarkan visibilitas (0.7-1.0)
          p.classList.add('visible-paragraph');
          p.classList.remove('hidden-paragraph');
        } else {
          p.classList.add('hidden-paragraph');
          p.classList.remove('visible-paragraph');
        }
      });
    }
    
    // Event handlers dengan debouncing dan throttling
    let touchTimeout;
    
    // Pause auto-scrolling saat user berinteraksi
    scrollContainer.addEventListener('mouseenter', () => {
      scrollPaused = true;
      lastScrollTop = scrollContainer.scrollTop;
    });
    
    scrollContainer.addEventListener('mouseleave', () => {
      // Hanya restart jika tidak discroll terlalu jauh
      if (Math.abs(scrollContainer.scrollTop - lastScrollTop) < 30) {
        scrollPaused = false;
        autoScroll();
      }
    });
    
    // Throttled scroll handler
    scrollContainer.addEventListener('scroll', throttle(function() {
      // Simpan posisi terakhir
      lastScrollTop = scrollContainer.scrollTop;
      
      // Update highlight saat scroll manual
      throttledHighlightParagraphs();
    }, 100)); // Throttle ke 100ms
    
    // Touch events untuk mobile
    scrollContainer.addEventListener('touchstart', () => {
      scrollPaused = true;
      lastScrollTop = scrollContainer.scrollTop;
      clearTimeout(touchTimeout);
    });
    
    scrollContainer.addEventListener('touchend', () => {
      // Tunggu sebentar untuk memastikan user selesai touch
      clearTimeout(touchTimeout);
      touchTimeout = setTimeout(() => {
        // Hanya restart jika tidak discroll terlalu jauh
        if (Math.abs(scrollContainer.scrollTop - lastScrollTop) < 30) {
          scrollPaused = false;
          autoScroll();
        }
      }, 800); // Lebih cepat di mobile
    });
    
    // Tambahkan utility class untuk CSS
    const style = document.createElement('style');
    style.textContent = `
      .visible-paragraph {
        opacity: 1;
        transform: translateX(0);
        transition: opacity 0.2s ease-out, transform 0.2s ease-out;
      }
      .hidden-paragraph {
        opacity: 0.5;
        transform: translateX(-3px);
        transition: opacity 0.2s ease-out, transform 0.2s ease-out;
      }
    `;
    document.head.appendChild(style);
    
    // Cleanup function
    return function cleanup() {
      clearTimeout(startScrollTimer);
      cancelAnimationFrame(scrollTimer);
      clearTimeout(touchTimeout);
      scrollActive = false;
      document.head.removeChild(style);
    };
  }

  // Animasi About section dengan performa yang lebih baik
  function animateAboutSection() {
    const aboutSection = document.getElementById('about');
    if (!aboutSection) return null;
    
    // Deteksi apakah ini perangkat mobile
    const isMobile = window.innerWidth <= 768;

    // Observer untuk animasi section
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // Tambahkan animation class ke section
          aboutSection.classList.add('about-active');

          // Animate title dengan efek sederhana
          const aboutTitle = aboutSection.querySelector('h2');
          if (aboutTitle) {
            aboutTitle.classList.add('title-animation');
          }

          // Animate setiap panel dengan staggered delay yang lebih pendek
          const panels = aboutSection.querySelectorAll('.panel-main, .panel-small');
          panels.forEach((panel, index) => {
            setTimeout(() => {
              panel.classList.add('panel-animation');
            }, isMobile ? 120 * index : 180 * index); // Delay lebih pendek di mobile
          });

          // Animate image dengan efek khusus
          const aboutImage = aboutSection.querySelector('.img-rounded');
          if (aboutImage) {
            setTimeout(() => {
              aboutImage.classList.add('image-animation');
            }, isMobile ? 300 : 400);
          }

          // Animate text content dengan sequence fade-in
          const textElements = aboutSection.querySelectorAll('.text-custom');
          textElements.forEach((text, index) => {
            setTimeout(() => {
              text.classList.add('text-animation');
            }, (isMobile ? 500 : 600) + (isMobile ? 80 : 100) * index);
          });

          // Disconnect observer setelah animasi selesai
          observer.disconnect();
        }
      });
    }, { 
      threshold: isMobile ? 0.1 : 0.2, // Threshold lebih rendah di mobile
      rootMargin: '0px 0px -10% 0px'
    });

    observer.observe(aboutSection);
    return observer;
  }

  // Efek hover dengan performa lebih baik (gunakan CSS dan class daripada inline style)
  function setupHoverEffects() {
    // Hanya terapkan efek hover pada desktop
    if (window.innerWidth <= 768) return;
    
    const panels = document.querySelectorAll('.panel-small, .panel-main');
    
    // Tambahkan style untuk hover effect
    const style = document.createElement('style');
    style.textContent = `
      .panel-small.hover {
        transform: translateY(-3px);
      }
      .panel-main.hover {
        transform: translateY(-3px);
      }
    `;
    document.head.appendChild(style);
    
    panels.forEach(panel => {
      panel.addEventListener('mouseenter', function() {
        this.classList.add('hover');
      });
      
      panel.addEventListener('mouseleave', function() {
        this.classList.remove('hover');
      });
    });
    
    // Return cleanup function
    return function cleanup() {
      document.head.removeChild(style);
    };
  }

  // Jalankan semua setup dengan requestIdleCallback jika tersedia
  function initializeAnimations() {
    // Simpan referensi ke cleanup functions
    const cleanupFunctions = [];
    
    // Setup scroll animations
    const scrollObserver = setupScrollAnimations();
    if (scrollObserver) {
      cleanupFunctions.push(() => scrollObserver.disconnect());
    }
    
    // Setup efek hover
    const hoverCleanup = setupHoverEffects();
    if (hoverCleanup) {
      cleanupFunctions.push(hoverCleanup);
    }
    
    // Setup about section animations
    const aboutObserver = animateAboutSection();
    if (aboutObserver) {
      cleanupFunctions.push(() => aboutObserver.disconnect());
    }
    
    // Setup auto-scroll
    const autoScrollCleanup = setupAutoScroll();
    if (autoScrollCleanup) {
      cleanupFunctions.push(autoScrollCleanup);
    }
    
    // Handler untuk resize yang dioptimasi
    const resizeHandler = throttle(function() {
      // Cleanup previous animations
      cleanupFunctions.forEach(cleanup => {
        if (typeof cleanup === 'function') {
          cleanup();
        }
      });
      
      // Re-initialize animations
      cleanupFunctions.length = 0;
      
      const newScrollObserver = setupScrollAnimations();
      if (newScrollObserver) {
        cleanupFunctions.push(() => newScrollObserver.disconnect());
      }
      
      const newHoverCleanup = setupHoverEffects();
      if (newHoverCleanup) {
        cleanupFunctions.push(newHoverCleanup);
      }
      
      const newAutoScrollCleanup = setupAutoScroll();
      if (newAutoScrollCleanup) {
        cleanupFunctions.push(newAutoScrollCleanup);
      }
    }, 200);
    
    // Listen for resize events
    window.addEventListener('resize', resizeHandler);
    
    // Also cleanup on page unload
    window.addEventListener('unload', () => {
      cleanupFunctions.forEach(cleanup => {
        if (typeof cleanup === 'function') {
          cleanup();
        }
      });
    });
  }
  
  // Use requestIdleCallback for non-critical initialization
  if ('requestIdleCallback' in window) {
    requestIdleCallback(() => initializeAnimations(), { timeout: 1000 });
  } else {
    // Fallback untuk browser yang tidak support requestIdleCallback
    setTimeout(initializeAnimations, 100);
  }
});