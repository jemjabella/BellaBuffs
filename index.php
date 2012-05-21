<?php
require_once('prefs.php');
include_once('header.php');
?>

<h1>Welcome to <?php echo $title; ?></h1>





<p>
<strong>Members:</strong> <?php countfile(MEMBERS); ?><br />
<strong>Pending:</strong> <?php countfile(NEWBIES); ?><br />
<strong>Last Update:</strong> <?php lastupdate(); ?>
</p>

<?php include('footer.php'); ?>