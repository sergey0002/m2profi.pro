 <?
 	$result=$data;
	$stat = $data['status'];
?>

<style>
  @media print {
    .noprint {
      display: none;
    }
  }
  
  body {
    font-family: "Exo 2";
    color: #000;
  }
  
  body {
    font: 90.5%/1.3 normal Helvetica, sans-serif;
    padding-top: 50px;
    margin-top: 50px;
    font: 90.5%/1.3 normal Helvetica, sans-serif;
    padding-top: 50px;
    margin-top: 50px;
  }
  
  .actfix {
    padding-left: 10px;
    width: 100%;
    display: inline-block;
  }
  
  .vcardem {
    margin-top: 5px;
    margin-bottom: 5px;
  }
  
  .tabs__caption {
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-flex-wrap: wrap;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    list-style: none;
    position: relative;
    margin: -1px 0 0 -1px;
    font-family: Exo 2;
    font-size: 18px;
    font-weight: bold;
    width: 100%;
  }
  
  .tabs__caption li:last-child:before {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    right: -2px;
    z-index: -1;
    height: 5px;
  }
  
  .tabs__caption:after {
    content: '';
    display: table;
    clear: both;
  }
  
  .tabs__caption li {
    padding: 9px 15px;
    margin: 1px 0 0 1px;
    color: #000;
    position: relative;
    text-align: center;
  }
  
  .tabs__caption li:not(.active) {
    cursor: pointer;
  }
  
  .tabs__caption li:not(.active):hover {
    border-bottom: solid 5px #E30613;
  }
  
  .tabs__caption .active {
    border-bottom: solid 5px #E30613;
  }
  
  .tabs__caption .active:after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    right: 0;
    height: 5px;
  }
  
  .tabs__content {
    transition: 1s;
    display: none;
    padding: 7px 15px;
    font-family: Exo 2;
    font-size: 14px;
    line-height: 1.7em;
    padding-left: 40px;
    padding-top: 20px;
    opacity: 0;
  }
  
  .tabs__content.active {
    display: block;
    transition: 1s;
    opacity: 100;
  }
  
  .vertical .tabs__caption {
    float: left;
    display: block;
 }
  
  .vertical .tabs__caption li {
    float: none;
    border-width: 2px 0 2px 2px;
    border-radius: 5px 0 0 5px;
  }
  
  .vertical .tabs__caption li:last-child:before {
    display: none;
  }
  
  .vertical .tabs__caption .active:after {
    left: auto;
    top: 0;
    right: -2px;
    bottom: 0;
    width: 2px;
    height: auto;
  }
  
  .vertical .tabs__content {
    overflow: hidden;
  }
  
  @media screen and (max-width: 650px) {
    .tabs__caption li {
      -webkit-flex: 1 0 auto;
      -ms-flex: 1 0 auto;
      flex: 1 0 auto;
    }
    .vertical .tabs__caption {
      float: none;
      display: -webkit-flex;
      display: -ms-flexbox;
      display: flex;
    }
    .vertical .tabs__caption li {
      border-width: 2px 2px 0;
      border-radius: 5px 5px 0 0;
    }
    .vertical .tabs__caption li:last-child:before {
      display: block;
    }
    .vertical .tabs__caption .active:after {
      top: auto;
      bottom: -5px;
      left: 0;
      right: 0;
      width: auto;
      height: 5px;
      background: #FFF;
    }
  }
  
  .room-tabnav {
    margin-bottom: 23px;
 }
  
  .room-tabnav li {
    display: inline-block;
    vertical-align: top;
    margin-right: 23px;
  }
  
  .room-tabnav li:last-child {
    margin-right: 0;
  }
  
  .room-tabnav li a {
    position: relative;
    display: inline-block;
    padding-bottom: 17px;
    font-size: 18px;
    font-weight: 700;
  }
  
  .room-tabnav li a:before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    -webkit-transform: translateX(-50%);
    -ms-transform: translateX(-50%);
    transform: translateX(-50%);
    width: 0;
    height: 5px;
    -webkit-transition: all .25s ease;
    -o-transition: all .25s ease;
    transition: all .25s ease;
    background: #E30613;
  }
  
  .room-tabnav li a:hover:before,
  .room-tabnav li a.active:before {
    width: 100%;
  }
  
  .room-tabbody {
    display: none;
  }
  
  .room-tabbody:before,
  .room-tabbody:after {
    content: " ";
    display: table;
  }
  
  .room-tabbody:after {
    clear: both;
  }
  
  .room-tabbody.open {
    display: block;
 }
  
  .room-tabtext {
    float: left;
    width: 50%;
    padding-right: 20px;
    font-size: 16px;
    line-height: 1.5;
  }
  
  .room-tabform {
    float: left;
    width: 50%;
    padding-right: 45px;
    text-align: right;
  }
  
  .room-tabform form {
    display: inline-block;
    max-width: 300px;
  }
  
  .room-tabform input {
    display: block;
    width: 100%;
    max-width: 300px;
    height: 37px;
    margin-bottom: 10px;
    padding-left: 4px;
    font-size: 16px;
    border: 1px solid rgba(0, 0, 0, 0.53);
  }
  
  .room-tabform__accept {
    display: block;
    margin-bottom: 10px;
    text-align: left;
  }
  
  .room-tabform__accept .jq-checkbox {
    float: left;
    top: 3px;
    width: 16px;
    height: 16px;
    margin-right: 8px;
    border-radius: 0;
    border: 1px solid #C3C3C3;
    -webkit-box-shadow: none;
            box-shadow: none;
    background: none;
  }
  
  .room-tabform__accept .jq-checkbox.focused {
    border: 1px solid #C3C3C3;
  }
  
  .room-tabform__accept .jq-checkbox.checked .jq-checkbox__div {
    width: 12px;
    height: 12px;
    border-radius: 0;
    -webkit-box-shadow: none;
            box-shadow: none;
    background: url(../images/check.svg) 0 0 no-repeat;
    background-size: 100%;
 }
  
  .room-tabform__accept span {
    display: table;
    font-size: 16px;
    line-height: 1.4;
  }
  
  .room-tabform__btn {
    width: 100%;
    height: 37px;
    line-height: 35px;
 }
  
  select{
    padding: 4px;
    margin: 3px;
    border: 1px solid rgba(0, 0, 0, 0.53);
 

    max-width: 300px;
  }
