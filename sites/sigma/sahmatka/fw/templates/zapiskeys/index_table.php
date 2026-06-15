<style>
  .table table .del td span {
    color: red;
    text-decoration: line-through;
    text-decoration-color: red;
  }
</style>
<div class="stat-top stat-top_lp stat-top_key">
  <form method="get" action="#" id="filtrform" data-controller="zapiskeys">
    <input type="hidden" name="get_arr" value="">

    <div class="stat-top-filter">

      <div class=" stat-top-item  stat-top-select stat-top-item_house">
        <select name="home" id="sel_home" data-placeholder="Объект">
          <option value="">Объект</option>
        </select>
      </div>

      <div class="stat-top-item  stat-top-select  stat-top-item_house">
        <select name="section" id="sel_section" data-placeholder="Секция">
          <option value="">Секция</option>
        </select>
      </div>

      <div class=" stat-top-item stat-top-select stat-top-item_house">
        <select name="apartment_num" id="sel_apartment_num" placeholder="Секция">
          <option value=""><?= unit_label_cap('nom') ?></option>
        </select>
      </div>

      <div class="stat-top-item    stat-top-select   stat-top-item_house">
        <select name="date" id="sel_date" data-placeholder="Дата">
          <option value="">Дата</option>
        </select>
      </div>

      <div class="stat-top-item    stat-top-select   stat-top-item_house" style="min-width: 100px; line-height: 3.2em; ">

        <input type="checkbox" id="show_dell" name="show_dell" value="1">
        <label for="show_dell">Удаленные</label>

        <input type="checkbox" id="pom" name="pom" value="1">
        <label for="pom">С помогающей</label>

        <input type="checkbox" id="arhiv" name="arhiv" value="1">
        <label for="arhiv">Архив</label>

      </div>

    </div>

    <input type="hidden" name="action" value="zapis">

  </form>

  <a href="JavaScript:window.print();" class="stat-top__print"></a>
</div>

<div class="stat-table stat-table_notpd stat-table-user table">

  <table>
    <thead>
      <tr>
        <th>id</th>
        <th>Дата</th>
        <th>Время</th>
        <th>Дом</th>
        <th title="Секция №">П-д</th>
        <th><?= unit_label_cap('nom') ?></th>
        <th>Телефон</th>
		<th>E-Mail</th>
        <th title="С помогающей компанией">Пом</th>
		<th title="С помогающей компанией">ДКП</th>
        <th>ФИО</th>
        <th style="min-width:70px;"> </th>
      </tr>
    </thead>
    <tbody id="fw_data_tbody"></tbody>
  </table>

  <div style="width: 100%; max-width: 100vw; text-align: center; padding: 50px; display: none;" id="progressbar">
    <img src="loader.gif">
  </div>

</div>