-- ============================================================
--  ТОО «Колор» — Мебельный цех
--  База данных: kolor_shop
--  Импортируйте этот файл через phpMyAdmin:
--    1. Откройте phpMyAdmin
--    2. Создайте базу данных "kolor_shop" (кодировка utf8mb4)
--    3. Выберите её и нажмите вкладку «Импорт»
--    4. Загрузите этот файл и нажмите «Вперёд»
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
--  База данных
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `kolor_shop`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `kolor_shop`;

-- ------------------------------------------------------------
--  Таблица: users — зарегистрированные пользователи
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id`         INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(150)    NOT NULL,
    `email`      VARCHAR(150)    NOT NULL,
    `phone`      VARCHAR(30)     DEFAULT NULL,
    `password`   VARCHAR(255)    NOT NULL,
    `role`       ENUM('customer','admin') DEFAULT 'customer',
    `created_at` DATETIME        DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Зарегистрированные пользователи';

-- ------------------------------------------------------------
--  Таблица: categories — категории товаров
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(10)  DEFAULT '🪑',
    UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Категории мебели';

-- ------------------------------------------------------------
--  Таблица: products — товары
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id`          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED  DEFAULT NULL,
    `name`        VARCHAR(200)  NOT NULL,
    `description` TEXT,
    `price`       DECIMAL(12,2) NOT NULL,
    `old_price`   DECIMAL(12,2) DEFAULT NULL,
    `image`       VARCHAR(200)  DEFAULT 'default.jpg',
    `material`    VARCHAR(200)  DEFAULT NULL,
    `dimensions`  VARCHAR(200)  DEFAULT NULL,
    `color`       VARCHAR(100)  DEFAULT NULL,
    `in_stock`    TINYINT(1)   DEFAULT 1  COMMENT '1=в наличии, 0=нет',
    `featured`    TINYINT(1)   DEFAULT 0  COMMENT '1=хит продаж',
    `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_prod_cat`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Товары мебельного цеха';

-- ------------------------------------------------------------
--  Таблица: cart — корзина покупателей
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity`   INT UNSIGNED DEFAULT 1,
    `added_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_cart` (`user_id`, `product_id`),
    CONSTRAINT `fk_cart_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cart_prod`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Корзина покупателей';

-- ------------------------------------------------------------
--  Таблица: orders — оформленные заказы
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id`         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED  DEFAULT NULL,
    `total`      DECIMAL(12,2) NOT NULL,
    `name`       VARCHAR(150)  NOT NULL,
    `phone`      VARCHAR(30)   NOT NULL,
    `address`    VARCHAR(500)  NOT NULL,
    `comment`    TEXT,
    `status`     ENUM('new','processing','done') DEFAULT 'new',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_order_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Заказы клиентов';

-- ------------------------------------------------------------
--  Таблица: order_items — состав заказов
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
    `id`           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `order_id`     INT UNSIGNED  NOT NULL,
    `product_id`   INT UNSIGNED  DEFAULT NULL,
    `product_name` VARCHAR(200)  NOT NULL,
    `price`        DECIMAL(12,2) NOT NULL,
    `quantity`     INT UNSIGNED  NOT NULL,
    CONSTRAINT `fk_oi_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_oi_prod`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Состав заказов';

-- ============================================================
--  НАЧАЛЬНЫЕ ДАННЫЕ
-- ============================================================

-- Категории
INSERT INTO `categories` (`name`, `slug`, `icon`) VALUES
('Диваны и кресла',    'divany',   '🛋️'),
('Спальные гарнитуры', 'spalni',   '🛏️'),
('Кухонные гарнитуры', 'kuhni',    '🍽️'),
('Шкафы и стеллажи',   'shkafi',   '🗄️'),
('Столы и стулья',     'stoly',    '🪑'),
('Детская мебель',     'detskaya', '🧸');

