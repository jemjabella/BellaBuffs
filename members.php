<?php 
require_once('prefs.php');
include_once('header.php');

if(!fopen(MEMBERS, "r")) { 
	echo "<p>Could not open members file. Please verify permissions (CHMOD - 666) and actual existence.</p>";
} else {
	if (filesize(MEMBERS) > 0) {
		$members = file(MEMBERS);
		$queryURL = "";

		if (isset($_GET['s']) && $_GET['s'] == "sortName") {
			natcasesort($members);
			$members = array_values($members);

			$queryURL = "&amp;s=sortName";
		} elseif (isset($_GET['s']) && $_GET['s'] == "sortCountry") {
			$countryArray = file(COUNTRIES);
			foreach($countryArray as $country) {
				$countryArray[] = rtrim($country);
			}
			if (!isset($_GET['c']) || in_array($_GET['c'], $countryArray) === FALSE) {
				// find out who has joined with which country and stick them in an array
				foreach ($members as $mem) {
					list($name,$email,$dispemail,$url,$country,$fave) = preg_split("/,(?! )/",$mem);
					$newArray[] = $country;
				}
				// count each time country occurs
				$countryCount = array_count_values($newArray);

				// sort the array so that the countries are in alphabetical order
				$newArray = array_unique($newArray);
				natcasesort($newArray);

				// display the countries
				echo "<ul>";
				foreach ($newArray as $country) {
					foreach ($countryCount as $key => $cc) {
						if ($key == $country) {
							echo "<li><a href=\"members.php?s=sortCountry&amp;c=".urlencode($country)."\">$country</a> ($cc members)</li>";
						}
					}
				}
				echo "</ul>";
				exit(include("footer.php"));
			} else {
				foreach ($members as $key => $memb) {
					list($name,$email,$dispemail,$url,$country,$fave) = preg_split("/,(?! )/",$memb);
					if (preg_match("/($_GET[c])/i", $country)) {
						$NEWmembers[] = "$name,$email,$dispemail,$url,$country,$fave";
					}
				}
				
				$members = $NEWmembers;
				natcasesort($members);
				$members = array_values($members);
				unset($NEWmembers);

				$queryURL = "&amp;s=sortCountry&amp;c=".urlencode($country);
			}
		}

		$count = count($members);
		$numpages = ceil($count/$perpage);

		if ($perpage < $count) {
			echo "<p style=\"text-align: center;\">$count members | ";

			if (isset($_GET['page']) && $_GET['page'] > 1  && $_GET['page'] <= $numpages) {
				$prev = $_GET['page'] - 1;
				echo "<a href='members.php?page={$prev}$queryURL'>Prev</a> &middot; ";
			} else {
				echo "Prev &middot; ";
			}
			for ($x=1; $x<=$numpages; $x++) {
					if ((isset($_GET['page']) && $x == $_GET['page']) || (!isset($_GET['page']) &&  $x == 1)) {
						echo "<strong>$x</strong> ";
					} else {
						echo "<a href=\"members.php?page=$x$queryURL\">$x</a> ";
					}
			}
			if ((!isset($_GET['page'])) || (isset($_GET['page']) && $_GET['page'] < $numpages)) {
				if (!isset($_GET['page'])) {
					$_GET['page'] = 1;
				}
				$next = $_GET['page'] + 1;
				echo " &middot; <a href='members.php?page={$next}$queryURL'>Next</a>";
			} else {
				echo " &middot; Next";
			}
			echo  "</p> \n\n ";
		} else {
			echo "<p style=\"text-align: center;\">$count members</p>";
		}
		if (isset($_GET['page']) && is_numeric($_GET['page'])) {
			$i=$perpage*($_GET['page']-1);
		} else {
			$i=0;
		}
		$end = $i + $perpage;
	
		if ($end > $count) { $end = $count; }

?>
		<table style="width: 100%;">
		<tr> <th>Name</th> <th>E-mail</th> <th>Website</th> <th>Country</th> <?php if (isset($favefield) && $favefield == "yes") { echo "<th>{$favetext}</th>"; } ?> </tr>
<?php 
		while ($i<$end) {
			list($name,$email,$dispemail,$url,$country,$fave) = preg_split("/,(?! )/",$members[$i]);
		
			$fave = trim($fave, "\"\x00..\x1F");

			if ($dispemail == "yes") {
				// this bit of javascript prevents the email address being picked up by bots
				$email = "
						<script type=\"text/javascript\">
						 <!--//
						document.write('<a href=\"mailto:".fixEmail($email)."\">e-mail<\/a>');
						 //-->
						</script>
				";
			} else {
				$email = "<del>e-mail</del>";
			}
			if (empty($url) || $url == "http://") $url = "<del>www</del>"; else $url = "<a href=\"$url\" title=\"$name's website\">www</a>";
	
			echo "
				<tr> <td>$name</td> 
				<td>$email</td>
				<td>$url</td> <td>$country</td>
			";
			if (isset($favefield) && $favefield == "yes") { echo "<td>" . str_replace('|', ',', stripslashes($fave)) . "</td>"; }
			echo "</tr>";

			$i++;
		}
?>
		</table>

		<p><a href="members.php?s=sortName">Sort by Name</a> &middot; <a href="members.php?s=sortCountry">Sort by Country</a></p>
<?php 

	} else {
		echo "<p>No members have joined yet!</p>";
	}
}
include('footer.php'); ?>