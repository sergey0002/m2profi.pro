<?
require 'core/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

//Создаем экземпляр класса электронной таблицы
$spreadsheet = new Spreadsheet();

 

//$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(30);
// https://progi.pro/shirina-stolbca-shirini-phpexcel-7503132
//https://github.com/shuchkin/simplexlsx/ - легкая альтернатива php 

/*
Функция которая выводит ссылку на ексель файл 


к текущему урл добавляется приставка &excel=1
при ее наличии создается файл и редиректится на него ?
(захламление файлами)


Альтернативный роутер (все текущие гет переменные но файл excel_route.php - )

как сделать чтобы не мешал вывод шаблона итп ? 
об старт в начале обгет клеан в конце как в гранде!
и вывод напрямую как убрать хердер заголовки обявленные ранее? 


спец шлюз !
сделать без всяких шаблонов (альтернативный аякс роутер)
*/


//Получаем текущий активный лист
$sheet = $spreadsheet->getActiveSheet();
// Записываем в ячейку A1 данные

 





$sheet->setCellValue('A1', 'Hello my Friend!');

# Заголоки
$sheet->setCellValue('A1', 'Картинка');  
$sheet->setCellValue('B1', 'Название');  
$sheet->setCellValue('C1', 'Количество');
$sheet->setCellValue('D1', 'Остаток');
$sheet->setCellValue('E1', 'Цена');
$sheet->setCellValue('F1', 'Сумма');
$sheet->setCellValue('G1', 'Код');

# Содержимое

for($i=0; $i<100; $i++) // Строки
{
	$sheet->setCellValue('A'.$i, 'Картинка'.rand(0,1000));  
	$sheet->setCellValue('B'.$i, 'Название');  
	$sheet->setCellValue('C'.$i, 'Количество');
	$sheet->setCellValue('D'.$i, 'Остаток');
	$sheet->setCellValue('E'.$i, 'Цена');
	$sheet->setCellValue('F'.$i, 'Сумма');
	$sheet->setCellValue('G'.$i, 'Код 123123123');
}

# Авто ширина столбов (все столбы на листе)
$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
$cellIterator->setIterateOnlyExistingCells( true );
/** @var PHPExcel_Cell $cell */
foreach( $cellIterator as $cell ) {
        $sheet->getColumnDimension( $cell->getColumn() )->setAutoSize( true );
}

$sheet->getStyle('1:1')->getFont()->setBold(true); // Первая строка жирным
$sheet ->setAutoFilter(
    $spreadsheet->getActiveSheet()
        ->calculateWorksheetDimension()
);




$writer = new Xlsx($spreadsheet);
//Сохраняем файл в текущей папке, в которой выполняется скрипт.
//Чтобы указать другую папку для сохранения. 
//Прописываем полный путь до папки и указываем имя файла
 //$writer->save('hello.xlsx');


$date = date('d-m-y-'.substr((string)microtime(), 1, 8));
$date = str_replace(".", "", $date);
$usr = rand(1000,100000);
$filename = "export_".$usr.'_'.$date.".xlsx";
 
 
 
 // We'll be outputting an excel file
header('Content-type: application/vnd.ms-excel');
// It will be called file.xls
header('Content-Disposition: attachment; filename="'.$filename.'"');
$writer->save("php://output");
exit();



/*
//$writer->save($filename);
$content = file_get_contents($filename);
header("Content-Disposition: attachment; filename=".$filename);
//unlink($filename);
exit($content);
*/
 