-- Товары
INSERT INTO `products` (`category_id`,`name`,`description`,`price`,`old_price`,`image`,`material`,`dimensions`,`color`,`in_stock`,`featured`) VALUES
(1,'Диван «Комфорт» угловой',        'Просторный угловой диван с механизмом трансформации. Обивка из экокожи высокого качества.', 89900.00, 119000.00,'sofa1.jpg',     'Экокожа, ДСП',   '280×180×90 см',  'Серый',        1,1),
(1,'Кресло «Релакс»',                'Мягкое кресло с откидной спинкой. Идеально для отдыха.',                                    24900.00, NULL,     'armchair1.jpg', 'Велюр',           '90×85×100 см',  'Бежевый',      1,0),
(1,'Диван «Модерн» трёхместный',     'Стильный прямой диван в скандинавском стиле.',                                              54900.00, 69000.00, 'sofa2.jpg',     'Рогожка, дерево', '220×85×80 см',  'Синий',        1,1),
(2,'Спальный гарнитур «Венеция»',    'Полный комплект: кровать, 2 тумбы, шкаф, комод.',                                         189000.00,229000.00,'bedroom1.jpg',  'МДФ, зеркало',    'Кровать 160×200','Белый глянец', 1,1),
(2,'Кровать «Сонет» 160×200',        'Кровать с мягким изголовьем и подъёмным механизмом.',                                       64900.00, NULL,     'bed1.jpg',      'Экокожа, ДСП',   '164×206×100 см','Коричневый',   1,0),
(3,'Кухонный гарнитур «Прованс»',    'Уютный гарнитур в стиле прованс с патиной.',                                              145000.00,165000.00,'kitchen1.jpg',  'МДФ крашеный',    'Индивидуально', 'Кремовый',     1,1),
(3,'Кухонный гарнитур «Лофт»',       'Минималистичный гарнитур с элементами металла.',                                          128000.00, NULL,     'kitchen2.jpg',  'ЛДСП, металл',    'Индивидуально', 'Серый/Чёрный', 1,0),
(4,'Шкаф-купе «Стандарт» 2-дверный', 'Вместительный шкаф с зеркальными дверями-купе.',                                          49900.00, 59000.00, 'wardrobe1.jpg', 'ЛДСП, зеркало',   '180×60×220 см', 'Венге',        1,0),
(4,'Стеллаж «Модуль»',               'Открытый стеллаж из массива дерева.',                                                      18900.00, NULL,     'shelf1.jpg',    'Массив сосны',    '80×30×180 см',  'Натуральный',  1,0),
(5,'Обеденный стол «Классик»',        'Раздвижной обеденный стол на 6–8 персон.',                                                 34900.00, 42000.00, 'table1.jpg',    'ЛДСП, металл',    '120–160×80 см', 'Дуб сонома',   1,1),
(5,'Стул «Лофт» (4 шт)',              'Комплект из 4 стульев на металлокаркасе.',                                                  22900.00, NULL,     'chair1.jpg',    'Экокожа, металл', '42×45×90 см',   'Чёрный',       1,0),
(6,'Детская кровать «Звёздочка»',     'Кровать-чердак с горкой и рабочей зоной.',                                                  58900.00, 72000.00, 'kids1.jpg',     'МДФ, металл',     '90×200 см',     'Белый/Розовый',1,1);

-- Администратор
-- Данные для входа в панель администратора:
--   Email:  admin@kolor.kz
--   Пароль: admin123
-- Хэш сгенерирован: password_hash('admin123', PASSWORD_DEFAULT)
-- Для смены пароля выполните:  php -r "echo password_hash('НовыйПароль', PASSWORD_DEFAULT);"
INSERT INTO `users` (`name`,`email`,`phone`,`password`,`role`) VALUES
('Администратор','admin@kolor.kz','+7 (700) 000-00-00',
 '$2y$10$eubdGUA6RtwesibSx6ikP.36IXMc0JGTA0vwxKSw5JtRjLULO/2UK',
 'admin');

SET FOREIGN_KEY_CHECKS = 1;
