// Улучшенная система авторизации с Node.js бекендом
document.addEventListener('DOMContentLoaded', function() {
    const loginBtn = document.getElementById('loginBtn');
    const loginModal = document.getElementById('loginModal');
    const closeModal = document.getElementById('closeModal');
    const loginForm = document.getElementById('loginForm');
    
    // API endpoints
    const API_BASE = '/api/auth';
    
    // Проверить авторизацию при загрузке
    checkAuthStatus();
    
    // Открытие модального окна
    loginBtn.addEventListener('click', function(e) {
        e.preventDefault();
        loginModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    });
    
    // Закрытие модального окна
    closeModal.addEventListener('click', function() {
        loginModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
    
    // Закрытие при клике вне модального окна
    window.addEventListener('click', function(e) {
        if (e.target === loginModal) {
            loginModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Обработка формы входа
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        try {
            const response = await fetch(`${API_BASE}/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username, password })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Сохраняем токен
                localStorage.setItem('token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));
                
                // Закрываем модальное окно
                loginModal.style.display = 'none';
                document.body.style.overflow = 'auto';
                
                // Обновляем интерфейс
                updateUserInterface(data.user);
                
                // Показываем уведомление
                showNotification(`Добро пожаловать, ${data.user.name}!`, 'success');
                
                // Если админ, показываем ссылку на админ-панель
                if (data.user.role === 'admin') {
                    document.getElementById('adminLink').style.display = 'inline-block';
                }
            } else {
                showNotification(data.error || 'Ошибка авторизации!', 'error');
            }
        } catch (error) {
            console.error('Ошибка:', error);
            showNotification('Ошибка соединения с сервером', 'error');
        }
    });
    
    // Функция проверки статуса авторизации
    async function checkAuthStatus() {
        const token = localStorage.getItem('token');
        const user = localStorage.getItem('user');
        
        if (token && user) {
            try {
                const response = await fetch(`${API_BASE}/verify`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    updateUserInterface(data.user);
                    if (data.user.role === 'admin') {
                        document.getElementById('adminLink').style.display = 'inline-block';
                    }
                } else {
                    // Токен невалидный, удаляем
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                }
            } catch (error) {
                console.error('Ошибка проверки авторизации:', error);
                // В случае ошибки все равно показываем пользователя из localStorage
                const userData = JSON.parse(user);
                updateUserInterface(userData);
            }
        }
    }
    
    // Функция обновления интерфейса после входа
    function updateUserInterface(user) {
        const userPanel = document.getElementById('userPanel');
        const userName = document.getElementById('userName');
        const userRole = document.getElementById('userRole');
        const adminLink = document.getElementById('adminLink');
        
        if (userPanel && userName && userRole) {
            userName.textContent = user.name;
            userRole.textContent = user.role === 'admin' ? 'Администратор' : 'Пользователь';
            userPanel.style.display = 'block';
            
            // Обновляем кнопку входа
            loginBtn.innerHTML = `<i class="fas fa-user"></i>${user.name}`;
            
            // Показываем админ-ссылку если нужно
            if (user.role === 'admin') {
                adminLink.style.display = 'inline-block';
            }
        }
    }
    
    // Обработчик выхода
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            
            const userPanel = document.getElementById('userPanel');
            const adminLink = document.getElementById('adminLink');
            
            if (userPanel) userPanel.style.display = 'none';
            if (adminLink) adminLink.style.display = 'none';
            
            loginBtn.innerHTML = `<i class="fas fa-user"></i>Личный кабинет`;
            showNotification('Вы успешно вышли из системы!', 'info');
        });
    }
    
    // Функция показа уведомлений
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            z-index: 3000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
        `;
        
        if (type === 'success') {
            notification.style.background = 'linear-gradient(90deg, #2ecc71, #27ae60)';
        } else if (type === 'error') {
            notification.style.background = 'linear-gradient(90deg, #e74c3c, #c0392b)';
        } else {
            notification.style.background = 'linear-gradient(90deg, #3498db, #2980b9)';
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
});