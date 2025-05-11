document.addEventListener('DOMContentLoaded', function() {
    // Open first category by default
    const firstCategory = document.querySelector('.category-content');
    if (firstCategory) {
      firstCategory.classList.add('active');
      const toggleIcon = firstCategory.previousElementSibling.querySelector('.toggle-icon');
      if (toggleIcon) toggleIcon.textContent = '▲';
    }
  
    // Toggle category visibility
    const categoryTitles = document.querySelectorAll('.category-title');
    categoryTitles.forEach(title => {
      title.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const content = document.getElementById(targetId);
        const toggleIcon = this.querySelector('.toggle-icon');
        
        if (content.classList.contains('active')) {
          content.classList.remove('active');
          toggleIcon.textContent = '▼';
        } else {
          content.classList.add('active');
          toggleIcon.textContent = '▲';
        }
      });
    });
  
    // Filter functionality with simplified animations
    const filterButtons = document.querySelectorAll('.filter-btn');
    const allSections = document.querySelectorAll('.menu-section');
    const menuItems = document.querySelectorAll('.menu-item');
    
    filterButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Remove active class from all buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));
        
        // Add active class to current button
        this.classList.add('active');
        this.classList.add('pulse-animation');
        
        // Remove the animation class after it completes
        setTimeout(() => {
          this.classList.remove('pulse-animation');
        }, 300);
        
        const target = this.getAttribute('data-target');
        
        // Simple fade effect for all menu items
        document.querySelectorAll('.menu-items-container').forEach(container => {
          container.style.opacity = '0.3';
          
          setTimeout(() => {
            // Process the filtering
            if (target === 'all') {
              // Show all sections
              allSections.forEach(section => {
                section.style.display = 'block';
              });
              
              // Show all menu items
              menuItems.forEach(item => {
                item.style.display = 'block';
              });
            } else {
              // Only show targeted section
              allSections.forEach(section => {
                if (section.id === target + 'Section') {
                  section.style.display = 'block';
                  
                  // Open the category
                  const content = document.getElementById(target + 'Content');
                  if (content && !content.classList.contains('active')) {
                    content.classList.add('active');
                    const toggleIcon = content.previousElementSibling.querySelector('.toggle-icon');
                    if (toggleIcon) toggleIcon.textContent = '▲';
                  }
                } else {
                  section.style.display = 'none';
                }
              });
              
              // Only show items from the selected category
              menuItems.forEach(item => {
                if (item.getAttribute('data-category') === target) {
                  item.style.display = 'block';
                } else {
                  item.style.display = 'none';
                }
              });
            }
            
            // Restore opacity with smooth transition
            setTimeout(() => {
              document.querySelectorAll('.menu-items-container').forEach(container => {
                container.style.opacity = '1';
              });
            }, 100);
          }, 200);
        });
      });
    });
  
    // Back to top functionality
    const backToTopButton = document.getElementById('backToTop');
    if (backToTopButton) {
      window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
          backToTopButton.classList.add('visible');
        } else {
          backToTopButton.classList.remove('visible');
        }
      });
      
      backToTopButton.addEventListener('click', function() {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    }
  });