-- EM task 12: «жизнь» на генплане — схема + дамп контента стейджа
-- БД: m2profi_em
-- Ветка: feature/12-genplan-life
-- Объединяет бывшие 007 (tracks/agents) + 008 (settings) и seed из локальной БД (kvartal_id=6)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Schema
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `genplan_life_tracks` (
  `track_id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kvartal_id`   INT UNSIGNED NOT NULL COMMENT 'homes_kvartal.homes_kvartal_id',
  `role`         ENUM('road','walk','dog') NOT NULL DEFAULT 'road'
                   COMMENT 'road=машины, walk=люди, dog=собаки',
  `title`        VARCHAR(128) NULL DEFAULT NULL COMMENT 'подпись в админке',
  `points`       TEXT NOT NULL COMMENT 'JSON [[x,y],…] CRS.Simple y=0 у низа; >=2 точек; OPEN polyline',
  `closed`       TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Stage1 всегда 0 для car/person',
  `sort_order`   INT NOT NULL DEFAULT 0,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `del`          TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`track_id`),
  KEY `idx_glt_kvartal` (`kvartal_id`, `del`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `genplan_life_agents` (
  `agent_id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kvartal_id`   INT UNSIGNED NOT NULL,
  `track_id`     INT UNSIGNED NULL DEFAULT NULL
                   COMMENT 'обязателен для car/person/dog; NULL для bird/cloud',
  `species`      ENUM('car','person','dog','bird','cloud') NOT NULL,
  `sprite_key`   VARCHAR(32) NOT NULL DEFAULT 'default'
                   COMMENT 'car_a|car_b|car_c|person_a|… встроенные SVG',
  `speed`        DECIMAL(8,2) NOT NULL DEFAULT 40
                   COMMENT 'px/s вдоль трека',
  `period_ms`    INT UNSIGNED NOT NULL DEFAULT 8000
                   COMMENT 'интервал цикла / появления',
  `enabled`      TINYINT(1) NOT NULL DEFAULT 1,
  `params_json`  TEXT NULL COMMENT 'JSON: phase, direction, scale…',
  `sort_order`   INT NOT NULL DEFAULT 0,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `del`          TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`agent_id`),
  KEY `idx_gla_kvartal` (`kvartal_id`, `del`),
  KEY `idx_gla_track` (`track_id`, `del`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `genplan_life_settings` (
  `kvartal_id`         INT UNSIGNED NOT NULL COMMENT 'homes_kvartal.homes_kvartal_id',
  `perspective_json`   TEXT NULL COMMENT 'JSON: {enabled,points[[x,y]×3],scaleNear,scaleFar}',
  `settings_json`      TEXT NULL COMMENT 'JSON ambient overrides: birds,clouds,light…',
  `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`kvartal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Seed (стейдж: kvartal_id=6) — REPLACE, чтобы можно было перенакатить
-- ---------------------------------------------------------------------------

REPLACE INTO `genplan_life_tracks` (`track_id`, `kvartal_id`, `role`, `title`, `points`, `closed`, `sort_order`, `created_at`, `updated_at`, `del`) VALUES
(1,6,'road','','[[2.3570082178308667,564.6395249409815],[317.72663262703105,351.09327702264414],[126.80780170666323,225.22826997143866],[91.45246264733585,230.88512422093106]]',0,0,'2026-08-11 02:27:45','2026-08-11 02:27:45',0),
(2,6,'walk','','[[371.46674799720864,287.45366671585487],[483.18961942468314,209.67192078533463],[459.14798886434056,192.7013580368575]]',0,0,'2026-08-11 02:28:15','2026-08-11 02:28:15',0),
(3,6,'walk','','[[430.66666412353516,838.1354217529297],[399.91666412353516,822.3854217529297],[536.4166641235352,759.8854217529297],[739.6666641235352,667.8854217529297],[793.6666641235352,672.3854217529297],[815.9166641235352,672.3854217529297],[823.4166641235352,677.6354217529297],[811.4166641235352,685.6354217529297]]',0,0,'2026-08-11 02:38:30','2026-08-11 02:38:30',0),
(4,6,'walk','','[[830.4166641235352,708.1354217529297],[850.9166641235352,696.8854217529297],[901.1666641235352,727.8854217529297],[890.9166641235352,734.8854217529297]]',0,0,'2026-08-11 02:38:56','2026-08-11 02:38:56',0),
(5,6,'road','','[[733.344375487771,297.6362432517748],[752.3716893278146,275.0413080667231],[644.1538418625669,195.36443136154077],[610.8560426424907,201.31046693655438],[319.50029946682406,389.20519110698433],[353.98730580190295,403.475676487017]]',0,0,'2026-08-11 02:49:52','2026-08-11 02:49:52',0),
(6,6,'road','','[[1009.3333282470703,829.640625],[1078.3333282470703,797.140625]]',0,0,'2026-08-11 02:50:50','2026-08-11 02:50:52',1),
(7,6,'road','','[[1209.3333282470703,765.140625],[1199.3333282470703,754.640625],[1124.8333282470703,784.640625],[1126.3333282470703,793.640625],[1133.3333282470703,801.640625],[1261.3333282470703,884.140625]]',0,0,'2026-08-11 02:51:33','2026-08-11 02:51:33',0),
(8,6,'road','','[[1227.5686125908605,884.3208316976759],[1099.7523574722961,802.7538794180656],[1084.616221997729,803.1743276256925],[1011.458233870656,830.923909329065]]',0,0,'2026-08-11 03:20:25','2026-08-11 03:20:25',0),
(9,6,'road','','[[442.0312070643286,857.950845675579],[381.3886303285093,825.7974985426513],[366.2262396122246,825.648847653276],[358.19909158595624,826.6894038789034],[321.63097279962255,843.9329070464428],[231.52680872283574,886.4230727358103]]',0,0,'2026-08-11 03:21:29','2026-08-11 03:21:29',0),
(10,6,'road','','[[190.0749311659814,165.9377520765908],[215.64288413853993,188.53268726164254],[158.5609426184093,215.28984734920374],[149.04728569838755,233.12795407424457],[319.698506701278,340.75119798199086],[341.69883832882834,336.5889730794813],[564.0805688343372,195.66792995165886],[604.5136107444297,167.72156274909491],[602.1351965144243,140.96440266153368],[533.7557874017679,67.82816508886633],[446.94366800656917,0.637963091212588]]',0,0,'2026-08-11 12:35:33','2026-08-11 12:35:33',0),
(11,6,'road','','[[459.3836983177982,2.1728838095522343],[595.8553070868019,122.3810366112653],[636.1603936144351,163.39322992008505],[667.9801987678297,142.18002648448862]]',0,0,'2026-08-11 12:35:56','2026-08-11 12:35:56',0),
(12,6,'road','','[[1133.3333282470703,535.5364685058594],[1218.8333282470703,495.5364685058594],[1175.3333282470703,458.5364685058594],[1116.8333282470703,482.5364685058594],[1109.3333282470703,478.5364685058594],[890.3333282470703,578.5364685058594],[883.3333282470703,586.0364685058594],[866.3333282470703,595.5364685058594],[858.3333282470703,615.0364685058594],[1023.3333282470703,727.5364685058594],[1079.3333282470703,763.0364685058594]]',0,0,'2026-08-11 12:59:25','2026-08-11 12:59:25',0),
(13,6,'road','','[[302.66666412353516,584.2682304382324],[152.16666412353516,559.7682304382324],[138.41666412353516,568.7682304382324],[97.16666412353516,757.5182304382324],[103.41666412353516,766.5182304382324],[115.41666412353516,768.7682304382324]]',0,0,'2026-08-11 13:00:04','2026-08-11 13:00:04',0),
(14,6,'road','','[[945.0232419582554,3.6543404124306185],[914.1038569681846,22.681654252474157],[946.2124490732581,50.033417897536744],[1066.322367688533,51.22262501253946],[992.5915265583642,278.3611839780592],[1024.7001186634377,302.1453262781136]]',0,0,'2026-08-11 13:01:05','2026-08-11 13:01:05',0),
(15,6,'walk',NULL,'[[710,166],[702,157],[869,57],[970,59],[982,74]]',0,0,'2026-08-11 13:15:24','2026-08-11 13:15:34',0),
(16,6,'walk','','[[569.6923561828996,142.73246444688385],[588.0771324937498,161.11724075773407],[532.9228035611991,197.88679337943455],[507.4669594384834,179.5020170685843]]',0,0,'2026-08-11 13:16:06','2026-08-11 13:16:06',0);

REPLACE INTO `genplan_life_agents` (`agent_id`, `kvartal_id`, `track_id`, `species`, `sprite_key`, `speed`, `period_ms`, `enabled`, `params_json`, `sort_order`, `created_at`, `updated_at`, `del`) VALUES
(1,6,1,'car','car_b',34.00,14000,1,'{"phase":0,"direction":1,"scale":1}',0,'2026-08-11 02:27:45','2026-08-11 02:27:53',0),
(2,6,2,'person','person_b',7.00,20000,1,'{"phase":0,"direction":1,"scale":1,"rotateVariants":true}',0,'2026-08-11 02:28:15','2026-08-11 02:28:22',0),
(3,6,3,'person','person_a',7.00,20000,1,'{"phase":0,"direction":1,"scale":1,"rotateVariants":true,"color":"#5c6670","colorId":"gray"}',0,'2026-08-11 02:38:30','2026-08-11 02:38:30',0),
(4,6,4,'person','person_a',7.00,20000,1,'{"phase":0,"direction":1,"scale":1,"rotateVariants":true,"color":"#4a6f98","colorId":"blue"}',0,'2026-08-11 02:38:56','2026-08-11 02:38:56',0),
(5,6,5,'car','car_a',24.00,18000,1,'{"phase":0,"direction":1,"scale":1,"color":"#a84a4a","colorId":"red"}',0,'2026-08-11 02:49:52','2026-08-11 02:49:52',0),
(6,6,6,'car','car_a',34.00,14000,1,'{"phase":0,"direction":1,"scale":1,"color":"#5a6570","colorId":"gray"}',0,'2026-08-11 02:50:50','2026-08-11 02:50:50',0),
(7,6,7,'car','car_c',34.00,14000,1,'{"phase":0,"direction":1,"scale":1,"color":"#d0d6dc","colorId":"white"}',0,'2026-08-11 02:51:33','2026-08-11 02:51:33',0),
(8,6,8,'car','car_a',34.00,14000,1,'{"phase":0,"direction":1,"scale":1,"color":"#c3c9d0","colorId":"gray"}',0,'2026-08-11 03:20:25','2026-08-11 03:20:25',0),
(9,6,9,'car','car_a',34.00,14000,1,'{"phase":0,"direction":1,"scale":1,"color":"#c3c9d0","colorId":"gray"}',0,'2026-08-11 03:21:29','2026-08-11 03:21:29',0),
(10,6,10,'car','car_a',34.00,14000,1,'{"phase":0,"direction":1,"scale":1,"rotateVariants":true,"color":"#c3c9d0","colorId":"gray"}',0,'2026-08-11 12:35:33','2026-08-11 12:35:33',0),
(11,6,11,'car','car_a',34.00,14000,1,'{"phase":0,"direction":1,"scale":1,"rotateVariants":true,"color":"#c3c9d0","colorId":"gray"}',0,'2026-08-11 12:35:56','2026-08-11 12:35:56',0),
(12,6,12,'car','car_a',34.00,14000,1,'{"phase":0,"direction":1,"scale":1,"rotateVariants":true,"color":"#c3c9d0","colorId":"gray"}',0,'2026-08-11 12:59:25','2026-08-11 12:59:25',0),
(13,6,13,'car','car_a',34.00,14000,1,'{"phase":0,"direction":1,"scale":1,"rotateVariants":true,"color":"#c3c9d0","colorId":"gray"}',0,'2026-08-11 13:00:04','2026-08-11 13:00:04',0),
(14,6,14,'car','car_a',34.00,14000,1,'{"phase":0,"direction":1,"scale":1,"rotateVariants":true,"color":"#c3c9d0","colorId":"gray"}',0,'2026-08-11 13:01:05','2026-08-11 13:01:05',0),
(15,6,15,'person','person_a',7.00,20000,1,'{"phase":0,"direction":1,"scale":1,"rotateVariants":true,"color":"#5c6670","colorId":"gray"}',0,'2026-08-11 13:15:24','2026-08-11 13:15:34',0),
(16,6,16,'person','person_a',7.00,20000,1,'{"phase":0,"direction":1,"scale":1,"rotateVariants":true,"color":"#5c6670","colorId":"gray"}',0,'2026-08-11 13:16:06','2026-08-11 13:16:06',0);

REPLACE INTO `genplan_life_settings` (`kvartal_id`, `perspective_json`, `settings_json`, `updated_at`) VALUES
(6,'{"enabled":true,"points":[[-277,1091],[1629,191],[1632,213]],"scaleNear":1,"scaleFar":0.35}','{"cars":true,"people":true,"birds":true,"clouds":true,"light":"day","birdFlockSize":5,"birdFlockPeriodMs":26000,"birdSingles":2,"birdSinglePeriodMs":15000,"cloudCount":4,"cloudOpacity":0.85,"cloudShade":0.38,"cloudSpeed":20,"groundPitch":0.32,"groundSkew":0.22,"personBillboard":true,"lightFromDeg":48,"shadowLen":7,"shadowOpacity":0.34}','2026-08-11 12:53:52');

ALTER TABLE `genplan_life_tracks` AUTO_INCREMENT = 17;
ALTER TABLE `genplan_life_agents` AUTO_INCREMENT = 17;

SET FOREIGN_KEY_CHECKS = 1;
