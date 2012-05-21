<?php 
require_once('prefs.php');
include_once('header.php');
?>
<h1>Affiliates</h1>
<p>
<?php
if (filesize(AFFILIATES) > 0) {
	$array = file(AFFILIATES);
	foreach ($array as $value) {
		list($affButton,$affName,$affEmail,$affURL,$affSitename) = preg_split("/,(?! )/",$value);
		echo "<a href=\"$affURL\" title=\"affiliate: $affName of $affSitename\"><img src=\"buttons/$affButton\" alt=\"$affSitename button\" /></a>";
	}
} else {
	echo "There are no affiliates.";
} ?>
</p>

<?php include('footer.php'); ?>