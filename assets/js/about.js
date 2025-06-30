document.addEventListener('DOMContentLoaded', function() {

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
  

  function setupScrollAnimations() {

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {

          const element = entry.target;
          const delay = element.dataset.delay || 0;
          

          requestAnimationFrame(() => {
            setTimeout(() => {
              element.classList.add('active');
            }, delay);
          });
        }
      });
    }, { 
      threshold: 0.15, 
      rootMargin: '0px 0px -10% 0px' 
    });
    

    const elements = document.querySelectorAll('.slide-up');
    elements.forEach(element => {
      observer.observe(element);
    });
    
    return observer; 
  }


  function setupAutoScroll() {
    const scrollContainer = document.getElementById('scrollContainer');
    if (!scrollContainer) return null;
    
    const scrollText = document.getElementById('scrollText');
    if (!scrollText) return null;
    

    const isMobile = window.innerWidth <= 768;
    

    let scrollActive = false;
    let scrollPaused = false;
    let lastScrollTop = 0;
    let scrollTimer = null;
    let isScrolling = false;
    

    const startScrollTimer = setTimeout(() => {
      scrollActive = true;
      autoScroll();
    }, isMobile ? 2500 : 2000); 
    

    function autoScroll() {
      if (!scrollActive || scrollPaused || !document.hasFocus()) return;
      

      if (!document.body.contains(scrollContainer)) {
        cancelAnimationFrame(scrollTimer);
        return;
      }
      

      const scrollMax = scrollContainer.scrollHeight - scrollContainer.clientHeight;
      
      if (scrollContainer.scrollTop < scrollMax) {
        scrollContainer.scrollTop += scrollSpeed;
        

        if (!isScrolling) {

          throttledHighlightParagraphs();
        }
        

        scrollTimer = requestAnimationFrame(autoScroll);
      } else {

        setTimeout(() => {

          scrollToTop();
        }, isMobile ? 3000 : 5000); 
      }
    }
    

    const throttledHighlightParagraphs = throttle(highlightVisibleParagraphs, 150);
    

    function scrollToTop() {
      scrollPaused = true;
      isScrolling = true;
      
      const start = scrollContainer.scrollTop;
      const startTime = performance.now();
      const duration = isMobile ? 1500 : 2000; 
      
      function scrollStep(timestamp) {
        const elapsed = timestamp - startTime;
        const progress = Math.min(elapsed / duration, 1);
        

        const easeProgress = 1 - Math.pow(1 - progress, 3);
        
        scrollContainer.scrollTop = start * (1 - easeProgress);
        
        if (progress < 1) {
          requestAnimationFrame(scrollStep);
        } else {

          isScrolling = false;
          setTimeout(() => {
            scrollPaused = false;
            autoScroll();
          }, isMobile ? 1500 : 2000); 
        }
      }
      
      requestAnimationFrame(scrollStep);
    }
    

    function highlightVisibleParagraphs() {

      if (!document.body.contains(scrollContainer)) return;
      
      const paragraphs = scrollText.querySelectorAll('p');
      const containerRect = scrollContainer.getBoundingClientRect();
      
      paragraphs.forEach(p => {
        const rect = p.getBoundingClientRect();
        

        const isVisible = (
          rect.top < containerRect.bottom - 10 && 
          rect.bottom > containerRect.top + 10
        );
        

        if (isVisible) {

          p.classList.add('visible-paragraph');
          p.classList.remove('hidden-paragraph');
        } else {
          p.classList.add('hidden-paragraph');
          p.classList.remove('visible-paragraph');
        }
      });
    }
    

    let touchTimeout;
    

    scrollContainer.addEventListener('mouseenter', () => {
      scrollPaused = true;
      lastScrollTop = scrollContainer.scrollTop;
    });
    
    scrollContainer.addEventListener('mouseleave', () => {

      if (Math.abs(scrollContainer.scrollTop - lastScrollTop) < 30) {
        scrollPaused = false;
        autoScroll();
      }
    });
    

    scrollContainer.addEventListener('scroll', throttle(function() {

      lastScrollTop = scrollContainer.scrollTop;
      

      throttledHighlightParagraphs();
    }, 100)); 
    

    scrollContainer.addEventListener('touchstart', () => {
      scrollPaused = true;
      lastScrollTop = scrollContainer.scrollTop;
      clearTimeout(touchTimeout);
    });
    
    scrollContainer.addEventListener('touchend', () => {

      clearTimeout(touchTimeout);
      touchTimeout = setTimeout(() => {

        if (Math.abs(scrollContainer.scrollTop - lastScrollTop) < 30) {
          scrollPaused = false;
          autoScroll();
        }
      }, 800); 
    });
    

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
    

    return function cleanup() {
      clearTimeout(startScrollTimer);
      cancelAnimationFrame(scrollTimer);
      clearTimeout(touchTimeout);
      scrollActive = false;
      document.head.removeChild(style);
    };
  }


  function animateAboutSection() {
    const aboutSection = document.getElementById('about');
    if (!aboutSection) return null;
    

    const isMobile = window.innerWidth <= 768;


    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {

          aboutSection.classList.add('about-active');


          const aboutTitle = aboutSection.querySelector('h2');
          if (aboutTitle) {
            aboutTitle.classList.add('title-animation');
          }


          const panels = aboutSection.querySelectorAll('.panel-main, .panel-small');
          panels.forEach((panel, index) => {
            setTimeout(() => {
              panel.classList.add('panel-animation');
            }, isMobile ? 120 * index : 180 * index); 
          });


          const aboutImage = aboutSection.querySelector('.img-rounded');
          if (aboutImage) {
            setTimeout(() => {
              aboutImage.classList.add('image-animation');
            }, isMobile ? 300 : 400);
          }


          const textElements = aboutSection.querySelectorAll('.text-custom');
          textElements.forEach((text, index) => {
            setTimeout(() => {
              text.classList.add('text-animation');
            }, (isMobile ? 500 : 600) + (isMobile ? 80 : 100) * index);
          });


          observer.disconnect();
        }
      });
    }, { 
      threshold: isMobile ? 0.1 : 0.2, 
      rootMargin: '0px 0px -10% 0px'
    });

    observer.observe(aboutSection);
    return observer;
  }


  function setupHoverEffects() {

    if (window.innerWidth <= 768) return;
    
    const panels = document.querySelectorAll('.panel-small, .panel-main');
    

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
    

    return function cleanup() {
      document.head.removeChild(style);
    };
  }


  function initializeAnimations() {

    const cleanupFunctions = [];
    

    const scrollObserver = setupScrollAnimations();
    if (scrollObserver) {
      cleanupFunctions.push(() => scrollObserver.disconnect());
    }
    

    const hoverCleanup = setupHoverEffects();
    if (hoverCleanup) {
      cleanupFunctions.push(hoverCleanup);
    }
    

    const aboutObserver = animateAboutSection();
    if (aboutObserver) {
      cleanupFunctions.push(() => aboutObserver.disconnect());
    }
    

    const autoScrollCleanup = setupAutoScroll();
    if (autoScrollCleanup) {
      cleanupFunctions.push(autoScrollCleanup);
    }
    

    const resizeHandler = throttle(function() {

      cleanupFunctions.forEach(cleanup => {
        if (typeof cleanup === 'function') {
          cleanup();
        }
      });
      

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
    

    window.addEventListener('resize', resizeHandler);
    

    window.addEventListener('unload', () => {
      cleanupFunctions.forEach(cleanup => {
        if (typeof cleanup === 'function') {
          cleanup();
        }
      });
    });
  }
  

  if ('requestIdleCallback' in window) {
    requestIdleCallback(() => initializeAnimations(), { timeout: 1000 });
  } else {

    setTimeout(initializeAnimations, 100);
  }
});