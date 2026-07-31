# Журнал изменений

## 26.11.2025

### Замена жестко закодированных доменов

В рамках задачи по улучшению гибкости конфигурации все жестко закодированные домены `doc.m2profi.pro`, `m2profi.pro`, `em.m2profi.pro`, `em-nsk.ru`, `gl.m2profi.pro`, `msk.m2profi.pro` были заменены на соответствующие переменные из конфигурационного файла.

#### Затронутые файлы:

1.  `sahmatka/fw/templates/landplots/map_js_new.php`
    *   Строка 35: `http://em.m2profi.pro` заменен на `<?=$GLOBALS['config']['domains']['em']?>`
    *   Строка 36: `http://em.m2profi.pro` заменен на `<?=$GLOBALS['config']['domains']['em']?>`

2.  `sahmatka/fw/templates/landplots/map_js.php`
    *   Строка 35: `http://em.m2profi.pro` заменен на `<?=$GLOBALS['config']['domains']['em']?>`
    *   Строка 36: `http://em.m2profi.pro` заменен на `<?=$GLOBALS['config']['domains']['em']?>`

3.  `sahmatka/fw/templates/rentobjects/cat_one_item.php`
    *   Строка 25: `https://em-nsk.ru` заменен на `<?=$GLOBALS['config']['domains']['em_nsk']?>`

4.  `sahmatka/fw/templates/parking_spaces/form_broni_pub.php`
    *   Строка 196: `href="https://em-nsk.ru/exc.php"` заменен на `href="<?=$GLOBALS['config']['domains']['em_nsk']?>/exc.php"`

5.  `sahmatka/fw/templates/parking_spaces/form_broni_ag.php`
    *   Строка 196: `href="http://em-nsk.ru/sahmatka/reglament.php"` заменен на `href="<?=$GLOBALS['config']['domains']['em_nsk']?>/sahmatka/reglament.php"`

6.  `sahmatka/fw/templates/parking_floors/style_floor_display.php`
    *   Строка 46: `url('https://em.m2profi.pro/sahmatka/parking/car_g.png')` заменен на `url('<?=$GLOBALS['config']['domains']['em']?>/sahmatka/parking/car_g.png')`
    *   Строка 47: `url('https://em.m2profi.pro/sahmatka/parking/car_r.png')` заменен на `url('<?=$GLOBALS['config']['domains']['em']?>/sahmatka/parking/car_r.png')`
    *   Строка 48: `url('https://em.m2profi.pro/sahmatka/parking/car_y.png')` заменен на `url('<?=$GLOBALS['config']['domains']['em']?>/sahmatka/parking/car_y.png')`
    *   Строка 49: `url('https://em.m2profi.pro/sahmatka/parking/car_f.png')` заменен на `url('<?=$GLOBALS['config']['domains']['em']?>/sahmatka/parking/car_f.png')`
    *   Строка 50: `url('https://em.m2profi.pro/sahmatka/parking/car_b.png')` заменен на `url('<?=$GLOBALS['config']['domains']['em']?>/sahmatka/parking/car_b.png')`

