  <?
 // print '<pre>';
  //print_r($data);
//  print '</pre>';
  
  $price = $value = number_format($data['data']['price'], 0, '.', ' ');
  
  $price = '';
  
  $status =  $data['data']['status'];
  
  // Публичный статус
  if($status=='5' || $status=='6')
  {
	  $status =4; 
  }
  
  
  if( !$status ){ $status = "2"; }
   
  $o1 = (int)($data['apartment']['window_orient_1'] ?? $data['data']['window_orient_1'] ?? 0);
  $o2 = (int)($data['apartment']['window_orient_2'] ?? $data['data']['window_orient_2'] ?? 0);
  $html_compass = render_window_compass_images($o1, $o2, 110);
   
  ?>

  <link rel="stylesheet" href="/sahmatka/template/default/css/sun_path.css?v=20">
  
  
  <style>
  
  
:root {
    --accent: #E30613;
    --green: #26b99a;
    --dark: #17233c;
    --dgray: #616161;
    --lpink: #f7e8e8;
    --beige: #c4a9a9;
    --hover: #FFF;
    --white: #fff;
    --black: #000;
    --placeholder: #666;
    --fontsize: 16px;
    --lineheight: 1.3;
    --mainfont: "Exo 2", "Exo2", sans-serif;
    --secfont: "Exo 2", "Exo2", sans-serif;
    --systemfont: -apple-system, BlinkMacSystemFont, Arial, sans-serif;
    --anim100: .10s ease-out;
    --anim150: .15s ease-out;
    --anim300: .3s ease-out;
}
/* iframe: десктоп — на всю высоту модалки; мобилка — обычный поток */
html, body {
  margin: 0;
  overflow-x: hidden;
}
@media (min-width: 1024px) {
  html, body {
    height: 100%;
    overflow: hidden;
  }
}
	@media print {
  .no-print {
    display: none !important;
  }
}

 

 


