document.addEventListener('DOMContentLoaded', function() {
    // Toggle category sections
    const categoryToggles = document.querySelectorAll('.category-title');
    categoryToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetContent = document.getElementById(targetId);
            
            if (targetContent.classList.contains('active-category')) {
                targetContent.classList.remove('active-category');
                this.querySelector('.toggle-icon').textContent = '▼';
            } else {
                targetContent.classList.add('active-category');
                this.querySelector('.toggle-icon').textContent = '▲';
            }
        });
    });
    
    // Back to top button
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });
    
    backToTop.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to current button
            this.classList.add('active');
            
            const target = this.getAttribute('data-target');
            const menuCards = document.querySelectorAll('.menu-card');
            
            if (target === 'all') {
                // Show all sections
                document.querySelectorAll('.menu-section').forEach(section => {
                    section.style.display = 'block';
                });
                // Show all cards
                menuCards.forEach(card => {
                    card.style.display = 'block';
                });
            } else {
                // Only show relevant section
                document.querySelectorAll('.menu-section').forEach(section => {
                    if (section.id === target + 'Section') {
                        section.style.display = 'block';
                        const content = section.querySelector('.category-content');
                        if (!content.classList.contains('active-category')) {
                            content.classList.add('active-category');
                            section.querySelector('.toggle-icon').textContent = '▲';
                        }
                    } else {
                        section.style.display = 'none';
                    }
                });
                
                // Only show cards of the selected category
                menuCards.forEach(card => {
                    if (card.getAttribute('data-category') === target) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const menuItems = document.querySelectorAll('.menu-item');
        
        if (searchTerm === '') {
            // Reset the view if search is cleared
            menuItems.forEach(item => {
                item.closest('.menu-card').style.display = 'block';
            });
            
            // Show all sections
            document.querySelectorAll('.menu-section').forEach(section => {
                section.style.display = 'block';
            });
            
            // Reset category filters
            filterButtons.forEach(btn => {
                if (btn.getAttribute('data-target') === 'all') {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            
            return;
        }
        
        // Handle searching
        let hasResults = false;
        menuItems.forEach(item => {
            const menuName = item.querySelector('.menu-name').textContent.toLowerCase();
            const menuPrice = item.querySelector('.menu-price').textContent.toLowerCase();
            
            if (menuName.includes(searchTerm) || menuPrice.includes(searchTerm)) {
                item.closest('.menu-card').style.display = 'block';
                hasResults = true;
                
                // Make sure its parent section is open
                const section = item.closest('.menu-section');
                section.style.display = 'block';
                
                const content = section.querySelector('.category-content');
                if (!content.classList.contains('active-category')) {
                    content.classList.add('active-category');
                    section.querySelector('.toggle-icon').textContent = '▲';
                }
            } else {
                item.closest('.menu-card').style.display = 'none';
            }
        });
        
        // Handle no results case
        if (!hasResults) {
            // Optional: Show a "no results" message
            console.log('No results found');
        }
    });
});