<?php
if (isset($_COOKIE['bellabuffs'])) {
	setcookie('bellabuffs', "");
	header("Location: logout.php");
	exit;
}
include('prefs.php');
include('header.php');

echo "<p>You are now logged out.</p>";

include('footer.php');
?>