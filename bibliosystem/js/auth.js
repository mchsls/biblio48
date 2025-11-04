// Улучшенная система авторизации с интеграцией в PHP систему бронирования
document.addEventListener('DOMContentLoaded', function() {
    const loginBtn = document.getElementById('loginBtn');
    const loginModal = document.getElementById('loginModal');
    const closeModal = document.getElementById('closeModal');
    const loginForm = document.getElementById('loginForm');
    
    // URL нашей PHP системы бронирования
    const LIBRARY_SYSTEM_URL = 'http://bibliosystem';
    
    // Проверить авторизацию при загрузке
    checkAuthStatus();
    
    // Открытие модального окна - ПЕРЕХОД В СИСТЕМУ БРОНИРОВАНИЯ
    loginBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Прямой переход в систему бронирования
        window.open(`${LIBRARY_SYSTEM_URL}/library_system.php`, '_blank');
        
        // Или показываем модальное окно с выбором (раскомментируйте если нужно)
        // showSystemModal();
    });
    
    // Закрытие модального окна
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            loginModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    }
    
    // Закрытие при клике вне модального окна
    window.addEventListener('click', function(e) {
        if (e.target === loginModal) {
            loginModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Обработка формы входа (если оставляем локальную форму)
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Перенаправляем в PHP систему с параметрами
            const loginUrl = `${LIBRARY_SYSTEM_URL}/login.php?username=${encodeURIComponent(username)}&auto=true`;
            window.open(loginUrl, '_blank');
            
            // Закрываем модальное окно
            loginModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            showNotification('Переход в систему бронирования...', 'info');
        });
    }
    
    // Функция проверки статуса авторизации
    function checkAuthStatus() {
        // Проверяем, есть ли данные о входе в localStorage
        const userData = localStorage.getItem('library_user');
        
        if (userData) {
            try {
                const user = JSON.parse(userData);
                updateUserInterface(user);
            } catch (e) {
                console.error('Ошибка парсинга пользователя:', e);
                localStorage.removeItem('library_user');
            }
        }
        
        // Также проверяем куки (если нужно)
        checkCookieAuth();
    }
    
    // Проверка авторизации через куки (если системы на одном домене)
    function checkCookieAuth() {
        // Эта функция может проверять куки, если системы на одном домене
        // Для разных доменов используем localStorage
    }
    
    // Функция обновления интерфейса после входа
    function updateUserInterface(user) {
        const userPanel = document.getElementById('userPanel');
        const userName = document.getElementById('userName');
        const userRole = document.getElementById('userRole');
        const adminLink = document.getElementById('adminLink');
        
        if (userPanel && userName && userRole) {
            userName.textContent = user.name || user.username;
            userRole.textContent = user.role === 'admin' ? 'Администратор' : 'Пользователь';
            userPanel.style.display = 'block';
            
            // Обновляем кнопку входа
            if (loginBtn) {
                loginBtn.innerHTML = `<i class="fas fa-user-check"></i>${user.name || user.username}`;
                loginBtn.style.background = '#28a745';
                
                // Обновляем обработчик - теперь ведет в личный кабинет
                loginBtn.onclick = function(e) {
                    e.preventDefault();
                    const userUrl = user.role === 'admin' ? 
                        `${LIBRARY_SYSTEM_URL}/admin/` : 
                        `${LIBRARY_SYSTEM_URL}/user/`;
                    window.open(userUrl, '_blank');
                };
            }
            
            // Показываем админ-ссылку если нужно
            if (user.role === 'admin' && adminLink) {
                adminLink.style.display = 'inline-block';
                adminLink.href = `${LIBRARY_SYSTEM_URL}/admin/`;
                adminLink.target = '_blank';
            }
        }
    }
    
    // Обработчик выхода
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            // Выход из системы бронирования
            localStorage.removeItem('library_user');
            
            const userPanel = document.getElementById('userPanel');
            const adminLink = document.getElementById('adminLink');
            
            if (userPanel) userPanel.style.display = 'none';
            if (adminLink) adminLink.style.display = 'none';
            
            // Восстанавливаем кнопку входа
            if (loginBtn) {
                loginBtn.innerHTML = `<i class="fas fa-user"></i>Онлайн-система`;
                loginBtn.style.background = '';
                loginBtn.onclick = function(e) {
                    e.preventDefault();
                    window.open(`${LIBRARY_SYSTEM_URL}/library_system.php`, '_blank');
                };
            }
            
            // Также выполняем выход в системе бронирования
            window.open(`${LIBRARY_SYSTEM_URL}/logout.php`, '_blank');
            
            showNotification('Вы вышли из системы!', 'info');
        });
    }
    
    // Функция показа модального окна с выбором сервисов
    function showSystemModal() {
        if (loginModal) {
            // Обновляем содержимое модального окна для системы бронирования
            const modalContent = loginModal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.innerHTML = `
                    <span class="close-modal" id="closeModal">&times;</span>
                    <h2>📚 Онлайн-сервисы библиотеки</h2>
                    
                    <div class="service-options" style="display: flex; flex-direction: column; gap: 15px; margin: 25px 0;">
                        <a href="${LIBRARY_SYSTEM_URL}/library_system.php" 
                           target="_blank"
                           style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 18px; border-radius: 12px; text-decoration: none; text-align: center; font-weight: bold; font-size: 1.1em; border: none; cursor: pointer;">
                            <i class="fas fa-laptop"></i> Система бронирования
                        </a>
                        
                        <a href="${LIBRARY_SYSTEM_URL}/books.php" 
                           target="_blank"
                           style="background: #28a745; color: white; padding: 18px; border-radius: 12px; text-decoration: none; text-align: center; font-weight: bold; font-size: 1.1em;">
                            <i class="fas fa-book"></i> Каталог книг
                        </a>
                        
                        <a href="${LIBRARY_SYSTEM_URL}/events.php" 
                           target="_blank"
                           style="background: #ffc107; color: black; padding: 18px; border-radius: 12px; text-decoration: none; text-align: center; font-weight: bold; font-size: 1.1em;">
                            <i class="fas fa-calendar-alt"></i> Мероприятия
                        </a>
                        
                        <a href="${LIBRARY_SYSTEM_URL}/news.php" 
                           target="_blank"
                           style="background: #17a2b8; color: white; padding: 18px; border-radius: 12px; text-decoration: none; text-align: center; font-weight: bold; font-size: 1.1em;">
                            <i class="fas fa-newspaper"></i> Новости системы
                        </a>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                        <p style="font-size: 0.9em; color: #666; line-height: 1.5;">
                            <strong>Все сервисы открываются в новой вкладке</strong><br>
                            Для бронирования книг и записи на мероприятия требуется регистрация
                        </p>
                    </div>
                `;
                
                // Обновляем обработчик закрытия
                const newCloseModal = modalContent.querySelector('#closeModal');
                if (newCloseModal) {
                    newCloseModal.addEventListener('click', function() {
                        loginModal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    });
                }
            }
            
            loginModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }
    
    // Функция для синхронизации входа (вызывается из PHP системы)
    window.syncLibraryLogin = function(userData) {
        localStorage.setItem('library_user', JSON.stringify(userData));
        updateUserInterface(userData);
        showNotification(`Добро пожаловать, ${userData.name || userData.username}!`, 'success');
    };
    
    // Функция для синхронизации выхода
    window.syncLibraryLogout = function() {
        localStorage.removeItem('library_user');
        const userPanel = document.getElementById('userPanel');
        const adminLink = document.getElementById('adminLink');
        
        if (userPanel) userPanel.style.display = 'none';
        if (adminLink) adminLink.style.display = 'none';
        
        if (loginBtn) {
            loginBtn.innerHTML = `<i class="fas fa-user"></i>Онлайн-система`;
            loginBtn.style.background = '';
        }
    };
    
    // Функция показа уведомлений
    function showNotification(message, type) {
        // Создаем стилизованное уведомление
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // Стили для уведомления
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-family: inherit;
        `;
        
        // Цвета в зависимости от типа
        if (type === 'success') {
            notification.style.background = 'linear-gradient(135deg, #2ecc71, #27ae60)';
        } else if (type === 'error') {
            notification.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
        } else {
            notification.style.background = 'linear-gradient(135deg, #3498db, #2980b9)';
        }
        
        document.body.appendChild(notification);
        
        // Анимация появления
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Автоматическое скрытие через 4 секунды
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }
    
    // Добавляем кнопку быстрого доступа в футер или шапку
    function addQuickAccessButton() {
        const quickAccess = document.createElement('div');
        quickAccess.innerHTML = `
            <a href="${LIBRARY_SYSTEM_URL}/library_system.php" 
               target="_blank"
               style="position: fixed; bottom: 20px; right: 20px; background: #28a745; color: white; padding: 15px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 9999; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-book"></i>
                <span>Бронировать</span>
            </a>
        `;
        document.body.appendChild(quickAccess);
    }
    
    // Добавляем кнопку быстрого доступа
    addQuickAccessButton();
});