<?
// Для доступа к переменным из $data
extract($data);
?>
<div style="padding:20px">
<form method="POST" enctype="multipart/form-data" >
<h2>Загрузка файла</h2><br/>
<?=$filed->text('file_caption','Заголовок',$file_caption);?> 
<?=$filed->file2('filex','Файл',$v['puth'],$fid,true,true,false);?>  


<?=$filed->date('docdate', 'Дата документа',   $v['docdate'] );?>
<?=$filed->text('comment', 'Комментарий',   $v['comment'] );?>




<?=$filed->checkbox( 'del', 'Удалить документ' , $v['del'] );?>

<?=$filed->submit('Сохранить');?>
</form>
</div>