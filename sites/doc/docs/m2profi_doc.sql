-- Adminer 5.4.1 MySQL 8.4.6 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `agency`;
CREATE TABLE `agency` (
  `agency_id` int NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) NOT NULL,
  `caption_dogovor` varchar(255) NOT NULL DEFAULT '0',
  `admin_user_id` int NOT NULL DEFAULT '0',
  `type` int NOT NULL DEFAULT '0' COMMENT 'тип 0-самозерегистрированное 1- одного глобального пользователя 2 отдел продаж 3 админы ',
  `reg_type` int NOT NULL DEFAULT '0',
  `gl_user_id` int NOT NULL DEFAULT '0',
  `sort` tinyint NOT NULL DEFAULT '0',
  `add_datetime` datetime NOT NULL COMMENT 'дата регистрации',
  `add_user_id` int NOT NULL DEFAULT '0' COMMENT 'Пользователь который зарегистрировал ',
  `inn` varchar(255) DEFAULT '',
  `ogrn` varchar(255) DEFAULT '',
  `comment` varchar(255) DEFAULT '' COMMENT 'Служебный комментарий',
  `unactiv` int DEFAULT '0' COMMENT 'Активность (вкл/выкл)',
  `del` tinyint DEFAULT '0',
  PRIMARY KEY (`agency_id`),
  KEY `admin_user_id` (`admin_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `apartaments`;
CREATE TABLE `apartaments` (
  `apartament_id` int NOT NULL AUTO_INCREMENT,
  `home_id` int NOT NULL,
  `section_id` int NOT NULL,
  `apartment_num` int NOT NULL,
  `apartments` int DEFAULT '0',
  `floor` int DEFAULT '0',
  `price` int DEFAULT '0',
  `price_m` int DEFAULT '0',
  `area` decimal(10,2) DEFAULT '0.00',
  `rooms` varchar(20) DEFAULT '0',
  `kitchen_area` int DEFAULT '0',
  `text` varchar(255) DEFAULT '0',
  `house_adress` varchar(255) DEFAULT '0',
  `adress` varchar(255) DEFAULT '0',
  `plan_code` varchar(255) DEFAULT '0',
  `status` int DEFAULT '0',
  `status2` int DEFAULT '0',
  `status_broni_id` int DEFAULT NULL,
  `status_broni_date` datetime DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `image_pb` varchar(255) DEFAULT '0',
  `image_pb_png` varchar(255) DEFAULT NULL,
  `plan_type` tinyint DEFAULT '0',
  `image` varchar(255) DEFAULT '0',
  `area2` decimal(10,2) DEFAULT '0.00',
  `area_t` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`apartament_id`),
  UNIQUE KEY `apartment_num_home_idu` (`apartment_num`,`home_id`),
  KEY `home_id` (`home_id`),
  KEY `apartment_num_home_id` (`apartment_num`,`home_id`),
  KEY `rooms` (`rooms`),
  KEY `apartment_num` (`apartment_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `broni`;
CREATE TABLE `broni` (
  `broni_id` int NOT NULL AUTO_INCREMENT,
  `home_id` int NOT NULL,
  `section_id` int NOT NULL,
  `floor` int NOT NULL,
  `apartments` int NOT NULL,
  `apartments_num1` int NOT NULL,
  `status` int NOT NULL,
  `user_id` int NOT NULL,
  `date` datetime NOT NULL,
  `date_first` datetime NOT NULL COMMENT 'дата первой брони до обновлений',
  `date_fu` datetime NOT NULL,
  `apartments_num` int NOT NULL,
  `apartament_id` int NOT NULL,
  `broni_up_counter` int NOT NULL DEFAULT '0' COMMENT 'счетчик обновлений брони',
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`broni_id`),
  KEY `user_id` (`user_id`),
  KEY `home_id_apartments_num` (`home_id`,`apartments_num`),
  KEY `home_id` (`home_id`),
  KEY `apartments` (`apartments`),
  KEY `status` (`status`),
  KEY `home_id_apartments_num_status` (`home_id`,`apartments_num`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `dir`;
CREATE TABLE `dir` (
  `dir_id` int NOT NULL AUTO_INCREMENT,
  `dir_type` int NOT NULL DEFAULT '0',
  `dir_title` varchar(255) NOT NULL,
  `parent_dir_id` int NOT NULL DEFAULT '0',
  `dir_name` varchar(255) NOT NULL DEFAULT '''''',
  `dir_level` int NOT NULL DEFAULT '0',
  `dir_path` varchar(255) NOT NULL DEFAULT '''''',
  `order` int DEFAULT '0',
  `del` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`dir_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `dir2node`;
CREATE TABLE `dir2node` (
  `dir2node_id` int NOT NULL AUTO_INCREMENT,
  `dir_id` int NOT NULL,
  `node_id` int NOT NULL,
  PRIMARY KEY (`dir2node_id`),
  KEY `dir_id` (`dir_id`),
  KEY `fw_posts` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `excurs`;
CREATE TABLE `excurs` (
  `zapis_id` int NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `message` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `home` int NOT NULL,
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `formhach` varbinary(255) NOT NULL,
  `del` tinyint DEFAULT '0',
  `peoples` int DEFAULT '1',
  PRIMARY KEY (`zapis_id`),
  FULLTEXT KEY `phone_message_name` (`phone`,`message`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `excurs_grafic`;
CREATE TABLE `excurs_grafic` (
  `excurs_grafic_id` int NOT NULL AUTO_INCREMENT,
  `place_id` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL COMMENT 'Дата d.m.Y',
  `time` varchar(255) NOT NULL COMMENT 'время h:m ',
  `сapacity` int NOT NULL DEFAULT '1' COMMENT 'Количество мест',
  PRIMARY KEY (`excurs_grafic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `fileds2node`;
CREATE TABLE `fileds2node` (
  `fileds2node_id` int NOT NULL AUTO_INCREMENT,
  `fw_node_fileds_cnf_id` int NOT NULL COMMENT 'ТИП ПОЛЯ',
  `order` int DEFAULT NULL,
  `value` int DEFAULT NULL,
  `del` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`fileds2node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `files2node`;
CREATE TABLE `files2node` (
  `files2node_id` int NOT NULL AUTO_INCREMENT,
  `node_type` varchar(255) NOT NULL DEFAULT 'node',
  `node_id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `docdate` date DEFAULT NULL,
  `comment` text,
  `link` varchar(255) DEFAULT NULL,
  `puth` varchar(255) DEFAULT NULL,
  `size` int DEFAULT NULL,
  `uptime` int DEFAULT NULL,
  `order` int DEFAULT '0',
  `main` int DEFAULT '0',
  `show` int DEFAULT '1',
  `user_id` int DEFAULT NULL,
  `version_date` int DEFAULT NULL,
  `actual` tinyint DEFAULT NULL,
  `status` tinyint DEFAULT '0',
  `del` int DEFAULT '0',
  PRIMARY KEY (`files2node_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `files2node_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `fw_nodes`;
CREATE TABLE `fw_nodes` (
  `fw_node_id` int NOT NULL AUTO_INCREMENT,
  `fw_node_type` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `descr` text,
  `anons` text,
  `image` varchar(255) DEFAULT '',
  `content` longtext,
  `date_p_start` int DEFAULT '0',
  `date_p_final` int DEFAULT '0',
  `dir` int DEFAULT '0',
  `status` int DEFAULT '1',
  `show` int DEFAULT '1',
  `del` int DEFAULT '0',
  `order` int DEFAULT '0',
  `add_time` int DEFAULT '0',
  `last_edit_time` int DEFAULT '0',
  `show_date` varchar(255) DEFAULT '',
  `add_user_id` int DEFAULT '0',
  `last_edit_user_id` int DEFAULT '0',
  PRIMARY KEY (`fw_node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `fw_sessions`;
CREATE TABLE `fw_sessions` (
  `id` varchar(32) NOT NULL,
  `access` int unsigned DEFAULT NULL,
  `data` varchar(21000) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `ua` varchar(255) DEFAULT NULL,
  `session_user_id` int DEFAULT NULL,
  `session_counter` int DEFAULT NULL,
  `session_created` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


SET NAMES utf8mb4;

DROP TABLE IF EXISTS `homes`;
CREATE TABLE `homes` (
  `homes_id` int NOT NULL AUTO_INCREMENT,
  `home_id` int NOT NULL,
  `complex_domclick` int DEFAULT '0',
  `corpus_code_domclick` int DEFAULT '0',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `long_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show` tinyint DEFAULT NULL COMMENT '1-всем 2-админам ',
  `color_bg` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Цвет дома',
  `seriya` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Русское название серии',
  `complite_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `complite` tinyint DEFAULT NULL,
  `built_year` tinyint DEFAULT NULL,
  `ready_quarter` tinyint DEFAULT NULL,
  `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `order` int DEFAULT NULL,
  `broni_setup` int DEFAULT NULL COMMENT 'Настройка блокировки бронирования',
  `show_keys` tinyint DEFAULT '0' COMMENT 'Выдача ключей (показывать в админке и форме записи)',
  `keys_adress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keys_message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `map_mapkeys_lat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `map_mapkeys_lon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `map_mapkeys_adress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kvartal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '0' COMMENT 'Квартал (инфинити итп)',
  `delivery_date` date DEFAULT NULL,
  `lat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `lon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `adress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `wallmaterial` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `floor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`homes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `homes_sections`;
CREATE TABLE `homes_sections` (
  `homes_sections_id` int NOT NULL AUTO_INCREMENT,
  `homes_id` int NOT NULL,
  `section_id` int NOT NULL,
  `caption` varchar(255) NOT NULL,
  `floor` int NOT NULL,
  `apartments` int NOT NULL,
  `start_num` int NOT NULL,
  PRIMARY KEY (`homes_sections_id`),
  UNIQUE KEY `homes_id_sec_num` (`homes_id`,`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `homes_sections_cl`;
CREATE TABLE `homes_sections_cl` (
  `homes_sections_cl_id` int NOT NULL AUTO_INCREMENT,
  `homes_sections_id` int NOT NULL,
  `floor` int NOT NULL,
  `appart` int NOT NULL,
  `alt_html` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`homes_sections_cl_id`),
  UNIQUE KEY `homes_sections_id_floor_appart` (`homes_sections_id`,`floor`,`appart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `keys_grafic`;
CREATE TABLE `keys_grafic` (
  `keys_grafic_id` int NOT NULL AUTO_INCREMENT,
  `place_id` varchar(255) NOT NULL,
  `place_group_id` int DEFAULT NULL,
  `date` varchar(255) NOT NULL,
  `date_sql` date DEFAULT NULL,
  `time` varchar(255) NOT NULL,
  `time_sql` time DEFAULT NULL,
  `сapacity` int NOT NULL,
  PRIMARY KEY (`keys_grafic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `keys_graficx_date`;
CREATE TABLE `keys_graficx_date` (
  `keys_graficx_date_id` int NOT NULL AUTO_INCREMENT,
  `date` varchar(255) DEFAULT NULL,
  `date_mysql` date DEFAULT NULL,
  `del` tinyint DEFAULT '0',
  `show` tinyint DEFAULT '1',
  PRIMARY KEY (`keys_graficx_date_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `keys_graficx_objects`;
CREATE TABLE `keys_graficx_objects` (
  `keys_graficx_objects_id` int NOT NULL AUTO_INCREMENT,
  `keys_graficx_date_id` int NOT NULL,
  `object_id` varchar(255) NOT NULL,
  PRIMARY KEY (`keys_graficx_objects_id`),
  KEY `keys_graficx_date_id` (`keys_graficx_date_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `keys_graficx_time`;
CREATE TABLE `keys_graficx_time` (
  `keys_graficx_gr_id` int NOT NULL AUTO_INCREMENT,
  `keys_graficx_date_id` int NOT NULL,
  `time` varchar(255) NOT NULL,
  `c` int NOT NULL,
  `pom` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`keys_graficx_gr_id`),
  KEY `keys_graficx_date_id` (`keys_graficx_date_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `landplots`;
CREATE TABLE `landplots` (
  `lp_id` int NOT NULL AUTO_INCREMENT,
  `map_id` int NOT NULL DEFAULT '1',
  `polygon_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `num` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `htype` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `project_id` int DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `raion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `area` float DEFAULT NULL,
  `kadastrnum` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price_area` float DEFAULT NULL,
  `price` float DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `status_broni_id` int DEFAULT NULL,
  `del` tinyint DEFAULT '0',
  `insale` tinyint DEFAULT '0',
  PRIMARY KEY (`lp_id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `landplots_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `landplots_area`;
CREATE TABLE `landplots_area` (
  `area_id` int NOT NULL AUTO_INCREMENT,
  `dir` int DEFAULT '1',
  `caption` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `order` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '1',
  `show` tinyint DEFAULT '1' COMMENT '1 всем 2 админам 3 оп',
  PRIMARY KEY (`area_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `landplots_broni`;
CREATE TABLE `landplots_broni` (
  `lp_broni_id` int NOT NULL AUTO_INCREMENT,
  `lp_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `status` int DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `date_first` datetime DEFAULT NULL,
  `date_fu` datetime DEFAULT NULL,
  `broni_up_counter` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `del` tinyint DEFAULT '0',
  `price` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`lp_broni_id`),
  KEY `lp_id` (`lp_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `landplots_broni_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `landplots_maps`;
CREATE TABLE `landplots_maps` (
  `landplots_map_id` int NOT NULL AUTO_INCREMENT,
  `area_id` int NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `bg_image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `svg` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `show` int NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `lat` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `lon` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `numbers` tinyint NOT NULL DEFAULT '0',
  `customcss` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`landplots_map_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `messages_id` int NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `to` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '0',
  `subject` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '0',
  `text` text COLLATE utf8mb4_general_ci,
  `status` tinyint DEFAULT '0',
  `type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '0',
  PRIMARY KEY (`messages_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `messages_fileds`;
CREATE TABLE `messages_fileds` (
  `messages_fileds_id` int NOT NULL AUTO_INCREMENT,
  `messages_id` int NOT NULL,
  `filed_name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `num` int DEFAULT '0',
  PRIMARY KEY (`messages_fileds_id`),
  KEY `messages_id` (`messages_id`),
  CONSTRAINT `messages_fileds_ibfk_3` FOREIGN KEY (`messages_id`) REFERENCES `messages` (`messages_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `obj_status`;
CREATE TABLE `obj_status` (
  `obj_status_id` int NOT NULL AUTO_INCREMENT,
  `obj_status_code` int NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `bgcolor` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `noedit` tinyint NOT NULL DEFAULT '0' COMMENT 'Не редактируемый',
  PRIMARY KEY (`obj_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `obj_status_group`;
CREATE TABLE `obj_status_group` (
  `obj_status_group_id` int NOT NULL,
  `users_group_id` int NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `bgcolor` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  KEY `users_group_id` (`users_group_id`),
  CONSTRAINT `obj_status_group_ibfk_1` FOREIGN KEY (`users_group_id`) REFERENCES `users_group` (`users_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `parking_broni`;
CREATE TABLE `parking_broni` (
  `parking_broni_id` int NOT NULL AUTO_INCREMENT,
  `parking_space_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` int NOT NULL,
  `date` datetime NOT NULL,
  `date_first` datetime NOT NULL,
  `date_fu` datetime NOT NULL,
  `broni_up_counter` int NOT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`parking_broni_id`),
  KEY `user_id` (`user_id`),
  KEY `parking_space_id` (`parking_space_id`),
  CONSTRAINT `parking_broni_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `parking_broni_ibfk_4` FOREIGN KEY (`parking_space_id`) REFERENCES `parking_spaces` (`parking_space_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `parking_buildings`;
CREATE TABLE `parking_buildings` (
  `parking_building_id` int NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) DEFAULT NULL,
  `adress` varchar(255) DEFAULT NULL,
  `adress_disp` varchar(255) DEFAULT NULL,
  `lat` varchar(255) DEFAULT NULL,
  `lon` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `show` tinyint DEFAULT '0',
  `order` tinyint DEFAULT '0',
  `delivery_date` varchar(255) DEFAULT NULL,
  `complite` tinyint DEFAULT NULL,
  `complite_text` varchar(255) DEFAULT '',
  `del` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`parking_building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `parking_floors`;
CREATE TABLE `parking_floors` (
  `parking_floor_id` int NOT NULL AUTO_INCREMENT,
  `parking_building_id` int NOT NULL,
  `caption` varchar(255) DEFAULT '',
  `floor` varchar(255) DEFAULT '',
  `plan_file` varchar(255) DEFAULT '0',
  `plan_width` int DEFAULT '0',
  `plan_height` int DEFAULT '0',
  `del` tinyint DEFAULT '0',
  PRIMARY KEY (`parking_floor_id`),
  KEY `parking_building_id` (`parking_building_id`),
  CONSTRAINT `parking_floors_ibfk_1` FOREIGN KEY (`parking_building_id`) REFERENCES `parking_buildings` (`parking_building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `parking_spaces`;
CREATE TABLE `parking_spaces` (
  `parking_space_id` int NOT NULL AUTO_INCREMENT,
  `parking_floor_id` int NOT NULL,
  `parking_building_id` int NOT NULL,
  `num` varchar(255) DEFAULT NULL,
  `area` float DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `status` tinyint DEFAULT '0',
  `x` float DEFAULT '0',
  `y` float DEFAULT '0',
  `rotate` float DEFAULT '0',
  `status_broni_id` int DEFAULT NULL,
  `del` tinyint DEFAULT '0',
  PRIMARY KEY (`parking_space_id`),
  KEY `parking_floor_id` (`parking_floor_id`),
  KEY `parking_building_id` (`parking_building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `text` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `rent_broni`;
CREATE TABLE `rent_broni` (
  `rent_broni_id` int NOT NULL AUTO_INCREMENT,
  `rent_objects_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` int NOT NULL,
  `date` datetime NOT NULL,
  `date_first` datetime NOT NULL,
  `date_fu` datetime NOT NULL,
  `broni_up_counter` int NOT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`rent_broni_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `rent_homes`;
CREATE TABLE `rent_homes` (
  `rent_home_id` int NOT NULL AUTO_INCREMENT,
  `adress` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `descr` text,
  `lat` varchar(255) DEFAULT NULL,
  `lon` varchar(255) DEFAULT NULL,
  `build_type` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `del` tinyint DEFAULT '0',
  PRIMARY KEY (`rent_home_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `rent_objects`;
CREATE TABLE `rent_objects` (
  `rent_objects_id` int NOT NULL AUTO_INCREMENT,
  `rent_home_id` int NOT NULL DEFAULT '0',
  `section_id` int NOT NULL DEFAULT '0',
  `appart_num` int NOT NULL DEFAULT '0',
  `build_type` varchar(255) NOT NULL DEFAULT '' COMMENT 'Тип дома текстом',
  `adress` varchar(255) NOT NULL DEFAULT '' COMMENT 'адрес внутри здания',
  `area` decimal(10,2) DEFAULT '0.00' COMMENT 'площадь',
  `max_power_grid` varchar(255) DEFAULT '' COMMENT 'Нагрузка электросетей',
  `separate_entrance` tinyint DEFAULT '1' COMMENT 'Тип входа',
  `place_for_unloading` tinyint DEFAULT '0' COMMENT 'Место под разгрузку',
  `appointment` tinyint DEFAULT '0' COMMENT 'Назначение',
  `rooms` tinyint DEFAULT '1' COMMENT 'Комнат',
  `osb` tinyint DEFAULT '0',
  `free_plan` varchar(255) NOT NULL DEFAULT 'нет',
  `comment` varchar(255) DEFAULT '',
  `floor` tinyint NOT NULL DEFAULT '1' COMMENT 'Этажей',
  `plan` varchar(255) NOT NULL DEFAULT '' COMMENT 'Ссылка на изображение ',
  `status` tinyint DEFAULT '0',
  `status_broni_id` int DEFAULT NULL,
  `show` tinyint DEFAULT '1',
  `show_b` tinyint DEFAULT '0' COMMENT 'Доступно к бронированию',
  `sale` tinyint DEFAULT '0' COMMENT '0-аренда 1-продажа 2-продажа и аренда',
  `sale_price` varchar(255) DEFAULT '0',
  `del` tinyint DEFAULT '0',
  PRIMARY KEY (`rent_objects_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `show2node`;
CREATE TABLE `show2node` (
  `show2node_id` int NOT NULL AUTO_INCREMENT,
  `node_id` int NOT NULL,
  `user_id` int NOT NULL,
  `show` int NOT NULL,
  `time` int NOT NULL,
  PRIMARY KEY (`show2node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `agency_id` varchar(255) DEFAULT NULL,
  `e_mail` varchar(255) DEFAULT '',
  `phone` varchar(255) DEFAULT '',
  `gl_user_id` int NOT NULL DEFAULT '0',
  `add_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_group` varchar(255) NOT NULL DEFAULT 'agent',
  `users_group_id` int DEFAULT NULL,
  `del` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `users_group_id` (`users_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `users_group`;
CREATE TABLE `users_group` (
  `users_group_id` int NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `color` varchar(100) NOT NULL DEFAULT '#000',
  `unactiv` tinyint NOT NULL DEFAULT '0',
  `del` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`users_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `users_group_rules`;
CREATE TABLE `users_group_rules` (
  `users_group_rules_id` int NOT NULL AUTO_INCREMENT,
  `users_group_id` int NOT NULL,
  `ctr` int NOT NULL,
  `act` int NOT NULL,
  `rule` int NOT NULL,
  PRIMARY KEY (`users_group_rules_id`),
  KEY `users_group_id` (`users_group_id`),
  CONSTRAINT `users_group_rules_ibfk_1` FOREIGN KEY (`users_group_id`) REFERENCES `users_group` (`users_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `users_stat`;
CREATE TABLE `users_stat` (
  `users_stat_id` int NOT NULL AUTO_INCREMENT,
  `users_id` int NOT NULL,
  `date` datetime NOT NULL,
  `action` varchar(155) NOT NULL,
  KEY `users_stat_id` (`users_stat_id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `zapis`;
CREATE TABLE `zapis` (
  `zapis_id` int NOT NULL AUTO_INCREMENT,
  `home_id` int NOT NULL,
  `section` int NOT NULL,
  `apartment_num` int NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `new_passport` tinyint NOT NULL,
  `fio` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `at` int DEFAULT NULL,
  `del` int DEFAULT '0',
  `pom` int DEFAULT '0',
  PRIMARY KEY (`zapis_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- 2025-11-26 14:48:06 UTC
