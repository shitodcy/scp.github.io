document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    const toggleBtn = document.querySelector('.toggle-btn');
    const menuItems = document.querySelector('.menu-items');
    const navLinks = document.querySelectorAll('.menu-items a');

    // Throttle function untuk mengurangi frekuensi event scroll
    function throttle(func, delay) {
      let lastCall = 0;
      return function(...args) {
        const now = Date.now();
        if (now - lastCall >= delay) {
          lastCall = now;
          func.apply(this, args);
        }
      }
    }

    // Handle scroll effect dengan throttle untuk performa lebih baik
    window.addEventListener('scroll', throttle(function() {
      // Tambahkan smooth transition dengan requestAnimationFrame
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
    }, 100)); // Throttle 100ms untuk pengalaman lebih halus

    // Toggle menu pada mobile dengan efek lebih halus
    toggleBtn.addEventListener('click', function(e) {
      e.stopPropagation(); // Mencegah event bubbling
      toggleBtn.classList.toggle('active');

      if (menuItems.classList.contains('closing')) {
        menuItems.classList.remove('closing');
      }

      // Gunakan transisi yang lebih halus
      requestAnimationFrame(() => {
        menuItems.classList.toggle('active');
      });
    });

    // Tambahkan animation delay ke setiap nav link
    navLinks.forEach((link, index) => {
      link.style.setProperty('--item-index', index);
    });

    // Tutup menu saat link diklik dengan transisi yang lebih halus
    navLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        // Hanya tutup menu di mobile view
        if (window.innerWidth <= 1024) {
          toggleBtn.classList.remove('active');
          menuItems.classList.add('closing');

          // Gunakan transisi yang lebih pendek
          setTimeout(() => {
            menuItems.classList.remove('active');
            menuItems.classList.remove('closing');
          }, 250); // Waktunya lebih cepat
        }
      });
    });

    // Tutup menu saat mengklik di luar navbar dengan animasi lebih halus
    document.addEventListener('click', function(event) {
      const isClickInside = navbar.contains(event.target);

      if (!isClickInside && menuItems.classList.contains('active') && window.innerWidth <= 1024) {
        toggleBtn.classList.remove('active');
        menuItems.classList.add('closing');

        // Gunakan transisi yang lebih pendek
        setTimeout(() => {
          menuItems.classList.remove('active');
          menuItems.classList.remove('closing');
        }, 250);
      }
    });

    // Deteksi section aktif dengan performa lebih baik
    const sections = document.querySelectorAll("section");

    // Observer untuk mendeteksi section yang visible
    const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      highlightNavLink(entry.target);
    }
  });
}, 
{
  threshold: 0.3, // Section harus terlihat minimal 30% untuk diaktifkan
  rootMargin: "0px 0px" // Offset untuk trigger yang lebih akurat
});

    // Observe semua section
    sections.forEach(section => {
      observer.observe(section);
    });

    // Fungsi untuk highlight link yang aktif
    function highlightNavLink(currentSection) {
navLinks.forEach(link => {
  link.classList.remove("active");
});
    }

    // Tambahkan smooth scroll behavior
    navLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        // Cek apakah link mengarah ke section di halaman yang sama
        const href = link.getAttribute('href');
        if (href.startsWith('#')) {
          e.preventDefault();
          const targetId = href.substring(1);
          const targetElement = document.getElementById(targetId);

          if (targetElement) {
            // Animasi scroll yang lebih halus
            window.scrollTo({
              top: targetElement.offsetTop - navbar.offsetHeight,
              behavior: 'smooth'
            });
          }
        }
      });
    });
  });