.mdl {
  width: 100%;
  max-width: 1254px;
  /* равные отступы со всех сторон */
  padding: 16px;
  background: #fff !important;
  box-sizing: border-box;
  height: auto;
  min-height: 0;
  overflow: visible;
}
@media (min-width: 1024px) {
  .mdl {
    height: 100% !important;
    min-height: 100% !important;
    max-height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
}
@media (max-width: 1023.98px) {
  .mdl {
    padding: 12px;
  }
}
@media (max-width: 767.98px) {
  .mdl {
    padding: 10px;
  }
}
.mdl-inner {
  display: -ms-grid;
  display: grid;
  -ms-grid-columns: 370px 20px 1fr;
  grid-template-columns: 370px 1fr;
  grid-column-gap: 20px;
  align-items: stretch;
  height: auto;
  min-height: 0;
  overflow: visible;
}
@media (min-width: 1024px) {
  .mdl-inner {
    flex: 1 1 auto;
    height: 100% !important;
    min-height: 0 !important;
    overflow: hidden;
  }
}
/* типографика Exo 2: заголовок 24px, остальное пропорционально */
.mdl {
  font-family: var(--mainfont);
}
/* серая плашка — на всю высоту ряда (как правая колонка) */
.mdl-aside {
  -ms-grid-column: 1;
  grid-column: 1;
  -ms-grid-row: 1;
  grid-row: 1;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: stretch;
  gap: 0;
  min-width: 0;
  height: auto;
  min-height: 0;
  align-self: stretch;
  background: #F0F0F0;
  border-radius: 16px;
  padding: 16px;
  box-sizing: border-box;
  overflow: auto;
  font-family: var(--mainfont);
  font-size: 16px;
  line-height: 1.4;
  color: var(--dark);
}
@media (min-width: 1024px) {
  .mdl-aside {
    height: 100%;
    overflow: auto;
  }
}
/* правая панель */
.mdl-body {
  -ms-grid-column: 3;
  grid-column: 2;
  -ms-grid-row: 1;
  grid-row: 1;
  padding: 16px 20px;
  border-left: none;
  background: #fff;
  border-radius: 16px;
  box-sizing: border-box;
  min-width: 0;
  align-self: stretch;
  overflow: visible;
}
@media (min-width: 1024px) {
  .mdl-body {
    height: 100%;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
}
@media (max-width: 1023.98px) {
  .mdl-inner {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
        -ms-flex-direction: column;
            flex-direction: column;
    gap: 16px;
  }
  /* мобилка: заголовок → планировка → форма (десктоп без изменений) */
  .mdl-aside {
    display: contents;
  }
  .mdl-head {
    -webkit-box-ordinal-group: 2;
        -ms-flex-order: 1;
            order: 1;
    padding: 0;
    background: transparent;
  }
  .mdl-body {
    -webkit-box-ordinal-group: 3;
        -ms-flex-order: 2;
            order: 2;
    -ms-grid-column: 1;
    grid-column: auto;
    grid-row: auto;
    padding: 0;
    border-radius: 0;
    background: transparent;
  }
  .mdl-foot {
    -webkit-box-ordinal-group: 4;
        -ms-flex-order: 3;
            order: 3;
    margin-top: 0 !important;
    padding: 16px;
    background: #F0F0F0;
    border-radius: 14px;
  }
}
@media (max-width: 767.98px) {
  .mdl-inner {
    gap: 14px;
  }
  .mdl-foot {
    padding: 12px;
    border-radius: 12px;
  }
}
.mdl-head {
  margin-bottom: 0;
  background: transparent;
  border-radius: 0;
  padding: 0 0 8px;
  flex: 0 0 auto;
}
@media (max-width: 767.98px) {
  .mdl-head {
    margin-bottom: 0;
  }
}
.mdl-head-row {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  -webkit-box-pack: justify;
      -ms-flex-pack: justify;
          justify-content: space-between;
  gap: 15px;
  margin-bottom: 20px;
}
@media (max-width: 1023.98px) {
  .mdl-head-row {
    gap: 38px;
  }
}
@media (max-width: 767.98px) {
  .mdl-head-row {
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
        -ms-flex-direction: column;
            flex-direction: column;
    -webkit-box-align: start;
        -ms-flex-align: start;
            align-items: flex-start;
    gap: 25px;
    margin-bottom: 32px;
  }
}
.mdl-head-group {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  -webkit-box-pack: justify;
      -ms-flex-pack: justify;
          justify-content: space-between;
  gap: 15px;
  -webkit-box-flex: 1;
      -ms-flex-positive: 1;
          flex-grow: 1;
}
@media (max-width: 767.98px) {
  .mdl-head-group {
    width: 100%;
  }
}
.mdl-back {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  -webkit-box-pack: center;
      -ms-flex-pack: center;
          justify-content: center;
  width: 235px;
  height: 31px;
  font-family: var(--mainfont);
  font-size: 14px;
  font-weight: 600;
  line-height: 1;
  text-align: center;
  color: #000;
  border-radius: 15px;
 border:1px solid #E30613;
  background: #FFF;
}
@media (max-width: 767.98px) {
  .mdl-back {
    width: 180px;
    height: 24px;
    font-size: 12px;
  }
}
.mdl-dnd {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  gap: 15px;
}
.mdl-dnd__item {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  -webkit-box-pack: center;
      -ms-flex-pack: center;
          justify-content: center;
  -ms-flex-negative: 0;
      flex-shrink: 0;
  width: 40px;
  height: 40px;
  cursor: pointer;
  border-radius: 50%;
  border: 2px solid #E30613;
}
.mdl-title {
  margin-bottom: 10px;
  font-family: var(--mainfont);
  font-size: 24px;
  font-weight: 600;
  line-height: 1.35;
  color: var(--dark);
}
@media (max-width: 767.98px) {
  .mdl-title {
    max-width: 300px;
    margin-bottom: 16px;
    font-size: 20px;
    line-height: 1.25;
  }
}
.mdl-prm {
  font-family: var(--mainfont);
  font-size: 30px;
  font-weight: 600;
  line-height: 1.2;
  color: var(--dark);
}
@media (max-width: 767.98px) {
  .mdl-prm {
    font-size: 26px;
  }
}
.mdl-form {
  margin-top: 9px;
}
@media (max-width: 1023.98px) {
  .mdl-form-row {
    display: -ms-grid;
    display: grid;
    -ms-grid-columns: 1fr 17px 1fr;
    grid-template-columns: 1fr 1fr;
    grid-column-gap: 17px;
  }
}
@media (max-width: 767.98px) {
  .mdl-form-row {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
        -ms-flex-direction: column;
            flex-direction: column;
  }
}
.mdl-form-field {
  margin-bottom: 8px;
}
.mdl-form-field input,
.mdl-form-field textarea {
  display: block;
  width: 100%;
  padding: 8px 12px;
  font-family: var(--mainfont);
  font-size: 14px;
  font-weight: 400;
  color: var(--dark);
  border: 1px solid #E30613;
  border-radius: 10px;
  background: #fff !important;
}
.mdl-form-field input:focus,
.mdl-form-field textarea:focus {
  outline: none;
}
.mdl-form-field input {
  height: 36px;
}
.mdl-form-field textarea {
  height: 88px;
  padding-top: 10px;
}
@media (max-width: 1023.98px) {
  .mdl-form-field textarea {
    height: 80px;
  }
}
@media (max-width: 767.98px) {
  .mdl-form-field textarea {
    height: 88px;
  }
}
.mdl-form__btn {
  width: 100%;
}
@media (max-width: 1023.98px) {
  .mdl-form__btn {
    margin-top: 8px;
  }
}
@media (max-width: 767.98px) {
  .mdl-form__btn {
    margin-top: 0;
  }
}
.mdl-form__accept {
  margin-top: 10px;
  margin-bottom: 0;
  padding-bottom: 2px;
  font-family: var(--mainfont);
  font-size: 10px;
  font-weight: 300;
  line-height: 1.4;
  color: var(--dark);
  overflow: visible;
  white-space: normal;
}
.mdl-form__accept a {
  text-decoration: underline;
  -webkit-text-decoration-skip-ink: none;
          text-decoration-skip-ink: none;
}
.mdl-form__accept a:hover {
  text-decoration: none;
  color: var(--accent);
}
@media (max-width: 1023.98px) {
  .mdl-body {
    -ms-grid-column: 1;
    grid-column: auto;
    -ms-grid-row: auto;
    -ms-grid-row-span: 1;
    grid-row: auto;
    border: none;
  }
}
.mdl-body-top {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  -webkit-box-pack: justify;
      -ms-flex-pack: justify;
          justify-content: space-between;
  gap: 15px;
  margin-bottom: 16px;
}
@media (max-width: 1199.98px) {
  .mdl-body-top {
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
        -ms-flex-direction: column;
            flex-direction: column;
    -webkit-box-align: start;
        -ms-flex-align: start;
            align-items: flex-start;
  }
}
.mdl-tabs {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  gap: 20px;
}
@media (max-width: 767.98px) {
  .mdl-tabs {
    gap: 10px;
    width: 100%;
  }
}
.mdl-tabs__item {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  -webkit-box-pack: center;
      -ms-flex-pack: center;
          justify-content: center;
  width: 153px;
  height: 31px;
  padding: 5px 5px 7px;
  cursor: pointer;
  white-space: nowrap;
  font-size: 16px;
  font-weight: 600;
  line-height: 1;
  text-align: center;
  color: #000;
  font-family: var(--mainfont);
  -webkit-transition: background var(--anim100), border-color var(--anim100);
  transition: background var(--anim100), border-color var(--anim100);
  border: 1px solid #e30613;
  border-radius: 15px;
  background: #fff;
}
@media (max-width: 767.98px) {
  .mdl-tabs__item {
    width: 100%;
    max-width: 108px;
    font-size: 14px;
  }
}
/* макет: активный — светло-розовый фон, красная рамка как у остальных */
.mdl-tabs__item.active {
  background: var(--lpink);
  color: #000;
  border-color: #e30613;
}
@media (max-width: 1023.98px) {
  .mdl-logo {
    display: none;
  }
}
.mdl-logo_mob {
  display: none;
}
@media (max-width: 1023.98px) {
  .mdl-logo_mob {
    display: block;
    margin-left: auto;
  }
}
@media (max-width: 767.98px) {
  .mdl-logo_mob {
    margin-left: 0;
  }
}
.mdl-main {
  position: relative;
  padding-bottom: 8px;
}
@media (max-width: 1023.98px) {
  .mdl-main {
    padding: 70px 0 16px;
  }
}
@media (max-width: 767.98px) {
  .mdl-main {
    padding: 60px 0 16px;
  }
}
.mdl-compas {
  position: absolute;
  top: 0;
  left: -19px;
  width: auto;
  max-width: 240px;
  z-index: 5;
}
.mdl-compas img,
.mdl-compas .window-compass-img {
  display: block;
  max-width: 100%;
  max-height: 100%;
}
.mdl-compas .window-compass-list {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}
@media (max-width: 1023.98px) {
  .mdl-compas {
    top: 20px;
  }
}
@media (max-width: 767.98px) {
  .mdl-compas {
    top: -18px;
    left: -10px;
    width: 95px;
  }
}
.mdl-pln {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
      -ms-flex-direction: column;
          flex-direction: column;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  gap: 28px;
}
@media (max-width: 767.98px) {
  .mdl-pln {
    gap: 10px;
  }
}
.mdl-pln img {
  display: block;
  max-width: 100%;
  max-height: 100%;
}
.mdl-pln__top {
  margin-bottom: 12px;
}
@media (max-width: 767.98px) {
  .mdl-pln__top {
    margin-bottom: 20px;
  }
}
.mdl-pln__top, .mdl-pln__bottom {
  font-size: 16px;
  font-weight: 300;
  text-align: center;
  color: var(--dark);
}
.mdl-map {
  height: 381px;
}
.mdl-map iframe {
  width: 100% !important;
  height: 100% !important;
}
.mdl-desc {
  margin-top: 8px;
  margin-bottom: 12px;
  font-family: var(--mainfont);
  font-size: 16px;
  font-weight: 600;
  line-height: 1.45;
  color: var(--dark);
}
.mdl-desc p {
  margin: 0;
  font-size: inherit;
  font-weight: inherit;
  line-height: inherit;
  font-family: inherit;
}
.mdl-price {
  font-family: var(--mainfont);
  font-size: 30px;
  font-weight: 600;
  color: var(--dark);
}
.mdl-status {
  font-family: var(--mainfont);
  font-size: 14px;
  font-weight: 700;
  line-height: 1.3;
  padding: 6px 8px;
  text-align: center;
  /* на десктопе статус+форма внизу, инфо (Дом/Секция…) остаётся сверху */
  margin-top: auto;
}
.mdl-cnt {
  display: none;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
      -ms-flex-direction: column;
          flex-direction: column;
  gap: 20px;
  font: 400 16px/1.9375 var(--mainfont);
}
.mdl-cnt p {
  margin: 0;
  font-size: inherit;
  font-weight: inherit;
  line-height: inherit;
  font-family: inherit;
  color: var(--dark);
}
.mdl-cnt span {
  font-size: 20px;
  font-weight: 600;
  text-decoration: underline;
  -webkit-text-decoration-skip-ink: none;
          text-decoration-skip-ink: none;
}
.mdl-logo-print {
  display: none;
}













.h-btn {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
      -ms-flex-align: center;
          align-items: center;
  -webkit-box-pack: center;
      -ms-flex-pack: center;
          justify-content: center;
  height: 48px;
  padding: 3px 15px 5px;
  -webkit-transition: color var(--anim150), background var(--anim150);
  transition: color var(--anim150), background var(--anim150);
  font-family: var(--mainfont);
  font-size: 18px;
  font-weight: 600;
  text-align: center;
  color: var(--white);
  cursor: pointer;
  white-space: nowrap;
  border: none;
  border-radius: 15px;
  background: var(--accent);
}
.h-btn:hover {
  color: var(--accent);
  background: var(--hover);
  border:solid 1px var(--accent);
  
}
.h-btn_light {
  color: var(--dark);
  background: var(--lpink);
}
.h-btn_green {
  color: var(--white);
  background: var(--green);
}
.h-btn_bd {
  color: var(--dark);
  border: 2px solid var(--accent);
  background: none;
}
.h-btn_sm {
  height: 27px;
  padding: 2px 11px 5px;
  font-size: 12px;
  border-radius: 15px;
}

 
[data-tabs-content] {
  display: none;
}
[data-tabs-content].open {
  display: block;
}
@media (min-width: 1024px) {
  [data-tabs-content].open {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
  }
  .mdl-body-top {
    flex: 0 0 auto;
  }
  .mdl-main {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
  .mdl-pln {
    flex: 1 1 auto;
    min-height: 0;
    width: 100%;
    justify-content: center;
    gap: 12px;
  }
  .mdl-pln__top,
  .mdl-pln__bottom {
    flex: 0 0 auto;
  }
  .mdl-pln .plan-with-sun {
    flex: 1 1 auto;
    min-height: 0;
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
  }
  .mdl-pln .plan-with-sun__frame {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .mdl-pln .plan-with-sun__img {
    max-height: 100% !important;
    width: auto !important;
    max-width: 100% !important;
  }
}

[data-tabs-target].active {
  pointer-events: none;
}



.mdl-foot {
  background: transparent;
  border-radius: 0;
  padding: 0;
  margin-top: 0 !important;
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  min-height: 0;
}
@media (max-width: 1023.98px) {
  .mdl-foot {
    flex: 0 0 auto;
  }
  .mdl-status {
    margin-top: 8px;
  }
}

  </style>
  
  <div class="mdl" id="modal-sale">
  <div class="mdl-inner" id="printableArea">
    <div class="mdl-aside">
    <div class="mdl-head">
      <div class="mdl-logo-print">
        <img src="images/logo.svg" alt="" />
      </div>
      <div class="mdl-head-row no-print">
        <div class="mdl-head-group">
    <a href="#" class="mdl-back" onclick="window.parent.postMessage('close-fancybox', '*'); return false;">Назад к выбору квартир</a>
          <div class="mdl-dnd">
            <div class="mdl-dnd__item" id="printButton">
              <img class="lazy" data-src="https://m2profi.pro/images/icons/print.svg" src="https://m2profi.pro/images/icons/print.svg" alt="" />
            </div>
            <a href="#" style="display:none" class="mdl-dnd__item" target="_blank" download>
              <img class="lazy" data-src="https://m2profi.pro/images/icons/pdf.svg"  src="https://m2profi.pro/images/icons/pdf.svg" alt="" />
            </a>
          </div>
        </div>
        <div class="mdl-logo mdl-logo_mob">
          <img style="    display: none;" class="lazy" data-src="https://m2profi.pro/images/logo.svg" src="https://m2profi.pro/images/logo.svg" alt="" />
        </div>
      </div>
      <div class="mdl-title no-print">Заявка на бронирование квартиры</div>
      <div class="mdl-prm"><?=$data['data']['rooms']?> | <?=$data['data']['area']?> м<sup>2</sup></div>
    </div>
    <div class="mdl-foot">
      <div class="mdl-desc">
        <p><?=$data['data']['kvartal_title']?></p>
        <p>Дом: <?=$data['data']['title']?></p>
        <p>Секция:  <?=$data['data']['section_id']?></p>
        <p>Этаж:  <?=$data['data']['floor']?></p>
        <p>Квартира: №<?=$data['data']['apartment_num']?></p>
      </div>
      <div class="mdl-price" style="display:none;"><?=$price?> ₽</div>
      <div class="mdl-cnt">
        <p>
          ОТДЕЛ ПРОДАЖ<br>
          <span>+7 (383) 347-47-00</span>
        </p>
        <p>
          г. Новосибирск, ул.Тюленина, д. 26<br>
          e-mail: op@em-nsk.group<br>
          1 этаж, каб 102-103
        </p>
        <p>
          Режим работы:<br>
          понедельник — пятница:<br>
          с 9-00 до 20-00 (без перерыва)<br>
          суббота — воскресенье:<br>
          с 9-00 до 18-00 (без перерыва)
        </p>
      </div>
		<?
	
		
		?>
	  <div class="mdl-status" style="background:<?=$GLOBALS['status_color_arr'][$status];?>">Статус: <?=$GLOBALS['status_arr'][$status];?></div>
 
	 
	  <?
	  if(!$status || $status=="2") // Только для свободных квартир 
	  {
		  ?>
		  <form action="https://em.m2profi.pro/sahmatka/ajax_router.php?ctr=apartments&act=card_ajaxform" method="POST"  data-fp-id="public_card" class="formprotect mdl-form no-print  ">
		  <input type="hidden" name="home" value="<?=$data['data']['title']?>" />
		  <input type="hidden" name="section_caption" value="<?=$data['data']['section_caption']?>" />
		  <input type="hidden" name="apartment_num" value="<?=$data['data']['apartment_num']?>" />
		  <input type="hidden" name="kvartal_title" value="<?=$data['data']['kvartal_title']?>" />
		 
		  
			<div class="mdl-form-row">
			  <div class="mdl-form-fieldset">
				<div class="mdl-form-field">
				  <input  name="fio" type="text" placeholder="ФИО" required>
				</div>
				<div class="mdl-form-field">
				  <input name="phone"  type="tel" placeholder="Номер телефона для  связи" required>
				</div>
			  </div>
			  <div class="mdl-form-field">
				<textarea name="message" placeholder="Ваши вопросы и пожелания"></textarea>
			  </div>
			</div>
			
			
			<?= FormProtect::hiddenFields('public_card') ?>
				
			<!-- Контейнер для CAPTCHA (не обязательно добавлять вручную) -->
			<!-- Если добавить, используйте класс fp-captcha-box -->
			<div class="fp-captcha-box"></div>

			<!-- Контейнер для общих ошибок (не обязательно добавлять вручную) -->
			<!-- Если добавить, используйте класс fp-general-errors -->
			<div class="fp-general-errors"></div>

			<!-- Контейнер для сообщения об успехе (не обязательно добавлять вручную) -->
			<!-- Если добавить, используйте класс fp-success-message -->
			<div class="fp-success-message" style="display:none;"></div>



		
				
			<button class="mdl-form__btn h-btn" type="submit">Забронировать</button>
			 
			<div class="mdl-form__accept">
			  Нажимая кнопку "Отправить", вы подтверждаете свое согласие на обработку <a href="https://em-nsk.ru/content/sitedoc/agreement.docx" target="_blank">персональных данных</a> и получение рекламных
			  рассылок.
			</div>
		  </form>
		  <?
	  }
	  ?>
	  
	  
    </div>
    </div>
    <div class="mdl-body" data-tabs="mdl">
      <div class="mdl-body-top no-print">
        <div class="mdl-tabs" data-tabs-nav>
          <div class="mdl-tabs__item active" data-tabs-target="tab-1">Планировка</div>
          <div style="<? if(!$data['data']['image_pb_plan']){?>display:none;<?}?>" class="mdl-tabs__item" data-tabs-target="tab-2">На этаже</div>
          <div style="display:none;" class="mdl-tabs__item" data-tabs-target="tab-3">На карте</div>
        </div>
        <div class="mdl-logo">
          <img style=" display: none;" class="lazy" data-src="https://m2profi.pro/images/logo.svg" src="https://m2profi.pro/images/logo.svg" alt="" />
        </div>
      </div>
	  
	  
      <div class="mdl-content open" data-tabs-content="tab-1">
        <div class="mdl-main">
          <?php if ($html_compass): ?>
          <div class="mdl-compas">
            <?= $html_compass ?>
          </div>
          <?php endif; ?>
          <div class="mdl-pln">
            <div class="mdl-pln__top"><?=$data['data']['homes_kvartal_title']?>  </div>
            <?= render_plan_with_sun(
                $data['data']['image_pb'] ?? '',
                $o1,
                $o2,
                ['img_style' => 'max-height:100%;max-width:100%;width:auto;height:auto;']
            ) ?>
            <div class="mdl-pln__bottom"><?=$data['data']['adress']?> </div>
          </div>
        </div>
      </div> 
	  
	  
      <div class="mdl-content" data-tabs-content="tab-2">
        <div class="mdl-main">
          <?php if ($html_compass): ?>
          <div class="mdl-compas">
            <?= $html_compass ?>
          </div>
          <?php endif; ?>
          <div class="mdl-pln">
            <div class="mdl-pln__top"><?=$data['data']['homes_kvartal_title']?></div>
            <img class="lazy" data-src="<?=$data['data']['image_pb_plan']?>" src="<?=$data['data']['image_pb_plan']?>" data-srcset="<?=$data['data']['image_pb_plan']?>" alt="" />
            <div class="mdl-pln__bottom"><?=$data['data']['adress']?>    </div>
          </div>
        </div>
      </div>
	  
	  
      <div class="mdl-content" data-tabs-content="tab-3">
        <div class="mdl-main">
          <div class="mdl-map">Карта 
            <script type="text/javascript" charset="utf-8" async
              src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A6c7e8976042bdf485f0e95adb683c87f3b44ec85d858b5593ec196ae3a8233be&amp;width=100%25&amp;height=400&amp;lang=ru_RU&amp;scroll=true"></script>
          </div>
        </div>
      </div>
	  
	  
    </div>
  </div>
</div>



<script>

 
 
document.getElementById('printButton').addEventListener('click', function(e) {
    e.preventDefault(); // Предотвращаем переход по ссылке

    const divId = 'printableArea'; // ID области для печати

    if (/iPhone|iPad/i.test(navigator.userAgent)) {
        printIOS(divId); // Вызов iOS-печати
    } else {
        document.body.classList.add('print-mode');
        window.print();
        setTimeout(() => document.body.classList.remove('print-mode'), 500);
    }
});

 class Tabs {
    constructor(selector) {
      const tabs = document.querySelectorAll(`[data-tabs="${selector}"]`);

      if (tabs.length) {
        tabs.forEach(element => {
          const tabsNav = element.querySelector('[data-tabs-nav]');
          const tabsNavItem = tabsNav.querySelectorAll('[data-tabs-target]');

          tabsNavItem.forEach(el => {
            el.addEventListener('click', (e) => {
              e.preventDefault();

              const tabsNavItemData = e.currentTarget.getAttribute('data-tabs-target');
              const tabsContentAll = element.querySelectorAll('[data-tabs-content]');
              const tabsContentItem = element.querySelector(`[data-tabs-content='${tabsNavItemData}']`);

              tabsNavItem.forEach(elem => {
                elem.classList.remove('active');
              });

              tabsContentAll.forEach(elem => {
                elem.classList.remove('open');
                elem.style.display = 'none';
              });

              tabsContentItem.classList.add('open');
              fadeIn(tabsContentItem, 600, 'block');
              e.currentTarget.classList.add('active');
            });
          });
        });
      }
    } 
  }

  const tabsPln = new Tabs('pln');
  const tabsMdl = new Tabs('mdl');
  
  
  
  
    function fadeIn(el, timeout, display) {
    el.style.opacity = 0;
    el.style.display = display || 'block';
    el.style.transition = `opacity ${timeout}ms`;
    setTimeout(() => {
      el.style.opacity = 1;
    }, 10);
  }

  function fadeOut(el, timeout) {
    el.style.opacity = 1;
    el.style.transition = `opacity ${timeout}ms`;
    el.style.opacity = 0;

    setTimeout(() => {
      el.style.display = 'none';
    }, timeout);
  } 
  
  
  
</script>
<script src="/sahmatka/template/default/js/sun_path.js?v=20"></script>