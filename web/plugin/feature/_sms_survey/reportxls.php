<?php
defined('_SECURE_') or die('Forbidden');

// nama file

$namaFile = "report.xls";

// Function penanda awal file (Begin Of File) Excel

function xlsBOF() {
echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
return;
}

// Function penanda akhir file (End Of File) Excel

function xlsEOF() {
echo pack("ss", 0x0A, 0x00);
return;
}

// Function untuk menulis data (angka) ke cell excel

function xlsWriteNumber($Row, $Col, $Value) {
echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
echo pack("d", $Value);
return;
}

// Function untuk menulis data (text) ke cell excel

function xlsWriteLabel($Row, $Col, $Value ) {
$L = strlen($Value);
echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
echo $Value;
return;
}

// header file excel

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,
        pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");

// header untuk nama file
header("Content-Disposition: attachment; filename=".$namaFile);

header("Content-Transfer-Encoding: binary ");

// memanggil function penanda awal file excel
xlsBOF();

// ------ membuat kolom pada excel --- //

// mengisi pada cell A1 (baris ke-0, kolom ke-0)
xlsWriteLabel(0,0,"NO");               

// mengisi pada cell A2 (baris ke-0, kolom ke-1)
xlsWriteLabel(0,1,"KEYWORD");              

// mengisi pada cell A3 (baris ke-0, kolom ke-2)
xlsWriteLabel(0,2,"SENDER");

// mengisi pada cell A4 (baris ke-0, kolom ke-3)
xlsWriteLabel(0,3,"MESSAGE");   

// mengisi pada cell A5 (baris ke-0, kolom ke-4)
xlsWriteLabel(0,4,"DATE"); 

// -------- menampilkan data --------- //

// koneksi ke mysql

mysql_connect("localhost", "root", "");
mysql_select_db("sms");


$keyword = $_GET['keyword'];
// query menampilkan semua data

$query = "
SELECT playsms_featureSurvey.keyword, 
        playsms_tblSMSIncoming.in_sender, 
        playsms_tblSMSIncoming.in_message, 
        playsms_tblSMSIncoming.in_datetime
FROM playsms_featureSurvey, playsms_tblSMSIncoming
WHERE playsms_featureSurvey.keyword = playsms_tblSMSIncoming.in_keyword AND 
         playsms_featureSurvey.keyword = '$keyword'
ORDER BY playsms_tblSMSIncoming.in_datetime DESC

";

$hasil = mysql_query($query);

// nilai awal untuk baris cell
$noBarisCell = 1;

// nilai awal untuk nomor urut data
$noData = 1;

while ($data = mysql_fetch_array($hasil))
{
  // menampilkan no. urut data
   xlsWriteNumber($noBarisCell,0,$noData);

   // menampilkan data nim
   xlsWriteLabel($noBarisCell,1,$data['keyword']);

   // menampilkan data nama mahasiswa
   xlsWriteLabel($noBarisCell,2,$data['in_sender']);

   // menampilkan data nilai
   xlsWriteLabel($noBarisCell,3,$data['in_message']);

   // menampilkan status kelulusan
   xlsWriteLabel($noBarisCell,4,$data['in_datetime']);
   // increment untuk no. baris cell dan no. urut data
   $noBarisCell++;
   $noData++;
}

// memanggil function penanda akhir file excel
xlsEOF();
exit();

?>

