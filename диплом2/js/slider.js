// slider.js - ТОЛЬКО для баннера
document.addEventListener('DOMContentLoaded', function() {
    console.log('Загрузка слайдера баннера...');
    
    const slides = document.querySelectorAll('.banner .slide');
    const dots = document.querySelectorAll('.banner .dot');
    const prevBtn = document.querySelector('.banner .slider-btn.prev');
    const nextBtn = document.querySelector('.banner .slider-btn.next');
    
    if (!slides.length) {
        console.error('Не найдены слайды баннера');
        return;
    }
    
    console.log('Найдено слайдов баннера:', slides.length);
    
    let currentSlide = 0;
    let slideInterval;
    
    function showSlide(n) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        currentSlide = (n + slides.length) % slides.length;
        
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }
    
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    function startSlider() {
        slideInterval = setInterval(nextSlide, 5000);
    }
    
    function stopSlider() {
        clearInterval(slideInterval);
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            stopSlider();
            nextSlide();
            startSlider();
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            stopSlider();
            showSlide(currentSlide - 1);
            startSlider();
        });
    }
    
    if (dots.length) {
        dots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                stopSlider();
                showSlide(index);
                startSlider();
            });
        });
    }
    
    const banner = document.querySelector('.banner');
    if (banner) {
        banner.addEventListener('mouseenter', stopSlider);
        banner.addEventListener('mouseleave', startSlider);
    }
    
    startSlider();
});