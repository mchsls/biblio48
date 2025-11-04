// Улучшенная активация выпадающих меню
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.dropdown');
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNav = document.querySelector('.main-nav');
    
    // Для десктопной версии - плавное появление
    dropdowns.forEach(dropdown => {
        const content = dropdown.querySelector('.dropdown-content');
        
        // Предварительная настройка для плавности
        content.style.transition = 'all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        
        dropdown.addEventListener('mouseenter', function() {
            if (window.innerWidth > 768) {
                content.style.display = 'block';
                
                // Небольшая задержка для плавности
                requestAnimationFrame(() => {
                    content.style.opacity = '1';
                    content.style.visibility = 'visible';
                    content.style.transform = 'translateY(0)';
                });
            }
        });
        
        dropdown.addEventListener('mouseleave', function() {
            if (window.innerWidth > 768) {
                content.style.opacity = '0';
                content.style.visibility = 'hidden';
                content.style.transform = 'translateY(-10px)';
                
                setTimeout(() => {
                    content.style.display = 'none';
                }, 300);
            }
        });
        
        // Для мобильной версии
        if (window.innerWidth <= 768) {
            const dropdownLink = dropdown.querySelector('a');
            dropdownLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Закрываем другие открытые dropdown
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.classList.remove('active');
                    }
                });
                
                dropdown.classList.toggle('active');
            });
        }
    });
    
    // Мобильное меню
    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', function() {
            mainNav.classList.toggle('active');
        });
    }
    
    // Закрытие мобильного меню при клике на ссылку
    const navLinks = document.querySelectorAll('.main-nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                mainNav.classList.remove('active');
                
                // Закрываем все выпадающие меню
                dropdowns.forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
    });
    
    // Обработка изменения размера окна
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            mainNav.classList.remove('active');
            
            // Сбрасываем состояние выпадающих меню
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
                const content = dropdown.querySelector('.dropdown-content');
                content.style.display = 'none';
                content.style.opacity = '0';
                content.style.visibility = 'hidden';
                content.style.transform = 'translateY(-10px)';
            });
        }
    });
    
    // Закрытие dropdown при клике вне меню
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown') && !e.target.closest('.mobile-menu-btn')) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
});