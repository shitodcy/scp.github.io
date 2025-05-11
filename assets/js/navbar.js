document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    const toggleBtn = document.querySelector('.toggle-btn');
    const menuItems = document.querySelector('.menu-items');
    const navLinks = document.querySelectorAll('.menu-items a');
    
    // Handle scroll effect
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Toggle menu on mobile
    toggleBtn.addEventListener('click', function() {
        toggleBtn.classList.toggle('active');
        if (menuItems.classList.contains('closing')) {
            menuItems.classList.remove('closing');
        }
        menuItems.classList.toggle('active');
    });
    
    // Add animation delay to each nav link
    navLinks.forEach((link, index) => {
        link.style.setProperty('--item-index', index);
    });
    
    // Close menu when a link is clicked
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                toggleBtn.classList.remove('active');
                menuItems.classList.add('closing');
                setTimeout(() => {
                    menuItems.classList.remove('active');
                    menuItems.classList.remove('closing');
                }, 300);
            }
        });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const isClickInside = navbar.contains(event.target);
        
        if (!isClickInside && menuItems.classList.contains('active') && window.innerWidth <= 768) {
            toggleBtn.classList.remove('active');
            menuItems.classList.add('closing');
            setTimeout(() => {
                menuItems.classList.remove('active');
                menuItems.classList.remove('closing');
            }, 300);
        }
    });
});

const sections = document.querySelectorAll("section");
const navLinks = document.querySelectorAll(".menu-items a");

window.addEventListener("scroll", () => {
  let currentSection = "";

  sections.forEach(section => {
    const sectionTop = section.offsetTop - 100; // offset biar pas scroll
    if (window.scrollY >= sectionTop) {
      currentSection = section.getAttribute("id");
    }
  });

  navLinks.forEach(link => {
    link.classList.remove("active");
    if (link.getAttribute("href").includes(currentSection)) {
      link.classList.add("active");
    }
  });
});

navLinks.forEach(link => {
    link.addEventListener('click', () => {
      navLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
    });
  });
  