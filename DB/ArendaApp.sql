#DROP DATABASE ArendaApp;
CREATE DATABASE IF NOT EXISTS ArendaApp;

USE ArendaApp;

#RENAME TABLE old_name to new_name;
#ALTER TABLE table_name RENAME COLUMN old_col_name TO new_col_name;

set session group_concat_max_len = 8000;

CREATE TABLE categories(
	idCategory INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    iconUri VARCHAR(300) NOT NULL
);

CREATE TABLE subcategories(
	idSubcategory INT PRIMARY KEY AUTO_INCREMENT,
	idCategory INT NOT NULL,
	FOREIGN KEY (idCategory) REFERENCES categories(idCategory) ON UPDATE CASCADE ON DELETE CASCADE,
	
    name VARCHAR(70) NOT NULL
);

CREATE TABLE users(
	idUser BIGINT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    token VARCHAR(70) NOT NULL,
    name VARCHAR(70) NOT NULL,
	lastName TEXT NOT NULL,
    
	login VARCHAR(80) NOT NULL,
	email VARCHAR(100) NOT NULL,
	password VARCHAR(70) NOT NULL,
    
    userLogo TEXT,
    
    address_1 VARCHAR(100),
    address_2 VARCHAR(100),
    address_3 VARCHAR(100),
    
	phone_1 VARCHAR(35) NOT NULL,
	phone_2 VARCHAR(35),
    phone_3 VARCHAR(35),
    
	accountType VARCHAR(20) NOT NULL DEFAULT "PRIVATE_PERSON",
	balance FLOAT NOT NULL DEFAULT 0.0,
	rating FLOAT NOT NULL DEFAULT 0.0,
    
    statusUser VARCHAR(50) NOT NULL DEFAULT "ACTIVE",
	statusConfirmationEmail BOOL NOT NULL DEFAULT FALSE,
    
    countAnnouncementsUser BIGINT NOT NULL DEFAULT 0,
    countAllViewers BIGINT NOT NULL DEFAULT 0,
    
    countFollowers BIGINT NOT NULL DEFAULT 0,
    countFollowing BIGINT NOT NULL DEFAULT 0,
    
    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX email ON users(email, password);

CREATE TABLE announcements(
	idAnnouncement BIGINT PRIMARY KEY AUTO_INCREMENT,
	idUser BIGINT NOT NULL,
	FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
	idSubcategory INT NOT NULL,
	FOREIGN KEY (idSubcategory) REFERENCES subcategories(idSubcategory) ON UPDATE CASCADE ON DELETE CASCADE,
	
    name TEXT NOT NULL,
	description TEXT NOT NULL,
    
    hourlyCost FLOAT NOT NULL DEFAULT 0.0,
    hourlyCurrency VARCHAR(20) NOT NULL DEFAULT 'USD',
    
    dailyCost FLOAT NOT NULL DEFAULT 0.0,
    dailyCurrency VARCHAR(20) NOT NULL DEFAULT 'USD',
    
    minTime INT NOT NULL DEFAULT 1,
    minDay INT NOT NULL DEFAULT 1,
    maxRentalPeriod INT NOT NULL DEFAULT 1,
    
    timeOfIssueWith TIME NOT NULL DEFAULT '00:00:00',
    timeOfIssueBy TIME NOT NULL DEFAULT '23:00:00',
    
    returnTimeWith TIME NOT NULL DEFAULT '00:00:00',
    returnTimeBy TIME NOT NULL DEFAULT '23:00:00',
    
    address VARCHAR(100) NOT NULL,
    
    phone_1 VARCHAR(25),
	phone_2 VARCHAR(25),
    phone_3 VARCHAR(25),
    
    withSale BOOLEAN NOT NULL DEFAULT FALSE,
    
	statusControl VARCHAR(20) NOT NULL DEFAULT "MODERATION",
	statusRent BOOLEAN NOT NULL DEFAULT FALSE,
    
	rating FLOAT NOT NULL DEFAULT 0.0,

	profit FLOAT NOT NULL DEFAULT 0.0,
	countRent INT NOT NULL DEFAULT 0,
    countViewers INT NOT NULL DEFAULT 0,
    countReviews INT NOT NULL DEFAULT 0,
    countFavorites INT NOT NULL DEFAULT 0,
    countComments INT NOT NULL DEFAULT 0,

	lifeCicle DATETIME NOT NULL,

    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE viewers(
	idViewer BIGINT PRIMARY KEY AUTO_INCREMENT,
    idUser BIGINT NOT NULL,
    FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
    idAnnouncement BIGINT NOT NULL,
	FOREIGN KEY (idAnnouncement) REFERENCES announcements(idAnnouncement) ON UPDATE CASCADE ON DELETE CASCADE
);

DELIMITER //
create trigger event_after_add_announcement after insert on announcements for each row
	begin
		declare _idUser BIGINT;
        set _idUser = new.idUser;
        
        update users set countAnnouncementsUser = countAnnouncementsUser + 1 where idUser = _idUser;
	end //
DELIMITER ;

DELIMITER //
create trigger event_after_add_viewer after insert on viewers for each row
	begin
		declare nof BIGINT;
        set nof = new.idAnnouncement;
        update announcements set countViewers = countViewers + 1 where idAnnouncement = nof;
        update users set countAllViewers = countAllViewers + 1 where idUser = (select idUser from announcements where idAnnouncement = nof);
	end //
DELIMITER ;

CREATE TABLE reviews(
	idReview BIGINT PRIMARY KEY AUTO_INCREMENT,
    idUser BIGINT NOT NULL,
    FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
    idAnnouncement BIGINT NOT NULL,
	FOREIGN KEY (idAnnouncement) REFERENCES announcements(idAnnouncement) ON UPDATE CASCADE ON DELETE CASCADE,
    
    rating INT NOT NULL DEFAULT 0,
    review VARCHAR(4000) NOT NULL,
    
    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME
);

DELIMITER //
create trigger event_after_add_reviews after insert on reviews for each row
	begin
		declare nof BIGINT;
        set nof = new.idAnnouncement;	
		update announcements set announcements.rating = (select avg(rating) from reviews) where announcements.idAnnouncement = nof;
        update announcements set countReviews = countReviews + 1 where idAnnouncement = nof;
	end //
DELIMITER ;

CREATE TABLE rent(
	idRent BIGINT PRIMARY KEY AUTO_INCREMENT,
	idUser BIGINT NOT NULL,
	FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
	idAnnouncement BIGINT NOT NULL,
	FOREIGN KEY (idAnnouncement) REFERENCES announcements(idAnnouncement) ON UPDATE CASCADE ON DELETE CASCADE,
    
    rentalStart DATETIME NOT NULL,
	rentalEnd DATETIME NOT NULL,
    
    isProposal BOOLEAN DEFAULT TRUE,
    isActive BOOLEAN DEFAULT FALSE,
    isClosed BOOLEAN DEFAULT FALSE,
    
    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DELIMITER //
create trigger event_after_update_rent after update on rent for each row
	begin
		declare nof BIGINT; 
        declare status BOOLEAN;
        declare statusClosed BOOLEAN;
        
        set nof = new.idAnnouncement;
        set status = new.isActive;
        set statusClosed = new.isClosed;
        
        if(status is true) then
			update announcements set countRent = countRent + 1 where idAnnouncement = nof;
			update announcements set statusRent = true where idAnnouncement = nof;
        end if;
        
        if(statusClosed is true) then		
			update announcements set statusRent = false where idAnnouncement = nof;
        end if;
	end //
DELIMITER ;

CREATE TABLE pictures(
	idPicture BIGINT PRIMARY KEY AUTO_INCREMENT,
	idAnnouncement BIGINT NOT NULL,
	FOREIGN KEY (idAnnouncement) REFERENCES announcements(idAnnouncement) ON UPDATE CASCADE ON DELETE CASCADE,
	
    picture VARCHAR(300) NOT NULL,
    isMainPicture BOOLEAN DEFAULT FALSE
);

CREATE TABLE favoriteAnnouncements(
	idFavorite BIGINT PRIMARY KEY AUTO_INCREMENT,
	idUser BIGINT NOT NULL,
	FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
	idAnnouncement BIGINT NOT NULL,
	FOREIGN KEY (idAnnouncement) REFERENCES announcements(idAnnouncement) ON UPDATE CASCADE ON DELETE CASCADE,
    isFavorite BOOLEAN NOT NULL DEFAULT TRUE
);

DELIMITER //
create trigger event_after_add_favoriteAnnouncements after insert on favoriteAnnouncements for each row
	begin
		declare nof BIGINT;
        set nof = new.idAnnouncement;
		update announcements set countFavorites = countFavorites + 1 where idAnnouncement = nof;
	end //
DELIMITER ;

DELIMITER //
create trigger event_after_delete_favoriteAnnouncements after delete on favoriteAnnouncements for each row
	begin
		declare nof BIGINT;
        set nof = old.idAnnouncement;
		update announcements set countFavorites = countFavorites - 1 where idAnnouncement = nof;
	end //
DELIMITER ;

 CREATE TABLE followers(
	idFollower BIGINT PRIMARY KEY AUTO_INCREMENT,
    idUser BIGINT NOT NULL,
	FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,	
    idUserFollower BIGINT NOT NULL,
    FOREIGN KEY (idUserFollower) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE
 );
 
 CREATE UNIQUE INDEX followIndex ON followers(idUser, idUserFollower);
 
DELIMITER //
create trigger event_after_add_followers after insert on followers for each row
	begin
		declare _idUser BIGINT;
		declare _idUserFollower BIGINT;
        
        set _idUser = new.idUser;
        set _idUserFollower = new.idUserFollower;
        
		update users set countFollowers = countFollowers + 1 where idUser = _idUser;
		update users set countFollowing = countFollowing + 1 where idUser = _idUserFollower;
	end //
DELIMITER ;

DELIMITER //
create trigger event_after_delete_followers after delete on followers for each row
	begin
		declare _idUser BIGINT;
		declare _idUserFollower BIGINT;
        
        set _idUser = old.idUser;
        set _idUserFollower = old.idUserFollower;
        
		update users set countFollowers = countFollowers - 1 where idUser = _idUser;
		update users set countFollowing = countFollowing - 1 where idUser = _idUserFollower;
	end //
DELIMITER ;

CREATE TABLE comments(
	idComment BIGINT PRIMARY KEY AUTO_INCREMENT,
    idUser BIGINT NOT NULL,
	FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
    idAnnouncement BIGINT NOT NULL,
	FOREIGN KEY (idAnnouncement) REFERENCES announcements(idAnnouncement) ON UPDATE CASCADE ON DELETE CASCADE,
    countReply INT NOT NULL DEFAULT 0,
    
    comment TEXT
);

DELIMITER //
create trigger event_after_add_comments after insert on comments for each row
	begin		
		declare _idAnnouncement BIGINT;	
        set _idAnnouncement = new.idAnnouncement;	
        
		update announcements set countComments = countComments + 1 where idAnnouncement = _idAnnouncement;
	end //
DELIMITER ;

DELIMITER //
create trigger event_after_delete_comments after delete on comments for each row
	begin		
		declare _idAnnouncement BIGINT;	
        set _idAnnouncement = old.idAnnouncement;	
        
		update announcements set countComments = countComments - 1 where idAnnouncement = _idAnnouncement;
	end //
DELIMITER ;

CREATE TABLE replyToComments(
	idReply BIGINT PRIMARY KEY AUTO_INCREMENT,
    idUser BIGINT NOT NULL,
	FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
    idComment BIGINT NOT NULL,
	FOREIGN KEY (idComment) REFERENCES comments(idComment) ON UPDATE CASCADE ON DELETE CASCADE,
    
    reply TEXT
);

DELIMITER //
create trigger event_after_add_reply after insert on replyToComments for each row
	begin		
		declare _idComment BIGINT;	
        set _idComment = new.idComment;	
        
		update comments set countReply = countReply + 1 where idComment = _idComment;
	end //
DELIMITER ;

DELIMITER //
create trigger event_after_delete_reply after delete on replyToComments for each row
	begin		
		declare _idComment BIGINT;	
        set _idComment = old.idComment;	
        
		update comments set countReply = countReply - 1 where idComment = _idComment;
	end //
DELIMITER ;

CREATE TABLE chats(
	idChat BIGINT PRIMARY KEY AUTO_INCREMENT,
    
    idUser_From BIGINT NOT NULL,
	FOREIGN KEY (idUser_From) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
    idUser_To BIGINT NOT NULL,
	FOREIGN KEY (idUser_To) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
    room VARCHAR(60) NOT NULL,
    
    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE messages(
	idMessage BIGINT PRIMARY KEY AUTO_INCREMENT,
    idChat BIGINT NOT NULL,
	FOREIGN KEY (idChat) REFERENCES chats(idChat) ON UPDATE CASCADE ON DELETE CASCADE,
    
    idUser_From BIGINT NOT NULL,
	FOREIGN KEY (idUser_From) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
    idUser_To BIGINT NOT NULL,
	FOREIGN KEY (idUser_To) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
    
    message TEXT,
    
    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO
	categories
VALUES
	(1, "Недвижимость", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_property.jpg"),
	(2, "Транспорт", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_transport.jpg"),
	(3, "Для детей", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_child.jpg"),
	(4, "Электроника", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_electronics.jpg"),
	(5, "Для дома, сада и мероприятий", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_home.jpg"),
	(6, "Одежда и аксессуары", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_pack.jpg"),
	(7, "Инструмент, спецтехника и ремонт", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_tool.jpg"),
	(8, "Спорт и активный отдых", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_none.jpg"),
	(9, "Хобби", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_none.jpg"),
	(10, "Красота и здоровье", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_none.jpg"),
	(11, "Животные", "http://192.168.43.241/AndroidConnectWithServer/ic_categories/ic_none.jpg");

INSERT INTO
	subcategories
VALUES
	(1, 1, "Квартиры"),
	(2, 1, "Дома"),
	(3, 1, "Комнаты"),
	(4, 1, "Койко-место"),  
	(5, 1, "Офисы"),
	(6, 1, "Коммерческая недвижимость"),
	(7, 1, "Гаражи"),
	(8, 1, "Земельные участки"),
	(9, 1, "Апартаменты"),
	(10, 1, "Коворкинг, антикафе"),
	(11, 1, "Отели"),
	(12, 1, "Базы отдыха"),
    
	(13, 2, "Легковые автомобили"),
	(14, 2, "Электротранспорт"),
	(15, 2, "Грузовые автомобили"),
	(16, 2, "Мото"),
	(17, 2, "Воздушный транспорт"),
	(18, 2, "Сельхозтехника"),
	(19, 2, "Автобусы"),
	(20, 2, "Лодки и аксессуары"),
	(21, 2, "Водный транспорт"),
	(22, 2, "Автодома, кэмперы"),
	(23, 2, "Снегоходы"),
	(24, 2, "Акустика и мультимедиа"),
	(25, 2, "Автоэлектрика"),
	(26, 2, "Противоугонные устройства"),
	(27, 2, "Внешний тюнинг"),
	(28, 2, "Багажники"),
	(29, 2, "Прицепы и полуприцепы"),
	(30, 2, "Автохолодильники"),
	(31, 2, "Другие авто- и мототовары"),
	(32, 2, "Мотоаксессуары"),
	(33, 2, "Автооборудование"),
	(34, 2, "Другой транспорт"),
	(35, 2, "Спецтехника"),
    
	(36, 3, "Детский транспорт"),
	(37, 3, "Детская мебель"),
	(38, 3, "Детские коляски"),
	(39, 3, "Детские весы"),
	(40, 3, "Автокресла"),
	(41, 3, "Игрушки"),
	(42, 3, "Товары для школьников"),
	(43, 3, "Молокоотсосы"),
	(44, 3, "Развивающие коврики"),
	(45, 3, "Эргорюкзаки, слинги, нагрудные сумки"),
	(46, 3, "Товары для кормления"),
	(47, 3, "Видеоняни, радионяни"),
	(48, 3, "Детская одежда и обувь"),
	(49, 3, "Другое"),
    
	(51, 4, "Фото, видео"),
	(52, 4, "Компьютеры и планшеты"),
	(53, 4, "Игры и игровые приставки"),
	(54, 4, "Телефоны"),
	(55, 4, "Рации"),
	(56, 4, "Дроны и квадрокоптеры"),
	(57, 4, "ТВ, видеотехника"),
	(58, 4, "Климатическое оборудование"),
	(59, 4, "Электроника для спорта"),
	(60, 4, "Аудиотехника"),
	(61, 4, "Фонари и студийный свет"),
	(62, 4, "Техника для кухни"),
	(63, 4, "Техника для дома"),
	(64, 4, "Индивидуальный уход"),
	(65, 4, "Аксессуары и комплектующие"),
	(66, 4, "Другое"),
	(67, 4, "Вендиговые аппараты"),
    
	(68, 5, "Декор"),
	(69, 5, "Текстиль"),
	(70, 5, "Люстры и светильники"),
	(71, 5, "Гладильные доски и сушилки"),
	(72, 5, "Мебель"),
	(73, 5, "Биотуалеты"),
	(74, 5, "Переносные души и умывальники"),
	(75, 5, "Пруды, фонтаны и аксессуары"),
	(76, 5, "Садовые фигурки"),
	(77, 5, "Садовые светильники"),
	(78, 5, "Принадлежности для барбекю"),
	(79, 5, "Бассейны надувные, каркасные и аксессуары"),
	(80, 5, "Садовые качели и гамаки"),
	(81, 5, "Ультразвуковые отпугиватели"),
	(82, 5, "Мобильные бани и сауны"),
	(83, 5, "Аксессуары для бани"),
	(84, 5, "Другое"),
	(85, 5, "Шатры"),
	(86, 5, "Посуда"),
	(87, 5, "Беседки"),
	(88, 5, "Выставочное оборудование"),
	(89, 5, "Растения"),
    
	(90, 6, "Обувь для охоты и рыбалки"),
	(91, 6, "Одежда и обувь для альпинизма и туризма"),
	(92, 6, "Аксессуары"),
	(93, 6, "Одежда"),
	(94, 6, "Обувь"),
	(95, 6, "Спортивная одежда и аксессуары"),
    
	(96, 7, "Тепловизионное оборудование"),
	(97, 7, "Леса строительные"),
	(98, 7, "Вышки-туры"),
	(99, 7, "Вибротрамбовки"),
	(100, 7, "Подъемники"),
	(101, 7, "Лестницы"),
	(102, 7, "Перфораторы"),
	(103, 7, "Дорожные катки"),
	(104, 7, "Пилы"),
	(105, 7, "Отбойные молотки"),
	(106, 7, "Плиткорезы"),
	(107, 7, "Алмазное сверление"),
	(108, 7, "Генераторы и электростанции"),
	(109, 7, "Сварочные аппараты, станции"),
	(110, 7, "Опалубка"),
	(111, 7, "Нагреватели"),
	(112, 7, "Швонарезчики"),
	(113, 7, "Уборка и клининг"),
	(114, 7, "Электроинструмент"),
	(115, 7, "Оборудование для дома и сада"),
	(116, 7, "Шлифовальные машины"),
	(117, 7, "Оборудование для проведения бетонных работ"),
	(118, 7, "Компрессоры"),
	(119, 7, "Пневмоинструмент"),
	(120, 7, "Грузоподъемное оборудование"),
	(121, 7, "Пресс-клещи"),
	(122, 7, "Оборудование для праздников"),
	(123, 7, "Осушители"),
	(124, 7, "Ручные инструменты"),
	(125, 7, "Измерительные инструменты и техника"),
	(126, 7, "Другое"),
    
	(127, 8, "Палатки"),
	(128, 8, "Спальные мешки"),
	(129, 8, "Коврики и карематы"),
	(130, 8, "Туристическая посуда"),
	(131, 8, "Снаряжение для туризма и альпинизма"),
	(132, 8, "Бокс и единоборства"),
	(133, 8, "Гимнастика"),
	(134, 8, "Тюбинг"),
	(135, 8, "Горнолыжный спорт"),
	(136, 8, "Сноубординг"),
	(137, 8, "Экипировка для зимнего спорта"),
	(138, 8, "Другие товары для отдыха и спорта"),
	(139, 8, "Хоккей и фигурное катание"),
	(140, 8, "Плавание"),
	(141, 8, "Дайвинг и подводная охота"),
	(142, 8, "Водный спорт"),
	(143, 8, "Аквафитнес"),
	(144, 8, "Велосипеды"),
	(145, 8, "Велосипедная экипировка"),
	(146, 8, "Аксессуары для велосипедов"),
	(147, 8, "Джамперы"),
	(148, 8, "Тренажеры"),
	(149, 8, "Тяжелая атлетика"),
	(150, 8, "Фитнес"),
	(151, 8, "Игры с ракеткой"),
	(152, 8, "Игры с мячом"),
	(153, 8, "Бильярд"),
	(154, 8, "Бейсбол"),
	(155, 8, "Гольф и мини-гольф"),
	(156, 8, "Картинг"),
	(157, 8, "Пейнтбол"),
	(158, 8, "Скейтборды, самокаты, ролики"),
    
	(159, 9, "Снаряжение для рыбалки"),
	(160, 9, "Одежда для охоты и рыбалки"),
	(161, 9, "Снаряжение для охоты"),
	(162, 9, "Оптические приборы"),
	(163, 9, "Книги, журналы"),
	(164, 9, "Музыкальные инструменты"),
	(165, 9, "Другое"),
	(166, 9, "Тренировочное оружие"),
	(167, 9, "Аттракционы и игры"),
	(168, 9, "Металлоискатели и металлодетекторы"),
	(169, 9, "Курительные принадлежности"),
    
	(170, 10, "Красота"),
	(171, 10, "Медицина, здоровье"),
    
	(172, 11, "Грызуны"),
	(173, 11, "Кошки"),
	(174, 11, "Сельхоз животные"),
	(175, 11, "Аквариумистка"),
	(176, 11, "Рептилии"),
	(177, 11, "Птицы"),
	(178, 11, "Другое"),
	(179, 11, "Насекомые"),
	(180, 11, "Зоотовары"),
	(181, 11, "Собаки"),
    
    (182, 1, "Другое"),
    (183, 2, "Другое"),
    (184, 6, "Другое"),
    (185, 10, "Другое");

-- INSERT INTO users(token, name, lastName, login, email, password, phone_1) VALUES
-- 	("token", "name1", "lastName1", "login1", "email1", "password1", "phone_1"),
-- 	("token", "name2", "lastName2", "login2", "email2", "password2", "phone_1"),
-- 	("token", "name3", "lastName3", "login3", "email3", "password3", "phone_1"),
-- 	("token", "name4", "lastName4", "login4", "email4", "password4", "phone_1");

-- INSERT INTO announcements(idUser, idSubcategory, name, description, hourlyCost, profit,
-- 	address, phone_1, phone_2, phone_3, created, lifeCicle) VALUES
--     (4, 1, "name 1", "desc 1", 
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (3, 2, "name 2", "desc 2",
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (1, 1, "name 3", "desc 3", 
--     2.2, 0, "address 1",  
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (2, 2, "name 4", "desc 4",  
--     2.2, 0, "address 1",  
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (3, 3, "name 5", "desc 5",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (1, 5, "name 6", "desc 6",  
--     2.2, 0, "address 1",  
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (1, 1, "name 7", "desc 7",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (1, 2, "name 8", "desc 8",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (4, 5, "name 9", "desc 9",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (1, 1, "name 10", "desc 10",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (1, 2, "name 11", "desc 11",
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (3, 4, "name 12", "desc 12",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (1, 1, "name 13", "desc 13",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (1, 3, "name 14", "desc 14",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (1, 4, "name 15", "desc 15",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (2, 1, "name 16", "desc 16",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (1, 5, "name 17", "desc 17",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (3, 5, "name 18", "desc 18",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (4, 2, "name 19", "desc 19",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now()),
--     (2, 1, "name 20", "desc 20",  
--     2.2, 0, "address 1", 
--     "+375(29)111-11-11", "+375(29)111-11-11", "+375(29)111-11-11", now(), now());
--     
-- INSERT INTO pictures(idAnnouncement, picture, isMainPicture) VALUES
-- 	(1, "https://images.unsplash.com/photo-1458668383970-8ddd3927deed?ixlib=rb-1.2.1&auto=format&fit=crop&w=747&q=80", true),
-- 	(2, "https://images.unsplash.com/photo-1480497490787-505ec076689f?ixlib=rb-1.2.1&auto=format&fit=crop&w=749&q=80", true),
-- 	(3, "https://images.unsplash.com/photo-1473654729523-203e25dfda10?ixlib=rb-1.2.1&auto=format&fit=crop&w=750&q=80",  true),
-- 	(4, "https://images.unsplash.com/photo-1451337516015-6b6e9a44a8a3?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEwMjc5NH0&auto=format&fit=crop&w=667&q=80", true),
-- 	(5, "https://images.unsplash.com/photo-1505312238910-67e64a4ec582?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80", true),
-- 	(6, "https://images.unsplash.com/photo-1444076784383-69ff7bae1b0a?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=750&q=80", true),
-- 	(7, "https://images.unsplash.com/photo-1480497490787-505ec076689f?ixlib=rb-1.2.1&auto=format&fit=crop&w=749&q=80", true),
-- 	(8, "https://images.unsplash.com/photo-1480497490787-505ec076689f?ixlib=rb-1.2.1&auto=format&fit=crop&w=749&q=80", true),
-- 	(9, "https://images.unsplash.com/photo-1461301214746-1e109215d6d3?ixlib=rb-1.2.1&auto=format&fit=crop&w=750&q=80", true),
-- 	(10, "https://images.unsplash.com/photo-1503197979108-c824168d51a8?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjExMjU4fQ&auto=format&fit=crop&w=500&q=60", true),
-- 	(11, "https://images.unsplash.com/photo-1441794016917-7b6933969960?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjExMDk0fQ&auto=format&fit=crop&w=500&q=60", true),
-- 	(12, "https://images.unsplash.com/photo-1437750769465-301382cdf094?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60", true),
-- 	(13, "https://images.unsplash.com/photo-1480497490787-505ec076689f?ixlib=rb-1.2.1&auto=format&fit=crop&w=749&q=80", true),
-- 	(14, "https://images.unsplash.com/photo-1462733441571-9312d0b53818?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", true),
-- 	(15, "https://images.unsplash.com/photo-1460400408855-36abd76648b9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", true),
-- 	(16, "https://images.unsplash.com/photo-1572357280636-1a2c2c26acdc?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60", true),
-- 	(17, "https://images.unsplash.com/photo-1546552916-985b466ffbec?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", true),
-- 	(18, "https://images.unsplash.com/photo-1524222835726-8e7d453fa83c?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", true),
-- 	(19, "https://images.unsplash.com/photo-1543362906-acfc16c67564?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", true),
-- 	(20, "https://images.unsplash.com/photo-1550411294-b3b1bd5fce1b?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", true),
--     (1, "https://images.unsplash.com/photo-1458668383970-8ddd3927deed?ixlib=rb-1.2.1&auto=format&fit=crop&w=747&q=80", false),
-- 	(2, "https://images.unsplash.com/photo-1480497490787-505ec076689f?ixlib=rb-1.2.1&auto=format&fit=crop&w=749&q=80", false),
-- 	(3, "https://images.unsplash.com/photo-1473654729523-203e25dfda10?ixlib=rb-1.2.1&auto=format&fit=crop&w=750&q=80",  false),
-- 	(4, "https://images.unsplash.com/photo-1451337516015-6b6e9a44a8a3?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEwMjc5NH0&auto=format&fit=crop&w=667&q=80", false),
-- 	(5, "https://images.unsplash.com/photo-1505312238910-67e64a4ec582?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80", false),
-- 	(6, "https://images.unsplash.com/photo-1444076784383-69ff7bae1b0a?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=750&q=80", false),
-- 	(7, "https://images.unsplash.com/photo-1480497490787-505ec076689f?ixlib=rb-1.2.1&auto=format&fit=crop&w=749&q=80", false),
-- 	(8, "https://images.unsplash.com/photo-1480497490787-505ec076689f?ixlib=rb-1.2.1&auto=format&fit=crop&w=749&q=80", false),
-- 	(9, "https://images.unsplash.com/photo-1461301214746-1e109215d6d3?ixlib=rb-1.2.1&auto=format&fit=crop&w=750&q=80", false),
-- 	(10, "https://images.unsplash.com/photo-1503197979108-c824168d51a8?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjExMjU4fQ&auto=format&fit=crop&w=500&q=60", false),
-- 	(11, "https://images.unsplash.com/photo-1441794016917-7b6933969960?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjExMDk0fQ&auto=format&fit=crop&w=500&q=60", false),
-- 	(12, "https://images.unsplash.com/photo-1437750769465-301382cdf094?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60", false),
-- 	(13, "https://images.unsplash.com/photo-1480497490787-505ec076689f?ixlib=rb-1.2.1&auto=format&fit=crop&w=749&q=80", false),
-- 	(14, "https://images.unsplash.com/photo-1462733441571-9312d0b53818?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", false),
-- 	(15, "https://images.unsplash.com/photo-1460400408855-36abd76648b9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", false),
-- 	(16, "https://images.unsplash.com/photo-1572357280636-1a2c2c26acdc?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60", false),
-- 	(17, "https://images.unsplash.com/photo-1546552916-985b466ffbec?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", false),
-- 	(18, "https://images.unsplash.com/photo-1524222835726-8e7d453fa83c?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", false),
-- 	(19, "https://images.unsplash.com/photo-1543362906-acfc16c67564?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", false),
-- 	(20, "https://images.unsplash.com/photo-1550411294-b3b1bd5fce1b?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60", false);
--     
    
-- INSERT INTO rent(idUser, idAnnouncement, rentalStart, rentalEnd) VALUES
-- 	(3, 20, "2020-05-05 15:00:00", "2020-05-06 16:00:00"),
-- 	(3, 20, "2020-05-06 15:00:00", "2020-05-07 16:00:00"),
-- 	(4, 19, "2020-05-07 15:00:00", "2020-05-08 16:00:00"),
-- 	(2, 19, "2020-05-08 15:00:00", "2020-05-09 16:00:00"),
-- 	(2, 18, "2020-05-09 15:00:00", "2020-05-10 16:00:00"),
-- 	(4, 15, "2020-05-10 15:00:00", "2020-05-11 16:00:00"),
-- 	(3, 16, "2020-05-11 15:00:00", "2020-05-12 16:00:00"),
-- 	(2, 18, "2020-05-12 15:00:00", "2020-05-13 16:00:00"),
--     
-- 	(3, 20, "2020-06-01 15:00:00", "2020-06-02 16:00:00"),
-- 	(4, 17, "2020-06-02 15:00:00", "2020-06-03 16:00:00"),
-- 	(4, 13, "2020-06-03 15:00:00", "2020-06-04 16:00:00"),
-- 	(3, 11, "2020-06-04 15:00:00", "2020-06-05 16:00:00"),
-- 	(4, 13, "2020-06-05 15:00:00", "2020-06-06 16:00:00");
    
-- UPDATE rent SET isProposals = false WHERE idUser = 2;
-- UPDATE rent SET isProposals = false WHERE idUser = 3;
-- UPDATE rent SET isProposals = false WHERE idUser = 4;
    
    insert into users (token, name, lastName, login, email, password, phone_1, address_1, userLogo) values
    ("token_1", "Артем", "Кошарнов", "arutomu", "artem@gmail.com", "123456", "+375(44)981-77-09", "Minsk", 
		"https://sun9-18.userapi.com/c847018/v847018927/131fbf/WamFWrClUH8.jpg"),
	("token_2", "Лера", "Шкундич", "lerka", "lerka@gmail.com", "123456", "+375(44)798-97-19", "Minsk", 
		"https://sun2.cosmostv-by-minsk.userapi.com/36ZTgdA6fQZV96rj8icTXHSAcsdghvQ-XEhqdw/OPVnaR6fOsA.jpg"),
	("token_3", "Даник", "Мозолевский", "danya", "danya@gmail.com", "123456", "+375(44)109-07-59", "Minsk", 
		"https://sun9-16.userapi.com/c847124/v847124365/83bdd/kPdJ2UmAheQ.jpg"),
	("token_4", "Никита", "Девочко", "nikitos", "nikitos@gmail.com", "123456", "+375(29)631-90-43", "Minsk", 
		"https://sun9-41.userapi.com/c857732/v857732396/4f0c2/ci-6DN0Q7og.jpg");
    
    INSERT INTO announcements(idUser, idSubcategory, name, description, hourlyCost, address, phone_1, lifeCicle) VALUES
    (1, 13, "Nissan Skyline GT-R", 
"В отличном техническом и косметическом состоянии! Подробности по телефону!

Двигатель:
Поршневая Mahle
Шатуны Eagle
Вкладыши ACL
Валы Tomei
Выпускной коллектор Shabanov brother
Выхлоп Blitz spec nur
Турбина Garret GTX gen2 3576
Вестгейт Tial
Радиатор Koyorad
Форсунки injector dynamics 1000

МКПП Getrag6
сцепление HKS двухдисковая органика
Топливная система:
Топливные насосы Walbro 540 2 штуки
Топливная рейка Radium
Топливный регулятор Radium
Топливная трасса AN10

Ходовая часть:
Все рычаги Cusco, кроме нижних передних (оригинал GTR)
Стойки оригинал GTR, в придачу Cusco

Мозги Link Fury, ШЛЗ, Flexfuel sensor", 10.5, "", "+375(44)981-77-09", now()),
	(2, 2, "Коттеджи в 'Робинсон Клуб'", "Робинсон Клуб предлагает новый формат отдыха для своих гостей - VIP отдых в новых коттеджах 'Равенна' и 'Модена'. 
    Это идеальный вариант для тех, кто привык получать всё и сразу! 
    Два зеркальных коттеджа премиум-класса спроектированы таким образом, 
    чтобы гости получили максимум удовольствия от проживания или проведения мероприятий. 
    Кроме того, гости могут снять как коттедж целиком ( и остаться переночевать), 
    так и отдельно его части: банкетный зал или застекленную террасу.", 18.75, "побережье Минского моря", "+375(44)798-97-19", now()),
    (3, 52, "Шлем виртуальной реальности HTC Vive", "Шлем виртуальной реальности для компьютера. Предназначен для игр, приложений, просмотра фотографий и фильмов в 3D. 
    Экран с разрешением 2160х1200. Частота обновления кадров 90 Гц. Угол обзора 110°. Датчики: акселерометр, гироскоп, магнитометр. 
    Интерфейсы подключения: порт USB 2.0, HDMI 1.4 или DisplayPort 1.2.", 2.5, "", "+375(44)109-07-59", now()),
    (4, 16, "Honda VFR800 VTEC", "Год выпуска - 2004
Объем двигателя – 800 куб.см
Тип мотоцикла - спорт-турист
Тип двигателя - 4-цилиндровый, 4-тактный, V-образный
Мощность двигателя – 106 л.с.
Контроль топлива – Инжектор
Коробка передач – 6-ступенчатая
Тип привода – Цепь
Максимальная скорость – 230 км/ч", 7.6, "", "+375(29)631-90-43", now());
    

INSERT INTO pictures(idAnnouncement, picture, isMainPicture) VALUES
	(1, "https://s.auto.drom.ru/i24246/s/photos/38391/38390592/gen600_520881416.jpg", true),
	(1, "https://s.auto.drom.ru/i24246/s/photos/38391/38390592/gen600_520880216.jpg", false),
	(1, "https://s.auto.drom.ru/i24246/s/photos/38391/38390592/gen600_520880347.jpg", false),
	(1, "https://s.auto.drom.ru/i24246/s/photos/38391/38390592/gen600_520880533.jpg", false),
	(1, "https://s.auto.drom.ru/i24246/s/photos/38391/38390592/gen600_520880748.jpg", false),
	(1, "https://s.auto.drom.ru/i24246/s/photos/38391/38390592/gen600_520880596.jpg", false),
	(1, "https://s.auto.drom.ru/i24246/s/photos/38391/38390592/gen600_520880638.jpg", false),
    
	(2, "https://static.realt.by/user/j8/v/r2002e3w5vj8/1a5f58c185.jpg?1498478797", true),
	(2, "https://static.realt.by/user/j8/v/r2002e3w5vj8/7758f2b181.jpg?1498478868", false),
	(2, "https://static.realt.by/user/j8/v/r2002e3w5vj8/8757cdb8bf.jpg?1498478870", false),
	(2, "https://static.realt.by/user/j8/v/r2002e3w5vj8/2e443ebb66.jpg?1498478871", false),
	(2, "https://static.realt.by/user/j8/v/r2002e3w5vj8/086ef84a84.jpg?1498478873", false),
	(2, "https://static.realt.by/user/j8/v/r2002e3w5vj8/e9681c2016.jpg?1498478874", false),
	(2, "https://static.realt.by/user/j8/v/r2002e3w5vj8/9d1610a138.jpg?1498478919", false),
	(2, "https://static.realt.by/user/j8/v/r2002e3w5vj8/ae4bce4e45.jpg?1498480188", false),
    
	(3, "https://shop.by/images/htc_vive_1.jpg", true),
	(3, "https://shop.by/images/htc_vive_2.jpg", false),
	(3, "https://shop.by/images/htc_vive_3.jpg", false),
	(3, "https://shop.by/images/htc_vive_5.jpg", false),
	(3, "https://shop.by/images/htc_vive_4.jpg", false),
    
	(4, "https://static.tildacdn.com/tild3037-6639-4339-a561-633930613262/6.jpg", true),
	(4, "https://static.tildacdn.com/tild3231-6437-4566-b239-666266626666/7.jpg", false),
	(4, "https://static.tildacdn.com/tild3765-3631-4263-b966-303033326331/2.jpg", false),
	(4, "https://static.tildacdn.com/tild3137-3632-4730-a436-376139356661/3.jpg", false);
    
	
    
    
    insert into followers(idUser, idUserFollower) values
    (1, 2),
    (1, 3),
    (4, 2),
    (3, 4),
    (1, 4);
    
    
    
    
    