DROP DATABASE ArendaApp;
CREATE DATABASE ArendaApp;

USE ArendaApp;

CREATE TABLE categories(
	idCategory INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE subcategories(
	idSubcategory INT PRIMARY KEY AUTO_INCREMENT,
	idCategory INT NOT NULL,
	FOREIGN KEY (idCategory) REFERENCES categories(idCategory) ON UPDATE CASCADE ON DELETE CASCADE,
	
    name VARCHAR(70) NOT NULL
);

CREATE TABLE users(
	idUser INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    token VARCHAR(70) NOT NULL,
	
    name VARCHAR(20) NOT NULL,
	lastName VARCHAR(30) NOT NULL,
	email VARCHAR(60) NOT NULL,
	password VARCHAR(70) NOT NULL,
    userPhoto VARCHAR(300),
    
    address_1 VARCHAR(100),
    address_2 VARCHAR(100),
    address_3 VARCHAR(100),
    
	phone_1 VARCHAR(25) NOT NULL,
	phone_2 VARCHAR(25),
    phone_3 VARCHAR(25),
    
	accountType VARCHAR(20) NOT NULL DEFAULT "person",
	balance INT NOT NULL DEFAULT 0,
	rating FLOAT NOT NULL DEFAULT 0.0,
	statusConfirmationEmail BOOL NOT NULL DEFAULT FALSE
);

CREATE UNIQUE INDEX email ON users(email, password);

CREATE TABLE announcements(
	idAnnouncement INT PRIMARY KEY AUTO_INCREMENT,
	idUser INT NOT NULL,
	FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
	idSubcategory INT NOT NULL,
	FOREIGN KEY (idSubcategory) REFERENCES subcategories(idSubcategory) ON UPDATE CASCADE ON DELETE CASCADE,
	
    name VARCHAR(70) NOT NULL,
	description VARCHAR(4000) NOT NULL,
    
    photoPath VARCHAR(300) NOT NULL,
    
	costToBYN FLOAT NOT NULL DEFAULT 0.0,
	costToUSD FLOAT NOT NULL DEFAULT 0.0,
	costToEUR FLOAT NOT NULL DEFAULT 0.0,
    profit FLOAT NOT NULL DEFAULT 0.0,
    
    address VARCHAR(100) NOT NULL,
    
    phone_1 VARCHAR(25),
    isVisible_phone_1 BOOL NOT NULL DEFAULT FALSE,
    
	phone_2 VARCHAR(25),
    isVisible_phone_2 BOOL NOT NULL DEFAULT FALSE,
    
    phone_3 VARCHAR(25),
    isVisible_phone_3 BOOL NOT NULL DEFAULT FALSE,
    
	statusControl VARCHAR(20) NOT NULL DEFAULT "moderation",
	statusRent BOOLEAN NOT NULL DEFAULT FALSE,
    
    countRent INT NOT NULL DEFAULT 0.0,
    countViewers INT NOT NULL DEFAULT 0,
    countFavorites INT NOT NULL DEFAULT 0,
	rating FLOAT NOT NULL DEFAULT 0.0,

    placementDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	lifeCicle DATETIME NOT NULL
);

CREATE TABLE reviews(
	idReview INT PRIMARY KEY AUTO_INCREMENT,
    idUser INT NOT NULL,
    FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
    idAnnouncement INT NOT NULL,
	FOREIGN KEY (idAnnouncement) REFERENCES announcements(idAnnouncement) ON UPDATE CASCADE ON DELETE CASCADE,
    
    rating INT NOT NULL DEFAULT 0,
    review VARCHAR(1000) NOT NULL
);

DELIMITER //
create trigger event_after_add_reviews after insert on reviews for each row
	begin
		declare nof int;
        set nof = new.idAnnouncement;	
		update announcements set announcements.rating = (select avg(rating) from reviews) where announcements.idAnnouncement = nof;
	end //
DELIMITER ;

CREATE TABLE rent(
	idRent INT PRIMARY KEY AUTO_INCREMENT,
	idUser INT NOT NULL,
	FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
	idAnnouncement INT NOT NULL,
	FOREIGN KEY (idAnnouncement) REFERENCES announcements(idAnnouncement) ON UPDATE CASCADE ON DELETE CASCADE,
	
    rentalStart DATETIME NOT NULL,
	rentalEnd DATETIME NOT NULL
);

DELIMITER //
create trigger event_after_add_rent after insert on rent for each row
	begin
		declare nof int;
        set nof = new.idAnnouncement;
		update announcements set countRent = countRent + 1 where idAnnouncement = nof;
		update announcements set statusRent = true where idAnnouncement = nof;
	end //
DELIMITER ;

CREATE TABLE photo(
	idPhoto INT PRIMARY KEY AUTO_INCREMENT,
	idAnnouncement INT NOT NULL,
	FOREIGN KEY (idAnnouncement) REFERENCES announcements(idAnnouncement) ON UPDATE CASCADE ON DELETE CASCADE,
	
    photoPath VARCHAR(300) NOT NULL
);

CREATE TABLE favoriteAnnouncements(
	idFavorite INT PRIMARY KEY AUTO_INCREMENT,
	idUser INT NOT NULL,
	FOREIGN KEY (idUser) REFERENCES users(idUser) ON UPDATE CASCADE ON DELETE CASCADE,
	idAnnouncement INT NOT NULL,
	FOREIGN KEY (idAnnouncement) REFERENCES announcements(idAnnouncement) ON UPDATE CASCADE ON DELETE CASCADE,
    isFavorite BOOL NOT NULL DEFAULT TRUE
);

DELIMITER //
create trigger event_after_add_favoriteAnnouncements after insert on favoriteAnnouncements for each row
	begin
		declare nof int;
        set nof = new.idAnnouncement;
		update announcements set countFavorites = countFavorites + 1 where idAnnouncement = nof;
	end //
DELIMITER ;

DELIMITER //
create trigger event_after_delete_favoriteAnnouncements after delete on favoriteAnnouncements for each row
	begin
		declare nof int;
        set nof = old.idAnnouncement;
		update announcements set countFavorites = countFavorites - 1 where idAnnouncement = nof;
	end //
DELIMITER ;

INSERT INTO
	categories
VALUES
	(1, "Недвижимость"),
	(2, "Транспорт"),
	(3, "Для детей"),
	(4, "Электроника"),
	(5, "Для дома, сада и мероприятий"),
	(6, "Одежда и аксессуары"),
	(7, "Инструмент, спецтехника и ремонт"),
	(8, "Спорт и активный отдых"),
	(9, "Хобби"),
	(10, "Красота и здоровье"),
	(11, "Животные"),	
	(12, "Другое");

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
	(181, 11, "Собаки");