</style>
<script>
 (function($) {
    $(function() {
      $('ul.tabs__caption').on('click', 'li:not(.active)', function() {
        $(this)
          .addClass('active').siblings().removeClass('active')
          .closest('div.tabs').find('div.tabs__content').removeClass('active').eq($(this).index()).addClass('active');
      });
    });
  })(jQuery);
  $(".room-tabnav li a").click(function() {
    $('.room-tabnav li a').removeClass("active");
    $(this).addClass("active");
    $(".room-tabbody").removeClass('open').hide();
    var activeTab = $(this).attr("href");
    $(activeTab).addClass('open').fadeIn(700);
    return false;
  });
</script>

<div class="container-fluid" style="padding:20px;">
  <div class="row">
    <div class="col-md-12 col-xs-12">
      <h1 style="font-size:34px;"><b><?=$homes[$home_id]['caption'];?></b></h1>
    </div>
    <div class="col-md-12 col-xs-12" style="font-size:16px; text-transform: uppercase;">
	
	<span style="font-size:24px; font-weight:bold;"><?=$data['adress_disp']?></span>
	
      
    </div>

  </div>

  <div class="row">

    <div class="col-md-6 col-xs-12" style="text-align:center; position:relative;">
      <img src="<?=$GLOBALS['config']['domains']['em']?>/sahmatka//images/parkingcar.png" style="max-height:600px; max-width:100%">
	  <div style="position:absolute; top:240px; text-align:center; width:100%; font-size:54px; font-weight:bold; right:5px;"> <?=$result['num'];?>  </div>
    </div>
    <div class="col-md-6 col-xs-12" style="text-align:left; font-size:24px; position:relative;">
      <b> Этаж:  <?=$result['floor'];?>    </b> <br/>
      <b> Место: <?=$result['num'];?>    </b> <br/>
       <b>Площадь: </b><?=$result['area']?> м<sup>2</sup>
      <hr/>

      <?
if(!$stat || $stat==2)
{
	?>

         <b><?=$result['price!'];?></b>
     <br/>  <br/> 

        <div style="margin:10px; margin-left:0;" class="noprint">

          <a href="#" onClick="window.print();"><img src="images/p.png" width="40px" style="margin:5px; margin-left:0;"></a>
          <a onclick="$('#one').slideToggle('slow');" href="javascript://"><img src="images/m.png" width="40px" style="margin:5px;"></a>

          <div id="one" style="display: none;">
            <form action=" " method="post" enctype="multipart/form-data" method="post">
              <input type="text" name="email" placeholder="E-Mail" style="font-size:14px; padding:4px; margin:4px; border: 1px solid rgba(0, 0, 0, 0.53); width:100%; max-width:300px;" />
              <br/>
              <input type="submit" style=" font-size:14px; padding:4px; margin:4px; background: #40C351; border: 1px solid #40C351; letter-spacing: 0.21em; text-transform: uppercase; color: #FFFFFF; width:100%; max-width:300px;">
            </form>

          </div>
        </div>
   <br/>      <br/>     
        <div class="tabs noprint">

          <ul class="tabs__caption" style="padding-left:0;">

            <li class="active">Забронировать</li>
            <a href="<?=$GLOBALS['config']['domains']['em_nsk']?>/exc.php" target="_top">
              <li>Записаться на экскурсию</li>
            </a>
          </ul>
 

          <div class="tabs__content active" style="padding-left: 0; font-size:16px;">
            <div class="row">

              <div class="col-md-6">
                Забронировать парковку в "Энергомонтаж" можно всего за пару минут: оставьте ваш контактный телефон и наш менеджер свяжется с вами.
                <br/> За качество отвечает застройщик.
              
              </div>
              <div class="col-md-6">
                <form action=" " method="post" enctype="multipart/form-data" method="post">
                  <input type="text" name="name" required placeholder="Ваше имя" style="padding:4px; margin:3px; border: 1px solid rgba(0, 0, 0, 0.53); width:100%; max-width:300px;" />
                  <br/>
                  <input type="text" name="phone" required placeholder="Контактный телефон" style="padding:4px; margin:3px; border: 1px solid rgba(0, 0, 0, 0.53); width:100%; max-width:300px;" />
                  <br/>
                  <div style="width:100%; max-width:300px;">
                    <input type="checkbox" name="offerta" checked="checked" value="1"> Я даю свое согласие на обработку персональных данных
                  </div>
                  <input type="submit" style="padding:4px; margin:3px; background: #40C351; border: 1px solid #40C351; letter-spacing: 0.21em; text-transform: uppercase; color: #FFFFFF; width:100%; max-width:300px;" value="Забронировать" onClick="ga('send', 'event', 'submit', 'kv_order_botton'); ym(18713149,'reachGoal','kv_order_botton'); return true;">
                </form>
              </div>

            </div>

          </div>

        </div>
        <!-- .tabs -->

        <?
}
?>

    </div>

  </div>

</div>