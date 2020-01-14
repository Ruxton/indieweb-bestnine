<?php
namespace BestNine;

$host = $_SERVER['HTTP_HOST'];
$path = "/indieweb-bestnine";
// $path = "";

$siteUrl = $_POST['uri'];

$year = intval($_POST['year']);
$month = intval($_POST['month']);
$direction = 'next';

$siteKey = hash('sha256',trim($siteUrl.$year.$month));

$headerCheck = @get_headers($siteUrl);
if(!$headerCheck || $headerCheck[0] == 'HTTP/1.1 404 Not Found') {
    $siteExists = false;
}
else {
  $siteExists = true;
  if(!isset($siteUrl) || !$siteExists ) {
  	header("Location: http://$host$path/nourl.php");
  } else {
  	header("Location: http://$host$path/result.php?key=".$siteKey);
  	header('Connection: close');
  	flush();
  	if (session_id()) session_write_close();
    $logfile = __DIR__.DIRECTORY_SEPARATOR."logs".DIRECTORY_SEPARATOR."$siteKey.txt";
    file_put_contents($logfile, $siteUrl."\n", FILE_APPEND);
  	$file = __DIR__.DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."$siteKey.jpg";
  	if(file_exists($file)) {
  		exit;
  	} else {
  		$exec = "/usr/bin/php ".__DIR__.DIRECTORY_SEPARATOR."background_image_builder.php $siteUrl $year $month >> $logfile 2>>$logfile &";
  		exec($exec);
  	}
  }
}
?>
