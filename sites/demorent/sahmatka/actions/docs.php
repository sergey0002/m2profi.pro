			
			
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
			
			
			 
			
			
			
			<div class="row acc-head slh_active" style=" border-bottom: 1px solid #EEE;">
				<div class="col-md-12">
				<a href="/sahmatka/user.php?action=docs" class="<?=$ncom_st?> right-line-header" style="line-height: 45px;"> 
				<span class="plus_span">+</span>
				<span>Жилая недвижимость</span></a></div>
			</div>

			<div class="row acc-body slb_active" style="border-bottom: 1px solid #EEE; margin-top: 20px;">
			
			
			
			<div style="  font-weight:bold;     font-size: 17px;   line-height: 1.5em;     border-bottom: solid 1px #CCC;
    padding: 10px;">
			
			


    По сделкам ДКП быстрой оплате подлежат сделки, акты и счета по которым были предоставлены партнерам не позднее 3-х дней с момента поступления крайней суммы от клиента. 

В противном случае, оплаты по субагентским договорам АН будут производиться в обычном режиме.

			
			</div>
				<div class="col-md-12" style="display:none;" >

					<div class="stat-docs-item">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a href="https://em-nsk.ru/sahmatka2/reglament_newc.php"  class="stat-docs-item__title iframe_r">
							Документы, необходимые <br/>для сотрудничества
						</a> 
					</div> 
				</div>

			<div class="row" style="margin: 15px;    border-bottom: 1px solid #EEE;    margin-bottom: 30px;">
				<div class="col-md-6" style="padding-left: 0;">
				<span class="helink" style="border-right:solid 1px #EEE; color:#000; font-size:16px;     margin-bottom: 20px;">Для индивидуального предпринимателя (ИП)</span>
				
					<div class="stat-docs-item" style="float:left; width:auto; min-width: 250px; "  >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a class="stat-docs-item__title iframe_r"  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2024/stalker_ip_2024.docx">
							Субагентский <br>договор <br> OOO "Сталкер" 
						</a>
						
					</div>
					
					<div class="stat-docs-item"  style="  width:auto; min-width: 250px;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a class="stat-docs-item__title iframe_r"  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2024/m2_ip_2024.docx">
							Субагентский <br>договор  <br>ООО "М2" 
						</a>
					</div>
					
				
				</div>
				<div class="col-md-6" style="padding-left: 0;">
				<span class="helink " style="color:#000; font-size:16px;     margin-bottom: 20px;">Для общества с ограниченной ответственностью (ООО)</span>
				
					<div class="stat-docs-item"  style="float:left; width:auto; min-width: 250px;  " >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2024/stalker_ooo_2024.docx" >
							Субагентский <br>договор <br> OOO "Сталкер" 
						</a>
					</div> 
			 
					<div class="stat-docs-item" style="  width:auto; min-width: 250px;">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2024/m2_ooo_2024.docx" >
							Субагентский <br>договор <br> ООО "М2" 
						</a>
					</div> 
				</div>
				 
			</div>	
			
			
			
				<div class="row" style="margin: 15px;    border-bottom: 1px solid #EEE;    margin-bottom: 30px; display:none;">
				<div class="col-md-6" style="padding-left: 0;">
			 
				
					<div class="stat-docs-item" style="float:left; width:auto; min-width: 250px; display:none;"  >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a class="stat-docs-item__title iframe_r"  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/dop_ip_stalker.docx">
							Доп. соглашение <br>до   30.06.2023 <br> OOO "Сталкер" 
						</a>
						
					</div>
					
		
					<div class="stat-docs-item"  style="  width:auto; min-width: 250px;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a class="stat-docs-item__title iframe_r"  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/30.06_23_dop_m2_ip.docx">
							Доп. соглашение <br>до  30.06.2023  <br>ООО "М2" 
						</a>
					</div>
					
					
					
					<div class="stat-docs-item"  style="float:left; width:auto; min-width: 250px; display:none;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/30.09_23_stalker__ip.docx" >
							Доп. соглашение <br>до  30.09.2023<br> OOO "Сталкер" 
						</a>
					</div> 
					
					
					<div class="stat-docs-item"  style="  width:auto; min-width: 250px;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a class="stat-docs-item__title iframe_r"  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/dop_m2_ip.docx">
							Доп. соглашение <br>до  30.09.2023  <br>ООО "М2" 
						</a>
					</div>
					
					
					
					<div class="stat-docs-item"  style="float:left; width:auto; min-width: 250px; display:none;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/31.12_23_stalker__ip.docx" >
							Доп. соглашение <br>до  31.12.2023<br> OOO "Сталкер" 
						</a>
					</div> 
					
					
					<div class="stat-docs-item"  style="  width:auto; min-width: 250px;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a class="stat-docs-item__title iframe_r"  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/31.12_23_dop_m2__ip.docx">
							Доп. соглашение <br>до  31.12.2023  <br>ООО "М2" 
						</a>
					</div>
					
					
					
					
					
				
				</div>
				<div class="col-md-6" style="padding-left: 0;">
			 
				
					<div class="stat-docs-item"  style="float:left; width:auto; min-width: 250px; display:none;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/dop_ooo_stalker.docx" >
							Доп. соглашение <br>до  30.06.2023<br> OOO "Сталкер" 
						</a>
					</div> 
			 
					<div class="stat-docs-item" style="  width:auto; min-width: 250px;">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/30.06_23_dop_m2_ooo.docx" >
							Доп. соглашение <br>до   30.06.2023<br> ООО "М2" 
						</a>
					</div> 
					
					
					
					
					<div class="stat-docs-item"  style="float:left; width:auto; min-width: 250px; display:none;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/30.09_23_stalker__ooo.docx" >
							Доп. соглашение <br>до  30.09.2023<br> OOO "Сталкер" 
						</a>
					</div> 
					
					
					
					<div class="stat-docs-item" style="  width:auto; min-width: 250px;">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/dop_m2_ooo.docx" >
							Доп. соглашение <br>до   30.09.2023<br> ООО "М2" 
						</a>
					</div> 
					
					
					
					
					<div class="stat-docs-item"  style="float:left; width:auto; min-width: 250px; display:none;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/31.12_23_stalker__ooo.docx" >
							Доп. соглашение <br>до  31.12.2023<br> OOO "Сталкер" 
						</a>
					</div> 
					
					
					
					<div class="stat-docs-item" style="  width:auto; min-width: 250px;">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dop_24032023/31.12_23_dop_m2__ooo.docx" >
							Доп. соглашение <br>до   31.12.2023<br> ООО "М2" 
						</a>
					</div> 
					
					
					
				</div>
				 
			</div>
			
			
				<div class="col-md-12">
				
				
				
				<div class="stat-docs-row">
					<div class="stat-docs-item">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka/agent_files/bron.docx"  class="stat-docs-item__title iframe_r">
							Форма брони
						</a>
					</div>
					<div class="stat-docs-item">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka/agent_files/uvedomlenie.docx"   class="stat-docs-item__title iframe_r">
							Форма уведомления
						</a>
					</div>
					<div class="stat-docs-item">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka/agent_files/ipoteka.zip"  class="stat-docs-item__title iframe_r">
							Анкета ипотека
						</a>
					</div>
					<div class="stat-docs-item">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka/agent_files/nal.zip"   class="stat-docs-item__title iframe_r">
							Анкета наличный <br>расчет
						</a>
					</div>
					<div class="stat-docs-item">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka/agent_files/voen.zip"  class="stat-docs-item__title iframe_r">
							Анкета военная ипотека
						</a>
					</div>
					
					
					<div class="stat-docs-item">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a href="https://em-nsk.ru/sahmatka2/naf/opros.pdf"  class="stat-docs-item__title iframe_r">
							Опросный лист
						</a>
					</div>
					
				</div>
				
				
				
				</div>
			</div>
			
			<div class="row acc-head"   style="    border-bottom: 1px solid #EEE; cursor: not-allowed; display:no!ne;" >
				<div class="col-md-12">
				<a href="/sahmatka/user.php?action=docs" class="<?=$com_st?> right-line-header"  > 
				<span class="plus_span">+</span>
				<span>Коммерческая недвижимость</span></a></div>
			</div>
			
			
			
			<div class="row acc-body" style="border-bottom: 1px solid #EEE; margin-top: 20px;">
				<div class="col-md-12" style="display:none;" >

					<div class="stat-docs-item">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a href="https://em-nsk.ru/sahmatka2/reglament_newc.php"  class="stat-docs-item__title iframe_r">
							Документы, необходимые <br/>для сотрудничества
						</a> 
					</div> 
				</div>








			<div class="row" style="display:no!ne; margin: 15px;    border-bottom: 1px solid #EEE;    margin-bottom: 30px;">
				<div class="col-md-6" style="padding-left: 0;">
				<span class="helink" style="border-right:solid 1px #EEE; color:#000; font-size:16px; margin-bottom: 20px;">Для индивидуального предпринимателя (ИП)</span>
				
				
					<div class="stat-docs-item"  style="  width:auto; min-width: 250px; FLOAT:LEFT;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a class="stat-docs-item__title iframe_r"  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2024/com/stalker_c_ip.docx">
							Субагентский <br>договор  <br>ООО "Сталкер" 
						</a>
					</div>
					
				<div class="stat-docs-item"  style="  width:auto; min-width: 250px;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a class="stat-docs-item__title iframe_r"  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2024/com/m2_c_ip.docx">
							Субагентский <br>договор  <br>ООО "М2" 
						</a>
					</div>
					
					
				</div>
				<div class="col-md-6" style="padding-left: 0;">
				<span class="helink " style="color:#000; font-size:16px; margin-bottom: 20px;">Для общества с ограниченной ответственностью (ООО)</span>
				
			 
					<div class="stat-docs-item" style="  width:auto; min-width: 250px; FLOAT:LEFT">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2024/com/stalker_c_ooo.docx" >
							Субагентский <br>договор <br> ООО "Сталкер" 
						</a>
					</div> 
					
					
					<div class="stat-docs-item" style="  width:auto; min-width: 250px;">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2024/com/stalker_c_ooo.docx" >
							Субагентский <br>договор <br> ООО "М2" 
						</a>
					</div>
					
					
				</div>
				
				
				
			</div>	
			
			
			




			<div class="row" style="display:none; margin: 15px;    border-bottom: 1px solid #EEE;    margin-bottom: 30px;">
				<div class="col-md-6" style="padding-left: 0;">
				<span class="helink" style="border-right:solid 1px #EEE; color:#000; font-size:16px;     margin-bottom: 20px;">Для индивидуального предпринимателя (ИП)</span>
				
				
					<div class="stat-docs-item"  style="  width:auto; min-width: 250px;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a class="stat-docs-item__title iframe_r"  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2022/m2_rent_ip.docx">
							Субагентский <br>договор  <br>ООО "М2" 
						</a>
					</div>
					
					
					<div class="stat-docs-item"  style="  width:auto; min-width: 250px;" >
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a class="stat-docs-item__title iframe_r"  href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2022/dop_m2_rent_ip.docx">
							Доп. соглашение ИП <br/>до 30.06.2023 <br>ООО "М2" 
						</a>
					</div>
					
					
				
				</div>
				<div class="col-md-6" style="padding-left: 0;">
				<span class="helink " style="color:#000; font-size:16px;     margin-bottom: 20px;">Для общества с ограниченной ответственностью (ООО)</span>
				
			 
					<div class="stat-docs-item" style="  width:auto; min-width: 250px;">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2022/m2_rent_ooo.docx" >
							1Субагентский <br>договор <br> ООО "М2" 
						</a>
					</div> 
					
				
				<div class="stat-docs-item" style="  width:auto; min-width: 250px;">
						<div class="stat-docs-item__icon"><img src="template/default/images/doc.png" alt="" /></div>
						<a  class="stat-docs-item__title iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=/sahmatka2/dog2022/dop_m2_rent_ooo.docx" >
								Доп. соглашение ООО  <br/>до 30.06.2023 <br>ООО "М2" 
						</a>
					</div>

	
					
				</div>
				
								
			</div>	
			
			
			
						
			
			
			
	 
			</div>
			
			
			
			
			
			 
			
			<script>
			
 
  
 
