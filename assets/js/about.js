document.addEventListener('DOMContentLoaded', function() {
    // Function to handle smooth scroll animation and fade in elements
    function handleScrollAnimations() {
      const elements = document.querySelectorAll('.slide-up');
      elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        const elementVisible = 150;
        if (elementPosition < windowHeight - elementVisible) {
          // Add delay based on data-delay attribute
          const delay = element.dataset.delay || 0;
          setTimeout(() => {
            element.classList.add('active');
          }, delay);
        }
      });
    }
  
    // Auto-scroll function for scrollable containers
    function setupAutoScroll() {
      const scrollContainer = document.getElementById('scrollContainer');
      if (!scrollContainer) return;
      
      const scrollText = document.getElementById('scrollText');
      if (!scrollText) return;
      
      // Variables for autoscroll
      let scrollActive = false;
      let scrollSpeed = 0.5; // pixels per frame
      let scrollPaused = false;
      let lastScrollTop = 0;
      
      // Start auto-scrolling after a delay
      setTimeout(() => {
        scrollActive = true;
        autoScroll();
      }, 2000); // Start after 2 seconds
      
      // Autoscroll animation function
      function autoScroll() {
        if (!scrollActive || scrollPaused) return;
        
        // Only scroll if we haven't reached the bottom yet
        const scrollMax = scrollContainer.scrollHeight - scrollContainer.clientHeight;
        
        if (scrollContainer.scrollTop < scrollMax) {
          scrollContainer.scrollTop += scrollSpeed;
          
          // Highlight current paragraphs
          highlightVisibleParagraphs();
          
          // Continue the animation
          requestAnimationFrame(autoScroll);
        } else {
          // When we reach the bottom, reset after a pause
          setTimeout(() => {
            // Smooth scroll back to top
            scrollToTop();
          }, 5000); // Wait 5 seconds at the bottom
        }
      }
      
      // Smooth scroll to top function
      function scrollToTop() {
        scrollPaused = true;
        
        const start = scrollContainer.scrollTop;
        const startTime = performance.now();
        const duration = 2000; // 2 seconds to scroll back to top
        
        function scrollStep(timestamp) {
          const elapsed = timestamp - startTime;
          const progress = Math.min(elapsed / duration, 1);
          const easeProgress = 1 - Math.pow(1 - progress, 3); // Cubic ease out
          
          scrollContainer.scrollTop = start * (1 - easeProgress);
          
          if (progress < 1) {
            requestAnimationFrame(scrollStep);
          } else {
            // Reset and restart
            setTimeout(() => {
              scrollPaused = false;
              autoScroll();
            }, 2000); // Wait 2 seconds before restarting
          }
        }
        
        requestAnimationFrame(scrollStep);
      }
      
      // Function to highlight visible paragraphs
      function highlightVisibleParagraphs() {
        const paragraphs = scrollText.querySelectorAll('p');
        paragraphs.forEach(p => {
          const rect = p.getBoundingClientRect();
          const containerRect = scrollContainer.getBoundingClientRect();
          
          // Check if paragraph is fully or partially visible
          const isVisible = (
            rect.top < containerRect.bottom - 20 && 
            rect.bottom > containerRect.top + 20
          );
          
          // Different opacities based on visibility
          if (isVisible) {
            const visibilityPercent = Math.min(
              1, 
              Math.min(
                (rect.bottom - containerRect.top) / rect.height,
                (containerRect.bottom - rect.top) / rect.height
              )
            );
            
            p.style.opacity = 0.7 + (visibilityPercent * 0.3);
            p.style.transform = 'translateX(0)';
          } else {
            p.style.opacity = 0.5;
            p.style.transform = rect.top > containerRect.bottom ? 'translateX(5px)' : 'translateX(-5px)';
          }
        });
      }
      
      // Pause auto-scrolling when user interacts with the scroll container
      scrollContainer.addEventListener('mouseenter', () => {
        scrollPaused = true;
        lastScrollTop = scrollContainer.scrollTop;
      });
      
      scrollContainer.addEventListener('mouseleave', () => {
        // Only restart if it hasn't been manually scrolled too much
        if (Math.abs(scrollContainer.scrollTop - lastScrollTop) < 50) {
          scrollPaused = false;
          autoScroll();
        }
      });
      
      // When user manually scrolls, temporarily disable auto-scrolling
      scrollContainer.addEventListener('scroll', function() {
        // Store last known position
        lastScrollTop = scrollContainer.scrollTop;
        
        // Still update highlighting while scrolling manually
        highlightVisibleParagraphs();
      });
      
      // Touch events for mobile
      scrollContainer.addEventListener('touchstart', () => {
        scrollPaused = true;
        lastScrollTop = scrollContainer.scrollTop;
      });
      
      scrollContainer.addEventListener('touchend', () => {
        // Wait a bit to ensure user is done touching
        setTimeout(() => {
          // Only restart if it hasn't been manually scrolled too much
          if (Math.abs(scrollContainer.scrollTop - lastScrollTop) < 50) {
            scrollPaused = false;
            autoScroll();
          }
        }, 1000);
      });
    }
  
    // Add special animations for About section when it comes into view
    function animateAboutSection() {
      const aboutSection = document.getElementById('about');
      if (!aboutSection) return;
  
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            // Add animation class to the section itself
            aboutSection.classList.add('about-active');
  
            // Animate title with a bounce effect
            const aboutTitle = aboutSection.querySelector('h2');
            if (aboutTitle) {
              aboutTitle.classList.add('title-animation');
            }
  
            // Animate each panel with staggered delay
            const panels = aboutSection.querySelectorAll('.panel-main, .panel-small');
            panels.forEach((panel, index) => {
              setTimeout(() => {
                panel.classList.add('panel-animation');
              }, 200 * index); // Staggered delay
            });
  
            // Animate the image with a special effect
            const aboutImage = aboutSection.querySelector('.img-rounded');
            if (aboutImage) {
              setTimeout(() => {
                aboutImage.classList.add('image-animation');
              }, 500);
            }
  
            // Animate text content with a fade-in sequence
            const textElements = aboutSection.querySelectorAll('.text-custom');
            textElements.forEach((text, index) => {
              setTimeout(() => {
                text.classList.add('text-animation');
              }, 700 + (100 * index));
            });
  
            // Once animations are done, disconnect the observer
            observer.disconnect();
          }
        });
      }, { threshold: 0.2 }); // Trigger when 20% of the section is visible
  
      observer.observe(aboutSection);
    }
  
    // Add hover effect to panels
    const panels = document.querySelectorAll('.panel-small, .panel-main');
    panels.forEach(panel => {
      panel.addEventListener('mouseenter', function() {
        this.style.transform = this.classList.contains('panel-small')
          ? 'rotateY(5deg) rotateX(5deg)'
          : 'translateY(-8px)';
      });
      panel.addEventListener('mouseleave', function() {
        this.style.transform = '';
      });
    });
  
    // Run initial animations
    setTimeout(handleScrollAnimations, 100);
    
    // Initialize about section animations
    animateAboutSection();
    
    // Setup auto-scrolling for scrollable content
    setupAutoScroll();
  
    // Listen for scroll events
    window.addEventListener('scroll', handleScrollAnimations);
  
    // Handle window resize events for responsive adjustments
    window.addEventListener('resize', function() {
      // Reset transforms on resize
      panels.forEach(panel => {
        panel.style.transform = '';
      });
      // Re-run animations
      handleScrollAnimations();
    });
  });