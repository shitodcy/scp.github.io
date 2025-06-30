document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    const toggleBtn = document.querySelector('.toggle-btn');
    const menuItems = document.querySelector('.menu-items');
    const navLinks = document.querySelectorAll('.menu-items a');
    

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
  

    window.addEventListener('scroll', throttle(function() {

      requestAnimationFrame(() => {
        if (window.scrollY > 50) {
          if (!navbar.classList.contains('scrolled')) {
            navbar.classList.add('scrolled');
          }
        } else {
          if (navbar.classList.contains('scrolled')) {
            navbar.classList.remove('scrolled');
          }
        }
      });
    }, 100)); 
    

    toggleBtn.addEventListener('click', function(e) {
      e.stopPropagation(); 
      toggleBtn.classList.toggle('active');
      
      if (menuItems.classList.contains('closing')) {
        menuItems.classList.remove('closing');
      }
      

      requestAnimationFrame(() => {
        menuItems.classList.toggle('active');
      });
    });
    

    navLinks.forEach((link, index) => {
      link.style.setProperty('--item-index', index);
    });
    

    navLinks.forEach(link => {
      link.addEventListener('click', function(e) {

        if (window.innerWidth <= 1024) {
          toggleBtn.classList.remove('active');
          menuItems.classList.add('closing');
          

          setTimeout(() => {
            menuItems.classList.remove('active');
            menuItems.classList.remove('closing');
          }, 250); 
        }
      });
    });
    

    document.addEventListener('click', function(event) {
      const isClickInside = navbar.contains(event.target);
      
      if (!isClickInside && menuItems.classList.contains('active') && window.innerWidth <= 1024) {
        toggleBtn.classList.remove('active');
        menuItems.classList.add('closing');
        

        setTimeout(() => {
          menuItems.classList.remove('active');
          menuItems.classList.remove('closing');
        }, 250);
      }
    });
    

    const sections = document.querySelectorAll("section");
    

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const currentSection = entry.target.getAttribute("id");
          highlightNavLink(currentSection);
        }
      });
    }, {
      threshold: 0.3, 
      rootMargin: "0px 0px" 
    });
    

    sections.forEach(section => {
      observer.observe(section);
    });
    

    function highlightNavLink(currentSection) {
      navLinks.forEach(link => {
        link.classList.remove("active");
        if (link.getAttribute("href").includes(currentSection)) {
          link.classList.add("active");
        }
      });
    }
    

    navLinks.forEach(link => {
      link.addEventListener('click', (e) => {

        const href = link.getAttribute('href');
        if (href.startsWith('#')) {
          e.preventDefault();
          const targetId = href.substring(1);
          const targetElement = document.getElementById(targetId);
          
          if (targetElement) {

            window.scrollTo({
              top: targetElement.offsetTop - navbar.offsetHeight,
              behavior: 'smooth'
            });
          }
        }
      });
    });
  });