			
			
<style>
.helink{
    color: #00CDAD;
    font-size: 21px;
    padding-top: 15px;
	 padding-bottom:15px;
    display: block;
	font-weight:bold;
}

.helink-a{
    color: #000;
   line-height: 45px;
}
.plus_span{
	width: 35px; height: 35px; 
	border-radius: 30px;  
	border: solid 2px #2F4049;  
	display: inline-block;  
	line-height: 33px; 
	text-align: center;    
	font-size: 26px;   
	margin-right: 10px; 
	font-weight: normal;
}





.right-line-header
{
	position: relative;
    overflow: hidden;
}
	
.right-line-header:after {
  content: "";
  border-bottom: 1px solid #2F4049;
  width: 100%;
  height: 0.5em;
  position: absolute;
  top: 30px;
  margin-left: 10px;
}




#accordeon {
	width: 350px;
	border: 10px solid #fff;
	box-shadow: 0 0 10px grey;
	margin: 10px;
}
 
.acc-head {
	cursor: pointer;
}
 
.acc-body {
	border-bottom: 1px solid #c0c0c0;
	margin-bottom: 5px;
	display: none;
}



.slh_active *{color:#00CDAD; border-color:#00CDAD;}

.slh_active  .right-line-header:after 
{
    content: "";
    border-bottom: 1px solid #00CDAD;
}

 

.stat-docs-item__title {font-size:16px;}
</style>


<?
if($_GET['type']=='com'){ $com_st = 'helink helink-a'; $ncom_st = 'helink helink-a'; }
else{ $com_st = 'helink helink-a'; $ncom_st = 'helink helink-a';  }
?>			
			
<section class="section-stat">
	<div class="container mobc">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Документы</div>
		</div>
		<div class="stat">
			<div class="stat-docs">
				<div class="row a2cc-1body" style="border-bottom: 1px solid #EEE; margin-top: 20px;">
					<div style="  font-weight:bold;     font-size: 17px;   line-height: 1.5em;     border-bottom: solid 1px #CCC;
    padding: 10px;">
						<p> По совершенным договорам купли-продажи земельных участков быстрой оплате подлежат сделки, оригиналы актов и счетов по которым были предоставлены партнерами не позднее 3-х дней с момента поступления полной оплаты сделки от Клиента. </p> 
						<p> Во всех остальных случаях оплаты по субагентским договорам АН производятся в обычном режиме в соответствии со сроками, указанными в субагентском договоре. </p>
					</div>
					<div class="col-md-12" style="display:none;">
						<div class="stat-docs-item">
							<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div> 
							<a href="" class="stat-docs-item__title iframe_r">Документы, необходимые <br/>для сотрудничества</a> </div>
					</div>
					 
					 
					 <style>
					 .stat-docs-item {
							width: auto;
						}
					 </style>
					 <br><br>			 <br><br>
					<div class="row" style="width:100%; display:no!ne; margin-top:30px;">
						<div class="col-md-4">
							<div class="stat-docs-item" style="  width:auto; min-width: 250px;">
								<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div> 
								<a class="stat-docs-item__title iframe_r" href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/dogovor.docx?x=<?=rand(0,1000)?>">Субагентский <br>договор</a>
							</div>
						</div>
						
						<div class="col-md-4"  >
							<div class="stat-docs-item" style="  width:auto; min-width: 250px;">
								<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div> 
								<a class="stat-docs-item__title iframe_r" href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/dop.docx">Дополнительное соглашение<br/> к субагентскому Договору </a> 
								</div>
						</div>
						
						
						
						<div class="col-md-4" style="display:none;">
							<div class="stat-docs-item" style="  width:auto; min-width: 250px;">
								<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div> 
								<a class="stat-docs-item__title iframe_r" href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/dog_reg.docx">Договор на подготовку документов <br/>к государственной регистрации</a> 
								</div>
						</div>
					</div>
					
				    <div class="row" style="width:100%; display:n!one;">
						<div class="col-md-4">
							<div class="stat-docs-item">
								<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div> 
								<a href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/bron.docx" class="stat-docs-item__title iframe_r">Форма брони</a> 
							</div>
						</div>
						<div class="col-md-4">
							<div class="stat-docs-item">
								<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div> 
								<a href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/uvedomlenie.docx?x=1" class="stat-docs-item__title iframe_r">Форма уведомления</a> 
							</div>
						</div>
						<div class="col-md-4">
							<div class="stat-docs-item">
								<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div> 
								<a href="https://gl.m2profi.pro/doc/reglament.php?q=/doc/anket.xlsx" class="stat-docs-item__title iframe_r">Анкета </a> </div>
						</div>
			 
					</div>
					
					
					
					
				 
					
					
					
				</div>
				<script>
				$(document).ready(function() {
				  //прикрепляем клик по заголовкам acc-head
					//$('.acc-head').on('click', f_acc);
				});
				 
				function f_acc(){
				//скрываем все кроме того, что должны открыть
				  $('.acc-body').not($(this).next()).slideUp(300);
				// открываем или скрываем блок под заголовком, по которому кликнули
				  
				  
				 
					if( !$(this).hasClass("slh_active") ) // Если не активный пункт на который нажали
					{
						// Закрываем все
						$('.acc-head').removeClass('slh_active');
						$('.acc-body').removeClass('slb_active');
						$('.plus_span').html('+');
						
						$('.com_header').hide();
						
						// Открываем текущий
						$(this).addClass('slh_active');
						$(this).next().addClass('slb_active'); 
						$('.plus_span',this).html('-');
						
					}
					else
					{
						// 
						$(this).removeClass('slh_active');
						$(this).next().removeClass('slb_active');
						$('.plus_span').html('+');
						
						$('.com_header').show();
					}
					
				    $(this).next().slideToggle(300);
					return false;
				}
				</script> <span class="com_header" style="font-size: 18px; color: #2F4049;  margin-top: 30px; display: block;">Дни приема актов: понедельник, вторник, четверг с 9.30 до 14.00</span> </div>
		</div> <br><br>
		<div style="display:none;">
			<h2 style="color:#999;">Дополнительные соглашения с 01.10.2022г.</h2> <br> <a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/new2/sahmatka2/agent_files/dop011022/dop_dog_m2_ip.docx" style="color:#999; font-size:18px;">Дополнительное соглашение к договору ООО "М2" 01.10.2022 - 31.12.2022 (ИП)</a><br><br> <a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/new2/sahmatka2/agent_files/dop011022/dop_dog_m2_ooo.docx" style="color:#999; font-size:18px;">Дополнительное соглашение к договору ООО "М2" 01.10.2022 - 31.12.2022 (ООО)</a><br><br>
			<h2 style="color:#999;">Форма договоров, заключенных до 30.08.2020 (с доп. соглашениями)</h2> <br> <a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/sahmatka2/agent_files/21/dogip.docx?x=1" style="color:#999; font-size:18px;">Субагентский договор 2021 (ИП)</a><br><br> <a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/sahmatka2/agent_files/21/dogooo.docx" style="color:#999; font-size:18px;">Субагентский договор 2021 (ООО)</a><br><br>
			<h2 style="color:#999;">Дополнительные соглашения к договору заключенному в 2022г.</h2> <br> <a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/sahmatka2/naf/dop010322/dopp_ip22.docx" style="color:#999; font-size:18px;">Субагентский договор 2022 (ИП)</a><br><br> <a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/sahmatka2/naf/dop010322/dopp_ooo22.docx" style="color:#999; font-size:18px;">Субагентский договор 2022(ООО)</a><br><br> </div>
	</div>
</section>




  