<?php if (!is_writable(MEMBERS) || !is_writable(NEWBIES) || !is_writable(SPAMWDS) || !is_writable(BUTTONS) || !is_writable(AFFILIATES) || !is_writable(UPDATES)) {
	echo "<p>This script cannot run unless the .txt files have been uploaded and have write permissions. Please ensure they are CHMODed/have permissions set to 666.</p>";
	exit;
} ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="stylesheet.css" rel="stylesheet" type="text/css" />

<title><?php echo $title; ?> - Powered by BellaBuffs</title>
</head>
<body>



<ul id="navigation">
<li><a href="index.php">Index</a></li>
<li><a href="buttons.php">Buttons</a></li>
<li><a href="join.php">Join</a></li>
<li><a href="members.php">Members</a></li>
<li><a href="affiliates.php">Affiliates</a></li>
<li><a href="contact.php">Contact</a></li>
</ul>




