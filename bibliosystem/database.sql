CREATE DATABASE IF NOT EXISTS bibliosystem CHARACTER SET utf8 COLLATE utf8_general_ci;
USE bibliosystem;

-- Пользователи
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    full_name VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Книги
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    description TEXT,
    isbn VARCHAR(20),
    year_published INT,
    quantity INT DEFAULT 1,
    available INT DEFAULT 1,
    cover_image VARCHAR(255),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Мероприятия
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(255),
    max_participants INT,
    current_participants INT DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Новости
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_published BOOLEAN DEFAULT TRUE
);

-- Бронирование книг
CREATE TABLE book_loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('active', 'returned', 'overdue') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Регистрация на мероприятия
CREATE TABLE event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Вставка тестового администратора (пароль: admin123)
INSERT INTO users (username, email, password, role, full_name) 
VALUES ('admin', 'admin@bibliosystem.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Администратор Системы');

-- Вставка тестовых книг
INSERT INTO books (title, author, description, year_published, quantity, available, category) VALUES
('Мастер и Маргарита', 'Михаил Булгаков', 'Роман о дьяволе, посетившем Москву 1930-х годов', 1967, 3, 3, 'Классика'),
('Преступление и наказание', 'Федор Достоевский', 'Психологический роман о студенте Раскольникове', 1866, 2, 2, 'Классика'),
('1984', 'Джордж Оруэлл', 'Антиутопия о тоталитарном обществе', 1949, 4, 4, 'Научная фантастика'),
('Война и мир', 'Лев Толстой', 'Эпопея о войне 1812 года', 1869, 2, 2, 'Классика'),
('Гарри Поттер и философский камень', 'Джоан Роулинг', 'Первая книга о юном волшебнике', 1997, 5, 5, 'Фэнтези');

-- Вставка тестовых мероприятий
INSERT INTO events (title, description, event_date, location, max_participants) VALUES
('Литературный вечер "Поэзия Серебряного века"', 'Вечер поэзии с чтением стихов и обсуждением творчества поэтов Серебряного века', DATE_ADD(NOW(), INTERVAL 7 DAY), 'Читальный зал', 30),
('Мастер-класс по скорочтению', 'Практический мастер-класс по техникам скорочтения для студентов и школьников', DATE_ADD(NOW(), INTERVAL 3 DAY), 'Конференц-зал', 20),
('Встреча с современными авторами', 'Дискуссия с местными писателями о современной литературе', DATE_ADD(NOW(), INTERVAL 14 DAY), 'Актовый зал', 50);

-- Вставка тестовых новостей
INSERT INTO news (title, content, is_published) VALUES
('Открытие нового читального зала', 'Мы рады сообщить об открытии нового современного читального зала, оборудованного по последнему слову техники. Теперь у наших читателей есть еще больше комфортного пространства для работы и учебы.', TRUE),
('Пополнение фонда научной литературы', 'Наша библиотека значительно пополнила фонд научной литературы. Поступило более 500 новых изданий по различным отраслям науки.', TRUE),
('Конкурс "Лучший читатель года"', 'Объявляем старт ежегодного конкурса "Лучший читатель года". Участвуйте и побеждайте! Подробности на сайте и у администраторов.', TRUE);