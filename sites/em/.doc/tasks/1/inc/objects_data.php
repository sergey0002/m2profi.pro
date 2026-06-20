<?php
/**
 * Файл: sites/xdemo2/sahmatka/inc/objects_data.php
 * Централизованное хранилище данных об объектах
 */

$domain_zemexx = "https://zemexx.ru";

// 1. ГОТОВЫЕ ДОМА
$houses_data = [
    'mo' => [
        'Севиан строй' => [
            ['name' => 'Дом 100 м² (Барн)', 'price' => '18500000', 'img' => 'https://static.tildacdn.com/stor3166-6666-4537-b035-323939363063/bc5c65079ef2f19424b6052d80e0aeb6.jpg', 'descr' => 'Проект одноэтажного дома из камня «К100 Барн» - необычный и современный дом.'],
            ['name' => 'Дом 150 м² (Камень)', 'price' => '25400000', 'img' => 'https://static.tildacdn.com/stor3965-6136-4033-b234-353430653461/aa6ef35b3ba5843e131073f3870fd7cb.jpg', 'descr' => 'Двухэтажный дом из газобетона для комфортного размещения семьи.'],
            ['name' => 'Дом 155 м² (Камень)', 'price' => '21900000', 'img' => 'https://static.tildacdn.com/stor3139-6163-4566-b035-303565313831/bd8f298f97ef8c92e51d689f716a5f7f.jpg', 'descr' => 'Надёжный двухэтажный дом, идеально подходящий для семьи с детьми.'],
            ['name' => 'Дом 190 м² (Камень)', 'price' => '35000000', 'img' => 'https://static.tildacdn.com/stor3239-6265-4534-a138-386565333461/c4d16469572595be8645288595d0e4c4.jpg', 'descr' => 'Просторный двухэтажный дом из газобетона для большой семьи.'],
            ['name' => 'Дом 105 м² (Брус)', 'price' => '11900000', 'img' => 'https://static.tildacdn.com/stor6330-6534-4930-b663-313262613863/1ecfbe6a61cd84212b1844a0cd72b350.jpg', 'descr' => 'Элегантный двухэтажный жилой дом из бруса, компактность и функциональность.'],
            ['name' => 'Дом 130 м² (Брус)', 'price' => '12600000', 'img' => 'https://static.tildacdn.com/stor6437-3431-4237-b039-336266396365/606a8c73bc34b1010ef718927915dc67.jpg', 'descr' => 'Просторный дом с атмосферой легкости из натуральной древесины.'],
            ['name' => 'Дом 135 м² (Брус)', 'price' => '14300000', 'img' => 'https://static.tildacdn.com/stor3432-6262-4538-a336-313635643430/e1635868588b8b416b81e56a3cac61cd.jpg', 'descr' => 'Функциональный двухэтажный дом с потрясающим видом.'],
            ['name' => 'Дом 150 м² (Брус)', 'price' => '15500000', 'img' => 'https://static.tildacdn.com/stor3762-3635-4361-b932-336163313363/8251f4db9ad5939e4e083df7794d38da.jpg', 'descr' => 'Классический проект из бруса для постоянного проживания.'],
            ['name' => 'Дом 180 м² (Брус)', 'price' => '15400000', 'img' => 'https://static.tildacdn.com/stor3362-3136-4238-b165-363462643032/7815727f61cf7c3ff3b099f51c245c34.jpg', 'descr' => 'Роскошный просторный дом с большой площадью остекления.'],
            ['name' => 'Дом 170 м² (Брус)', 'price' => '15700000', 'img' => 'https://static.tildacdn.com/stor3934-3533-4938-b866-623138313533/f7ba97745b193217d9498d09fe14c9d1.jpg', 'descr' => 'Стильный проект с продуманной планировкой комнат.'],
        ],
        'FirstKey' => [
            ['name' => 'FK 80-01B Комб. штукатурка', 'art' => 'FK 80-01B', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor6664-3934-4835-a433-366531376465/66319018.jpg'],
            ['name' => 'FK 80-01B Светлая штукатурка', 'art' => 'FK 80-01B', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor3235-3230-4839-b963-373232333762/46873748.jpg'],
            ['name' => 'FK 80-01B Серый кирпич', 'art' => 'FK 80-01B', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor3333-3064-4466-a531-636631366636/61201618.jpg'],
            ['name' => 'FK 80-01D Графит', 'art' => 'FK 80-01D', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor3039-3566-4735-a365-333134666362/18208298.jpg'],
            ['name' => 'FK 80-01D Серый кирпич', 'art' => 'FK 80-01D', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor6135-6533-4031-b963-313039633835/50407585.jpg'],
            ['name' => 'FK 80-02B Бежевый кирпич', 'art' => 'FK 80-02B', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor6266-3235-4961-b662-613431303563/21211448.jpg'],
            ['name' => 'FK 1001-01B Бежевый кирпич', 'art' => 'FK 1001-01B', 'price' => '8250000', 'area' => '100', 'floors' => '1', 'bedrooms' => '3', 'bath' => '2', 'img' => 'https://static.tildacdn.com/stor3937-3839-4862-a434-363137653534/83376456.jpg'],
            ['name' => 'FK 1001-01B Комб. кирпич', 'art' => 'FK 1001-01B', 'price' => '8250000', 'area' => '100', 'floors' => '1', 'bedrooms' => '3', 'bath' => '2', 'img' => 'https://static.tildacdn.com/stor6562-3965-4934-b564-643237393034/27280670.jpg'],
        ]
    ],
    'altai' => [
        'АлтайСтрой' => [
            ['name' => 'Шале 120 м²', 'price' => '9800000', 'img' => 'https://static.tildacdn.com/stor3538-3163-4433-b333-663136616238/7011909429f0e749041a767c596b88e4.jpg', 'descr' => ' Горное шале с панорамным видом на Катунь.']
        ]
    ],
    'nsk' => [
        'СибДом' => [
            ['name' => 'Коттедж 200 м²', 'price' => '14500000', 'img' => 'https://static.tildacdn.com/stor3666-3865-4766-b631-343939383161/1ba4d0addfa04a5ec8bde58b11e3ec29.jpg', 'descr' => ' Просторный кирпичный дом для сибирской зимы.']
        ]
    ]
];

// 2. ЗЕМЕЛЬНЫЕ УЧАСТКИ
$plots_data = [
    'mo' => [
        'РигаЛес' => [
            ['id' => 'Участок №11', 'area' => '10.5', 'price' => '15739500', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/020/irmqpnw2qji1oj7u84so5twm1c0bb77b/577_392_240cd750bba9870f18aada2478b24840a/N.webp'],
            ['id' => 'Участок №12', 'area' => '8.2', 'price' => '12291800', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/020/irmqpnw2qji1oj7u84so5twm1c0bb77b/577_392_240cd750bba9870f18aada2478b24840a/N.webp'],
        ],
        'Каретный ряд' => [
            ['id' => 'Участок №5', 'area' => '12.0', 'price' => '9660000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/974/ewif4q7s5yas3617j0qo0tuw4fac1pp8/577_392_240cd750bba9870f18aada2478b24840a/N.webp'],
        ],
        'ПриЛесной' => [
            ['id' => 'Участок №22', 'area' => '9.0', 'price' => '12240000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/682/pw7fkgaowyt1dt2a6rb453zbylm8eq54/577_392_240cd750bba9870f18aada2478b24840a/N.webp'],
        ],
        'Триумфальный' => [
            ['id' => 'Участок №44', 'area' => '15.0', 'price' => '14850000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/4d8/ufw5muunhmmfirly90xkbv3bnnafte75/577_392_240cd750bba9870f18aada2478b24840a/N.webp'],
        ],
        'Calipso Village-2' => [
            ['id' => 'Участок №7', 'area' => '6.5', 'price' => '3282500', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/dd4/5e931vs55l2u100bts55q58w98yh4run/577_392_240cd750bba9870f18aada2478b24840a/N.webp'],
        ],
        'Новое Фелисово' => [
            ['id' => 'Участок №3', 'area' => '11.2', 'price' => '5320000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/968/218hfshw8bayvscbnv092mi60o5yyoyg/577_392_240cd750bba9870f18aada2478b24840a/N.webp'],
        ],
        'Лесной остров' => [
            ['id' => 'Участок №21', 'area' => '10.0', 'price' => '6250000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/0a5/80vxynj29y5foovkbw0lxa5ud4t6qjtf/577_392_240cd750bba9870f18aada2478b24840a/N.webp'],
        ]
    ],
    'altai' => [
        'Алтайская Ривьера' => [
            ['id' => 'Участок №А1', 'area' => '20.0', 'price' => '4600000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/974/ewif4q7s5yas3617j0qo0tuw4fac1pp8/577_392_240cd750bba9870f18aada2478b24840a/N.webp'],
        ]
    ],
    'nsk' => [
        'Сибирские Просторы' => [
            ['id' => 'Участок №Н1', 'area' => '12.5', 'price' => '3500000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/682/pw7fkgaowyt1dt2a6rb453zbylm8eq54/577_392_240cd750bba9870f18aada2478b24840a/N.webp'],
        ]
    ]
];

// 3. ПОСЕЛКИ
$settlements_data = [
    'mo' => [
        'Земельный экспресс' => [
            ['name' => 'РигаЛес', 'price' => '1499000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/020/irmqpnw2qji1oj7u84so5twm1c0bb77b/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Новорижское', 'mkad' => '45', 'ready' => '15'],
            ['name' => 'Каретный ряд', 'price' => '805000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/974/ewif4q7s5yas3617j0qo0tuw4fac1pp8/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Новорижское', 'mkad' => '45', 'ready' => '20'],
            ['name' => 'ПриЛесной', 'price' => '1360000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/682/pw7fkgaowyt1dt2a6rb453zbylm8eq54/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Дмитровское', 'mkad' => '27', 'ready' => '55'],
            ['name' => 'Триумфальный', 'price' => '990000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/4d8/ufw5muunhmmfirly90xkbv3bnnafte75/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Дмитровское', 'mkad' => '28', 'ready' => '60'],
            ['name' => 'Calipso Village-2', 'price' => '505000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/dd4/5e931vs55l2u100bts55q58w98yh4run/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Дмитровское', 'mkad' => '26', 'ready' => '95'],
            ['name' => 'Новое Фелисово', 'price' => '475000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/968/218hfshw8bayvscbnv092mi60o5yyoyg/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Дмитровское', 'mkad' => '28', 'ready' => '40'],
            ['name' => 'Лесной остров', 'price' => '625000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/0a5/80vxynj29y5foovkbw0lxa5ud4t6qjtf/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Симферопольское', 'mkad' => '36', 'ready' => '100'],
            ['name' => 'Фаворит', 'price' => '455000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/f12/nij13udbz1w3a9y107qnpp8oyej8jwd9/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '30', 'ready' => '100'],
            ['name' => 'Ильинское ИЖС', 'price' => '395000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/dcb/r2g4r9mrkmzc92r106nha0gw9pveei2c/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '30', 'ready' => '100'],
            ['name' => 'Рэд', 'price' => '490000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/837/kxbbehbm4dydjtmw9bjun1izb5al9ztz/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '35', 'ready' => '100'],
            ['name' => 'Новое Сонино', 'price' => '355000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/b95/h4ixif13khl1q0i8xj499ws6iw8bd2nb/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '37', 'ready' => '100'],
            ['name' => 'Династия', 'price' => '300000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/4d3/hck6sfutcq6m7ob1bfwl72ba4sn3qdgi/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '41', 'ready' => '100'],
            ['name' => 'Грибоедово', 'price' => '444000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/7e6/f8so4315zfwap34ejnliina0y1houcjx/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '38', 'ready' => '65'],
            ['name' => 'Новое Растуново', 'price' => '348000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/f41/up8u6ry291pot5zjylgwt1gaem8zwl0k/577_392_240cd750bba9870f18aada2478b24840a/Novoe-rastunovo.webp', 'highway' => 'Каширское', 'mkad' => '37', 'ready' => '60'],
            ['name' => 'Лазурный', 'price' => '430000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/2d1/7p8wljz02ayeexfcbi6jphasns3oce7s/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '38', 'ready' => '100'],
            ['name' => 'Артемово ИЖС', 'price' => '235000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/d86/le1a873ve82rcsff6219qz6uau3cbsy7/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '42', 'ready' => '60'],
            ['name' => 'Дачная Практика-2', 'price' => '390000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/dbe/mbjr27vni072tujhol5zwquetewtkbu1/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '42', 'ready' => '100'],
            ['name' => 'Минаево Парк', 'price' => '230000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/d61/8v53seipubmoevvsb6mphpvj9f2lv7sk/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '40', 'ready' => '60'],
            ['name' => 'Станционный ИЖС', 'price' => '420000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/722/u2pecd6m0b3io9ms1u2tyyohfomalpk7/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '38', 'ready' => '0'],
            ['name' => 'Есенино', 'price' => '310000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/5ba/wyiowu3cj4w7fjibrq5ph6dx3kkr65sg/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '38', 'ready' => '100'],
            ['name' => 'Бунино', 'price' => '248000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/2ce/vrpl0bu2tgqqotmucxlsztbkk0lxuovy/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '38', 'ready' => '100'],
            ['name' => 'Шишкино Лэнд', 'price' => '465000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/471/dli2rotjfspc35oieujntl3zo818fzru/577_392_240cd750bba9870f18aada2478b24840a/Zimniy-SHishkino-Lend.webp', 'highway' => 'Каширское', 'mkad' => '41', 'ready' => '60'],
            ['name' => 'Лидер', 'price' => '255000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/b1f/56o42rzd691muhf5v7urodrdhfay5dtt/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '40', 'ready' => '90'],
            ['name' => 'Растуново ИЖС', 'price' => '310000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/e6c/jxh404gj3725jmstpkdi5uohcg2bckmm/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '39', 'ready' => '70'],
            ['name' => 'Брусникино', 'price' => '240000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/93e/t8uyawade5b71psx5n4wkure1hcfelvl/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '39', 'ready' => '80'],
            ['name' => 'Суриково', 'price' => '187000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/65c/2bmh02kte2nrukmx236k1ilcplbpgg52/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '43', 'ready' => '0'],
            ['name' => 'Репино', 'price' => '205000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/e8e/7orezrl53aic4y00idiuzviv4gmztgp6/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '43', 'ready' => '85'],
            ['name' => 'Калина', 'price' => '205000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/330/etxi1cdexrp4on9b7gxhizjauvrlxrfo/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '44', 'ready' => '100'],
            ['name' => 'Земляничная поляна-3', 'price' => '191000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/cf8/l2owoyqzclk26uxq0o0eez9pbge4ni2i/577_392_240cd750bba9870f18aada2478b24840a/Zimnyaya-Zemlyanichnaya-polyana_3.webp', 'highway' => 'Каширское', 'mkad' => '43', 'ready' => '100'],
            ['name' => 'Минаево ИЖС', 'price' => '220000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/ee3/yaivgc4oafoo46pralw2t30ntwxnq81v/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '39', 'ready' => '100'],
            ['name' => 'Дивный', 'price' => '170000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/1c5/ojgxqqg4v46wdfcvduswwhyz0unbxkwp/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Симферопольское', 'mkad' => '50', 'ready' => '100'],
            ['name' => 'Светлый', 'price' => '190000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/6b7/v0lhedbds3u9omsjuzi9961xqkxtjvhi/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '49', 'ready' => '100'],
            ['name' => 'Регата', 'price' => '440000', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/f3d/4a1utjroywytdoe6wufhy9octwqmlfyq/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '38', 'ready' => '100'],
            ['name' => 'Фишер (ПРОДАНО)', 'price' => '0', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/879/u4r8ta4zas8casz0zghgeubtkdnzt331/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '38', 'ready' => '100'],
            ['name' => 'Земляничная поляна (ПРОДАНО)', 'price' => '0', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/9c9/lvrzuewa14734l08476wry4oo6i43o40/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '38', 'ready' => '100'],
            ['name' => 'Карамель (ПРОДАНО)', 'price' => '0', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/820/3xfjlzc7m4j42dld2ir04c7s2c27hcvu/577_392_240cd750bba9870f18aada2478b24840a/N.webp', 'highway' => 'Каширское', 'mkad' => '38', 'ready' => '100'],
            ['name' => 'Шелест (ПРОДАНО)', 'price' => '0', 'img' => $domain_zemexx.'/upload/dev2fun.imagecompress/webp/resize_cache/iblock/d7a/577_392_240cd750bba9870f18aada2478b24840a/97d466f89fcb98898a7cc9756e6fa928.webp', 'highway' => 'Каширское', 'mkad' => '38', 'ready' => '100']
        ]
    ]
];

// 4. ПРОЕКТЫ
$projects_data = [
    'mo' => [
        'ТопДом' => [
            ['name' => 'Проект 1-284', 'art' => 'Арт. 1-284', 'price' => '7832451', 'area' => '121', 'floors' => '1', 'bedrooms' => '3', 'bath' => '2', 'img' => 'https://xn--d1aqebdq.xn--p1ai/images/1-284/TOPDOM.RF-1-284_Main_1708430299_800.jpg'],
            ['name' => 'Проект 1-285', 'art' => 'Арт. 1-285', 'price' => '8026644', 'area' => '124', 'floors' => '1', 'bedrooms' => '3', 'bath' => '2', 'img' => 'https://xn--d1aqebdq.xn--p1ai/images/1-285/TOPDOM.RF-1-285_Main_1708430446_800.jpg'],
            ['name' => 'Проект 1-276', 'art' => 'Арт. 1-276', 'price' => '8026644', 'area' => '124', 'floors' => '1', 'bedrooms' => '3', 'bath' => '2', 'img' => 'https://xn--d1aqebdq.xn--p1ai/images/1-276/TOPDOM.RF-1-276_Main_1708427613_800.jpg'],
            ['name' => 'Проект 1-302', 'art' => 'Арт. 1-302', 'price' => '10300000', 'area' => '125', 'floors' => '1', 'bedrooms' => '3', 'bath' => '2', 'img' => 'https://xn--d1aqebdq.xn--p1ai/images/1-302/TOPDOM.RF-1-302_Main_1765458648_800.jpg'],
            ['name' => 'Проект 1-291', 'art' => 'Арт. 1-291', 'price' => '8220837', 'area' => '127', 'floors' => '1', 'bedrooms' => '3', 'bath' => '2', 'img' => 'https://xn--d1aqebdq.xn--p1ai/images/1-291/TOPDOM.RF-1-291_Main_1708511439_800.jpg'],
            ['name' => 'Проект 1-271', 'art' => 'Арт. 1-271', 'price' => '8383056', 'area' => '132', 'floors' => '1', 'bedrooms' => '2', 'bath' => '2', 'img' => 'https://xn--d1aqebdq.xn--p1ai/images/1-271/TOPDOM.RF-1-271_Main_1700837089_800.jpg'],
            ['name' => 'Проект 1-45', 'art' => 'Арт. 1-45', 'price' => '8446564', 'area' => '133', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://xn--d1aqebdq.xn--p1ai/images/1-45/TOPDOM.RF-1-45_Main_800.jpg'],
            ['name' => 'Проект 1-44', 'art' => 'Арт. 1-44', 'price' => '8954628', 'area' => '141', 'floors' => '1', 'bedrooms' => '4', 'bath' => '2', 'img' => 'https://xn--d1aqebdq.xn--p1ai/images/1-44/TOPDOM.RF-1-44_Main_800.jpg'],
        ],
        'FirstKey' => [
            ['name' => 'FK 80-01B Комб. штукатурка', 'art' => 'FK 80-01B', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor6664-3934-4835-a433-366531376465/66319018.jpg'],
            ['name' => 'FK 80-01B Светлая штукатурка', 'art' => 'FK 80-01B', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor3235-3230-4839-b963-373232333762/46873748.jpg'],
            ['name' => 'FK 80-01B Серый кирпич', 'art' => 'FK 80-01B', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor3333-3064-4466-a531-636631366636/61201618.jpg'],
            ['name' => 'FK 80-01D Графит', 'art' => 'FK 80-01D', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor3039-3566-4735-a365-333134666362/18208298.jpg'],
            ['name' => 'FK 80-01D Серый кирпич', 'art' => 'FK 80-01D', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor6135-6533-4031-b963-313039633835/50407585.jpg'],
            ['name' => 'FK 80-02B Бежевый кирпич', 'art' => 'FK 80-02B', 'price' => '6880000', 'area' => '80', 'floors' => '1', 'bedrooms' => '3', 'bath' => '1', 'img' => 'https://static.tildacdn.com/stor6266-3235-4961-b662-613431303563/21211448.jpg'],
            ['name' => 'FK 1001-01B Бежевый кирпич', 'art' => 'FK 1001-01B', 'price' => '8250000', 'area' => '100', 'floors' => '1', 'bedrooms' => '3', 'bath' => '2', 'img' => 'https://static.tildacdn.com/stor3937-3839-4862-a434-363137653534/83376456.jpg'],
            ['name' => 'FK 1001-01B Комб. кирпич', 'art' => 'FK 1001-01B', 'price' => '8250000', 'area' => '100', 'floors' => '1', 'bedrooms' => '3', 'bath' => '2', 'img' => 'https://static.tildacdn.com/stor6562-3965-4934-b564-643237393034/27280670.jpg'],
        ]
    ],
    'altai' => [
        'Сибирь-Проект' => [
            ['name' => 'Горный Проект', 'art' => 'Арт. А-10', 'price' => '6500000', 'area' => '115', 'floors' => '1', 'bedrooms' => '3', 'bath' => '2', 'img' => 'https://xn--d1aqebdq.xn--p1ai/images/1-230/TOPDOM.RF-1-230_Main_1681481716_800.jpg']
        ]
    ],
    'nsk' => [
        'НСК-Архитект' => [
            ['name' => 'Степной Проект', 'art' => 'Арт. Н-15', 'price' => '7200000', 'area' => '140', 'floors' => '2', 'bedrooms' => '4', 'bath' => '2', 'img' => 'https://xn--d1aqebdq.xn--p1ai/images/1-6/TOPDOM.RF-1-6_Main_800.jpg']
        ]
    ]
];

/**
 * Функция для получения данных объекта по ID и типу (новое поколение - использует статические данные)
 */
function getObjectDataFromRegistry($type, $id) {
    global $houses_data, $plots_data, $settlements_data, $projects_data;
    
    switch($type) {
        case 'house':
            foreach($houses_data as $region => $builders) {
                foreach($builders as $builder => $houses) {
                    foreach($houses as $index => $house) {
                        $uid = 'house_' . md5($region . '_' . $builder . '_' . $house['name'] . '_' . $index);
                        if($uid === $id) {
                            return array_merge($house, ['region' => $region, 'builder' => $builder, 'type_label' => 'Дом']);
                        }
                    }
                }
            }
            break;
            
        case 'project':
            foreach($projects_data as $region => $builders) {
                foreach($builders as $builder => $projects) {
                    foreach($projects as $index => $project) {
                        $uid = 'project_' . md5($region . '_' . $builder . '_' . $project['name'] . '_' . $index);
                        if($uid === $id) {
                            return array_merge($project, ['region' => $region, 'builder' => $builder, 'type_label' => 'Проект']);
                        }
                    }
                }
            }
            break;
            
        case 'settlement':
            foreach($settlements_data as $region => $sellers) {
                foreach($sellers as $seller => $settlements) {
                    foreach($settlements as $index => $settlement) {
                        $uid = 'settlement_' . md5($region . '_' . $seller . '_' . $settlement['name'] . '_' . $index);
                        if($uid === $id) {
                            return array_merge($settlement, ['region' => $region, 'seller' => $seller, 'type_label' => 'Поселок']);
                        }
                    }
                }
            }
            break;
            
        case 'land':
            foreach($plots_data as $region => $settlements) {
                foreach($settlements as $settlement => $plots) {
                    foreach($plots as $index => $plot) {
                        $uid = 'land_' . md5($region . '_' . $settlement . '_' . $plot['id'] . '_' . $index);
                        if($uid === $id) {
                            return array_merge($plot, ['name' => $plot['id'], 'region' => $region, 'settlement' => $settlement, 'type_label' => 'Участок']);
                        }
                    }
                }
            }
            break;
    }
    
    return null;
}
