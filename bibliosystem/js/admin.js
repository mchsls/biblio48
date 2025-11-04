class AdminPanel {
    constructor() {
        this.token = localStorage.getItem('adminToken');
        this.init();
    }

    init() {
        this.checkAuth();
        this.setupEventListeners();
        this.loadData();
    }

    checkAuth() {
        if (!this.token) {
            window.location.href = '/admin-login.html';
            return;
        }
    }

    setupEventListeners() {
        // Кнопка сохранения новости
        const saveNewsBtn = document.getElementById('saveNews');
        if (saveNewsBtn) {
            saveNewsBtn.addEventListener('click', () => this.saveNews());
        }

        // Кнопка сохранения события
        const saveEventBtn = document.getElementById('saveEvent');
        if (saveEventBtn) {
            saveEventBtn.addEventListener('click', () => this.saveEvent());
        }

        // Кнопка выхода
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.logout());
        }
    }

    async saveNews() {
        const title = document.getElementById('newsTitle').value;
        const content = document.getElementById('newsContent').value;
        const date = document.getElementById('newsDate').value;
        const imageFile = document.getElementById('newsImage').files[0];

        if (!title || !content || !date) {
            this.showMessage('Заполните все обязательные поля', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);
            formData.append('date', date);
            if (imageFile) {
                formData.append('image', imageFile);
            }

            const response = await fetch('/api/news', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`
                },
                body: formData
            });

            if (response.ok) {
                this.showMessage('Новость успешно сохранена!', 'success');
                this.clearNewsForm();
                this.loadNews();
            } else {
                const error = await response.json();
                this.showMessage(`Ошибка: ${error.error}`, 'error');
            }
        } catch (error) {
            this.showMessage('Ошибка соединения с сервером', 'error');
        }
    }

    async saveEvent() {
        const title = document.getElementById('eventTitle').value;
        const description = document.getElementById('eventDescription').value;
        const date = document.getElementById('eventDate').value;
        const time = document.getElementById('eventTime').value;
        const location = document.getElementById('eventLocation').value;
        const ageGroup = document.getElementById('eventAgeGroup').value;
        const category = document.getElementById('eventCategory').value;
        const imageFile = document.getElementById('eventImage').files[0];

        if (!title || !description || !date || !time || !location) {
            this.showMessage('Заполните все обязательные поля', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('description', description);
            formData.append('date', date);
            formData.append('time', time);
            formData.append('location', location);
            formData.append('age_group', ageGroup);
            formData.append('category', category);
            if (imageFile) {
                formData.append('image', imageFile);
            }

            const response = await fetch('/api/events', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`
                },
                body: formData
            });

            if (response.ok) {
                this.showMessage('Событие успешно сохранено!', 'success');
                this.clearEventForm();
                this.loadEvents();
            } else {
                const error = await response.json();
                this.showMessage(`Ошибка: ${error.error}`, 'error');
            }
        } catch (error) {
            this.showMessage('Ошибка соединения с сервером', 'error');
        }
    }

    async loadData() {
        await this.loadNews();
        await this.loadEvents();
    }

    async loadNews() {
        try {
            const response = await fetch('/api/news');
            if (response.ok) {
                const news = await response.json();
                this.displayNews(news);
            }
        } catch (error) {
            console.error('Ошибка загрузки новостей:', error);
        }
    }

    async loadEvents() {
        try {
            const response = await fetch('/api/events');
            if (response.ok) {
                const events = await response.json();
                this.displayEvents(events);
            }
        } catch (error) {
            console.error('Ошибка загрузки событий:', error);
        }
    }

    displayNews(news) {
        const container = document.getElementById('newsList');
        if (!container) return;

        container.innerHTML = news.map(item => `
            <div class="news-item">
                <h4>${item.title}</h4>
                <p>${item.content.substring(0, 100)}...</p>
                <small>${new Date(item.date).toLocaleDateString('ru-RU')}</small>
            </div>
        `).join('');
    }

    displayEvents(events) {
        const container = document.getElementById('eventsList');
        if (!container) return;

        container.innerHTML = events.map(item => `
            <div class="event-item">
                <h4>${item.title}</h4>
                <p>${item.description.substring(0, 100)}...</p>
                <small>${new Date(item.date).toLocaleDateString('ru-RU')} в ${item.time}</small>
                <small>Место: ${item.location}</small>
            </div>
        `).join('');
    }

    clearNewsForm() {
        document.getElementById('newsTitle').value = '';
        document.getElementById('newsContent').value = '';
        document.getElementById('newsDate').value = '';
        document.getElementById('newsImage').value = '';
    }

    clearEventForm() {
        document.getElementById('eventTitle').value = '';
        document.getElementById('eventDescription').value = '';
        document.getElementById('eventDate').value = '';
        document.getElementById('eventTime').value = '';
        document.getElementById('eventLocation').value = '';
        document.getElementById('eventAgeGroup').value = '';
        document.getElementById('eventImage').value = '';
    }

    showMessage(message, type) {
        // Создаем или находим контейнер для сообщений
        let messageContainer = document.getElementById('messageContainer');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'messageContainer';
            messageContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                max-width: 300px;
            `;
            document.body.appendChild(messageContainer);
        }

        const messageEl = document.createElement('div');
        messageEl.style.cssText = `
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            color: white;
            background-color: ${type === 'success' ? '#4CAF50' : '#f44336'};
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        `;
        messageEl.textContent = message;

        messageContainer.appendChild(messageEl);

        // Автоматическое удаление через 5 секунд
        setTimeout(() => {
            messageEl.remove();
        }, 5000);
    }

    logout() {
        localStorage.removeItem('adminToken');
        window.location.href = '/';
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    new AdminPanel();
});