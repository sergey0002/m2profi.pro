<?
if($_POST)
{
	//print_r($_POST);
	
	
	if(isset($_POST['html']))
	{
		$html=$_POST['html'];
		$file="sdan2.txt";
		$ins=file_put_contents($file,$html);
		if($ins)
		{
			?>
			<center><span style="color:#006400; font-size:20px;">Данные сохранены!</span></center>
			<?
		}
	}
}
?>


<script src="https://cdn.ckeditor.com/ckeditor5/12.0.0/classic/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/12.0.0/classic/translations/ru.js"></script>
<style>
.ck-editor__editable {
     
}
</style>
<center>
<div style="padding:30px; width:100%;">
<form action="user.php?action=objects4" method="POST">
    <textarea class="editor" name="html">
		<?
		print file_get_contents('sdan2.txt');
		?>	   
    </textarea>
	<br><br>
	<input type="submit" value="СОХРАНИТЬ" />


</form>
	 </center>
	</div>
	
	
	
	
	<script>
	$(document).ready(function() {
	  var myEditor;
			ClassicEditor.create( document.querySelector( '.editor' ),
			{
				language: 'ru',
				resize: {
					minHeight: 100,
					maxHeight: 100
				}
			})
			.then( editor => {
            console.log( 'Editor was initialized', editor );
            myEditor = editor;
			} 
			)
            .catch( error => {
                console.error( error );
            } );
		 });
    </script>
	   
	   
	   