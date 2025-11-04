// Автоматическая бесконечная прокрутка афиши с изображениями
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎬 ЗАПУСК БЕСКОНЕЧНОЙ АФИШИ С ИЗОБРАЖЕНИЯМИ');
    
    const eventsTrack = document.getElementById('eventsTrack');
    if (!eventsTrack) {
        console.error('❌ Не найден eventsTrack');
        return;
    }
    
    // Клонируем слайды для бесконечной прокрутки
    const slides = eventsTrack.querySelectorAll('.event-slide');
    if (slides.length === 0) {
        console.error('❌ Нет слайдов для отображения');
        return;
    }
    
    // Создаем клоны для бесконечной прокрутки
    slides.forEach(slide => {
        const clone = slide.cloneNode(true);
        eventsTrack.appendChild(clone);
    });
    
    const allSlides = eventsTrack.querySelectorAll('.event-slide');
    const slideWidth = slides[0].offsetWidth + 30; // ширина + gap
    let currentIndex = 0;
    let autoScrollInterval;
    let isAnimating = false;
    
    // Устанавливаем начальную позицию
    function setInitialPosition() {
        eventsTrack.style.transform = `translateX(0px)`;
    }
    
    // Функция для плавной прокрутки
    function scrollToSlide(index) {
        if (isAnimating) return;
        isAnimating = true;
        
        const translateX = -index * slideWidth;
        eventsTrack.style.transition = 'transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        eventsTrack.style.transform = `translateX(${translateX}px)`;
        
        console.log(`➡️ Прокрутка к слайду ${index}`);
        
        // После завершения анимации
        setTimeout(() => {
            // Если достигли конца клонов, переходим к началу оригинальных слайдов
            if (index >= slides.length) {
                eventsTrack.style.transition = 'none';
                eventsTrack.style.transform = `translateX(0px)`;
                currentIndex = 0;
            }
            isAnimating = false;
        }, 800);
    }
    
    function nextEvent() {
        currentIndex++;
        scrollToSlide(currentIndex);
    }
    
    function startAutoScroll() {
        console.log('▶️ Запуск автоматической прокрутки афиши');
        stopAutoScroll();
        
        autoScrollInterval = setInterval(() => {
            if (!isAnimating) {
                nextEvent();
            }
        }, 4000); // Смена каждые 4 секунды
    }
    
    function stopAutoScroll() {
        if (autoScrollInterval) {
            clearInterval(autoScrollInterval);
            autoScrollInterval = null;
        }
    }
    
    // Обработчики для паузы при взаимодействии
    function setupHoverHandlers() {
        const eventsSection = document.querySelector('.events-section');
        
        eventsSection.addEventListener('mouseenter', function() {
            console.log('⏸️ Пауза автоматической прокрутки');
            stopAutoScroll();
        });
        
        eventsSection.addEventListener('mouseleave', function() {
            console.log('▶️ Возобновление автоматической прокрутки');
            startAutoScroll();
        });
        
        // Для мобильных устройств
        eventsSection.addEventListener('touchstart', function() {
            console.log('⏸️ Пауза (касание)');
            stopAutoScroll();
        });
        
        eventsSection.addEventListener('touchend', function() {
            setTimeout(() => {
                console.log('▶️ Возобновление (касание завершено)');
                startAutoScroll();
            }, 3000);
        });
    }
    
    // Обработка изменения размера окна
    function setupResizeHandler() {
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                // Пересчитываем ширину слайда
                const newSlideWidth = slides[0].offsetWidth + 30;
                slideWidth = newSlideWidth;
                
                // Обновляем позицию
                const translateX = -currentIndex * slideWidth;
                eventsTrack.style.transform = `translateX(${translateX}px)`;
            }, 250);
        });
    }
    
    // Добавляем обработчики свайпа для мобильных устройств
    function setupSwipeHandlers() {
        let startX = 0;
        let currentX = 0;
        let isSwiping = false;
        
        eventsTrack.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            currentX = startX;
            isSwiping = true;
            stopAutoScroll();
        });
        
        eventsTrack.addEventListener('touchmove', (e) => {
            if (!isSwiping) return;
            currentX = e.touches[0].clientX;
        });
        
        eventsTrack.addEventListener('touchend', () => {
            if (!isSwiping) return;
            
            const diff = startX - currentX;
            const swipeThreshold = 50;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Свайп влево - следующий слайд
                    nextEvent();
                } else {
                    // Свайп вправо - предыдущий слайд (можно добавить при необходимости)
                }
            }
            
            isSwiping = false;
            setTimeout(() => {
                startAutoScroll();
            }, 3000);
        });
    }
    
    // Инициализация
    function init() {
        console.log('🚀 ИНИЦИАЛИЗАЦИЯ БЕСКОНЕЧНОЙ АФИШИ');
        
        // Устанавливаем начальную позицию
        setInitialPosition();
        
        // Настройка обработчиков
        setupHoverHandlers();
        setupResizeHandler();
        setupSwipeHandlers();
        
        // Запускаем автоматическую прокрутку через 2 секунды
        setTimeout(() => {
            startAutoScroll();
        }, 2000);
        
        console.log('✅ Бесконечная афиша успешно инициализирована');
        console.log(`📊 Оригинальных мероприятий: ${slides.length}`);
        console.log(`📊 Всего слайдов с клонами: ${allSlides.length}`);
    }
    
    // Запуск
    init();
});