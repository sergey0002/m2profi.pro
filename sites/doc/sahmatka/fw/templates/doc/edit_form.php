<?
// Для доступа к переменным из $data
extract($data);
$edit_dir_id = isset($edit_dir_id) ? (int)$edit_dir_id : 0;
$edit_file_id = isset($edit_file_id) ? (int)$edit_file_id : 0;
if (!$edit_file_id && !empty($v['files2node_id'])) {
	$edit_file_id = (int)$v['files2node_id'];
}
if (!$edit_dir_id && !empty($v['node_id'])) {
	$edit_dir_id = (int)$v['node_id'];
}
?>
<div class="doc-edit-form-wrap" style="padding:20px"
	data-file-id="<?=$edit_file_id?>"
	data-dir-id="<?=$edit_dir_id?>">
<form method="POST" enctype="multipart/form-data" >
<h2><?= $edit_file_id ? 'Редактирование файла' : 'Загрузка файла' ?></h2><br/>
<?=$filed->text('file_caption','Заголовок',$file_caption);?> 
<?=$filed->file2('filex','Файл', isset($v['puth']) ? $v['puth'] : '',$fid,true,true,false);?>  


<?=$filed->date('docdate', 'Дата документа',   isset($v['docdate']) ? $v['docdate'] : '' );?>
<?=$filed->text('comment', 'Комментарий',   isset($v['comment']) ? $v['comment'] : '' );?>




<?=$filed->checkbox( 'del', 'Удалить документ' , isset($v['del']) ? $v['del'] : 0 );?>

<?=$filed->submit('Сохранить');?>
</form>
<?php if (!empty($saved_file_id)) { ?>
<script>
(function () {
    var payload = {
        source: 'm2profi-doc',
        type: 'doc-file-saved',
        fileId: <?= (int)$saved_file_id ?>,
        dirId: <?= (int)$saved_dir_id ?>,
        deleted: <?= !empty($saved_deleted) ? 1 : 0 ?>
    };
    try {
        if (window.parent && window.parent !== window) {
            window.parent.postMessage(payload, '*');
        }
    } catch (e) {}
})();
</script>
<?php } ?>
</div>