7.  `sahmatka/fw/controllers/ctr__zapisx.php`
    *   Строка 441: `multi_attach_mail('89236470002@mail.ru', 'Запись на выдачу ключей - дом:'.$this->homes_arr[$data['home_id']]['title'].' кв.:'.$data['apartment_num'].' дата:'.$_POST['date'].' время:'.$data['time'], $con, 'admin@m2profi.pro', 'em-nsk.ru');` заменен на `multi_attach_mail('89236470002@mail.ru', 'Запись на выдачу ключей - дом:'.$this->homes_arr[$data['home_id']]['title'].' кв.:'.$data['apartment_num'].' дата:'.$_POST['date'].' время:'.$data['time'], $con, 'admin@<?=$GLOBALS['config']['domains']['main']?>', '<?=$GLOBALS['config']['domains']['em_nsk']?>');`
    *   Строка 442: `multi_attach_mail($data['email'], 'Запись на выдачу ключей - дом:'.$this->homes_arr[$data['home_id']]['title'].' кв.:'.$data['apartment_num'].' дата:'.$_POST['date'].' время:'.$data['time'], $con, 'admin@m2profi.pro', 'em-nsk.ru');` заменен на `multi_attach_mail($data['email'], 'Запись на выдачу ключей - дом:'.$this->homes_arr[$data['home_id']]['title'].' кв.:'.$data['apartment_num'].' дата:'.$_POST['date'].' время:'.$data['time'], $con, 'admin@<?=$GLOBALS['config']['domains']['main']?>', '<?=$GLOBALS['config']['domains']['em_nsk']?>');`
    *   Строка 443: `multi_attach_mail('op15@em-nsk.group', 'Запись на выдачу ключей - дом:'.$this->homes_arr[$data['home_id']]['title'].' кв.:'.$data['apartment_num'].' дата:'.$_POST['date'].' время:'.$data['time'], $con, 'admin@m2profi.pro', 'em-nsk.ru');` заменен на `multi_attach_mail('op15@<?=$GLOBALS['config']['domains']['em_nsk_group']?>', 'Запись на выдачу ключей - дом:'.$this->homes_arr[$data['home_id']]['title'].' кв.:'.$data['apartment_num'].' дата:'.$_POST['date'].' время:'.$data['time'], $con, 'admin@<?=$GLOBALS['config']['domains']['main']?>', '<?=$GLOBALS['config']['domains']['em_nsk']?>');`
    *   Строка 960: `action="https://em.m2profi.pro/sahmatka/ajax_router.php?ctr=zapiskeys&act=zapisformx"` заменен на `action="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapiskeys&act=zapisformx"`
    *   Строка 961: `src="http://em.m2profi.pro/sahmatka/loader.gif"` заменен на `src="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/loader.gif"`
    *   Строка 1082: `url: "https://em.m2profi.pro/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis"` заменен на `url: "<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis"`
    *   Строка 1091: `url: "https://em.m2profi.pro/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis"` заменен на `url: "<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis"`
    *   Строка 1101: `url: "https://em.m2profi.pro/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis"` заменен на `url: "<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis"`
    *   Строка 1113: `url: "https://em.m2profi.pro/sahmatka/ajax_router.php?ctr=zapisx&act=sel_section_zapis"` заменен на `url: "<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_section_zapis"`
    *   Строка 1123: `url: "https://em.m2profi.pro/sahmatka/ajax_router.php?ctr=zapisx&act=sel_apartament_zapis"` заменен на `url: "<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_apartament_zapis"`
    *   Строка 1134: `url: "https://em.m2profi.pro/sahmatka/ajax_router.php?ctr=zapisx&act=sel_date_zapisx"` заменен на `url: "<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_date_zapisx"`
    *   Строка 1147: `url: "https://em.m2profi.pro/sahmatka/ajax_router.php?ctr=zapisx&act=sel_time_zapisx"` заменен на `url: "<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_time_zapisx"`

8.  `sahmatka/actions/docs.php`
    *   Строка 121: `href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/dogovor.docx?x=1"` заменен на `href="<?=$GLOBALS['config']['domains']['gl']?>/doc/reglament.php?q=/doc/dogovor.docx?x=1"`
    *   Строка 127: `href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/dog_reg.docx"` заменен на `href="<?=$GLOBALS['config']['domains']['gl']?>/doc/reglament.php?q=/doc/dog_reg.docx"`
    *   Строка 136: `href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/bron.docx"` заменен на `href="<?=$GLOBALS['config']['domains']['gl']?>/doc/reglament.php?q=/doc/bron.docx"`
    *   Строка 142: `href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/uvedomlenie.docx?x=1"` заменен на `href="<?=$GLOBALS['config']['domains']['gl']?>/doc/reglament.php?q=/doc/uvedomlenie.docx?x=1"`
    *   Строка 148: `href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/anket.xlsx"` заменен на `href="<?=$GLOBALS['config']['domains']['gl']?>/doc/reglament.php?q=/doc/anket.xlsx"`

9.  `sahmatka/user.php`
    *   Строка 9: `Location: https://doc.m2profi.pro/` заменен на `Location: <?=$GLOBALS['config']['domains']['doc']?>/`
    *   Строка 22: `window.location.href = 'https://doc.m2profi.pro/sahmatka/ctrind.php?ctr=doc&act=index'` заменен на `window.location.href = '<?=$GLOBALS['config']['domains']['doc']?>/sahmatka/ctrind.php?ctr=doc&act=index'`
    *   Строка 27: `header("Location: https://doc.m2profi.pro/sahmatka/ctrind.php?ctr=doc&act=index");` заменен на `header("Location: <?=$GLOBALS['config']['domains']['doc']?>/sahmatka/ctrind.php?ctr=doc&act=index");`
    *   Строка 513: `href="https://gl.m2profi.pro/sahmatka/ctrind.php?ctr=landplots&act=index"` заменен на `href="<?=$GLOBALS['config']['domains']['gl']?>/sahmatka/ctrind.php?ctr=landplots&act=index"`

10. `sahmatka/incudes_/header.php`
    *   Строка 182: `href="https://m2profi.pro"` заменен на `href="<?=$GLOBALS['config']['domains']['main']?>"`

#### Конфигурационный файл

В файл `sahmatka/config.php` были добавлены следующие переменные:

```php
$GLOBALS['config']['domains']['em'] = 'https://em.m2profi.pro';
$GLOBALS['config']['domains']['em_nsk'] = 'https://em-nsk.ru';
$GLOBALS['config']['domains']['em_nsk_group'] = 'https://em-nsk.group';
$GLOBALS['config']['domains']['gl'] = 'https://gl.m2profi.pro';
$GLOBALS['config']['domains']['msk'] = 'https://msk.m2profi.pro';
$GLOBALS['config']['domains']['doc'] = 'https://doc.m2profi.pro';
$GLOBALS['config']['domains']['main'] = 'https://m2profi.pro';