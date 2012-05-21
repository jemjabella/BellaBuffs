<?php 
require_once('prefs.php');
include_once('header.php');
?>
<h1>Buttons (Codes)</h1>

<?php
if (filesize(BUTTONS) > 0) {
	if (isset($_GET['p'])) {
		if ($_GET['p'] != "[0-9]x[0-9]" && !ctype_alnum($_GET['p'])) {
			echo "<p>That is not a valid button size. <a href=\"buttons.php\">See all sizes?</a></p>";
			include('footer.php');
			exit;
		}

		getButtonSizes();
		list($width,$height) = preg_split("/x/",$_GET['p']);
		getButtons($width, $height);
	} else {
		getButtonSizes();
	}
} else {
	echo "<p>There are no buttons.</p>";
}

include('footer.php'); ?>