$(document).ready(function() {
  //прикрепляем клик по заголовкам acc-head
	$('.acc-head').on('click', f_acc);
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
 
			</script>
			
			
			
		 
					
					
					
			 
				<span class="com_header" style ="font-size: 18px; color: #2F4049;  margin-top: 30px; display: block;">Дни приема актов: понедельник, вторник, четверг с 9.30 до 14.00</span>
			</div>
		</div>
		
		<br><br>
		
	 
		
<div style="display:none;">
<h2 style="color:#999;">Дополнительные соглашения с 01.10.2022г.</h2>
 <br> 
 
 
<a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/new2/sahmatka2/agent_files/dop011022/dop_dog_m2_ip.docx" style="color:#999; font-size:18px;">Дополнительное соглашение к договору ООО "М2" 01.10.2022 - 31.12.2022 (ИП)</a><br><br>
<a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/new2/sahmatka2/agent_files/dop011022/dop_dog_m2_ooo.docx" style="color:#999; font-size:18px;">Дополнительное соглашение к договору ООО "М2" 01.10.2022 - 31.12.2022 (ООО)</a><br><br>
		



<h2 style="color:#999;">Форма договоров, заключенных до 30.08.2020 (с доп. соглашениями)</h2>
 <br> 
 
 
<a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/sahmatka2/agent_files/21/dogip.docx" style="color:#999; font-size:18px;">Субагентский договор 2021 (ИП)</a><br><br>
<a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/sahmatka2/agent_files/21/dogooo.docx" style="color:#999; font-size:18px;">Субагентский договор 2021 (ООО)</a><br><br>
 
 
 
 <h2 style="color:#999;">Дополнительные соглашения к договору заключенному в 2022г.</h2>
 <br> 
 
<a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/sahmatka2/naf/dop010322/dopp_ip22.docx" style="color:#999; font-size:18px;">Субагентский договор 2022 (ИП)</a><br><br>
<a class="iframe_r" href="https://em-nsk.ru/sahmatka2/reglament.php?q=https://em-nsk.ru/sahmatka2/naf/dop010322/dopp_ooo22.docx" style="color:#999; font-size:18px;">Субагентский договор 2022(ООО)</a><br><br>
 
 </div>
 
 
		
	</div>
</section>




  