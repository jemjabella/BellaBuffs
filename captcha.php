<?php
session_start();
$md5 = md5(microtime() * mktime());
$string = substr($md5,0,5);

$captcha = imagecreatefromjpeg("captcha.jpg");
$black = imagecolorallocate($captcha, 0, 0, 0);
$line = imagecolorallocate($captcha,233,239,239);
imageline($captcha,0,0,39,29,$line);
imageline($captcha,40,0,64,29,$line);
imagestring($captcha, 5, 20, 10, $string, $black);

$_SESSION['key'] = md5($string);

header("Content-type: image/jpeg");
imagejpeg($captcha);
?>