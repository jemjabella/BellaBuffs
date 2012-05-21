<?php
$classA = "literow";
$classB = "darkrow";

define("MEMBERS", "members.txt");
define("NEWBIES", "newbies.txt");
define("IPBLOCKLST", "ipblock.txt");
define("SPAMWDS", "spamwds.txt");
define("COUNTRIES", "countries.txt");
define("BUTTONS", "buttons.txt");
define("AFFILIATES", "affiliates.txt");
define("UPDATES", "updates.txt");


function cleanUp($text) {
	$text = strip_tags($text);
	$text = str_replace(',', '|', str_replace('\r', '', str_replace('\n', '', trim(htmlentities($text)))));
	
	return $text;
}
function isBot() {
	$bots = array("Indy", "Blaiz", "Java", "libwww-perl", "Python", "OutfoxBot", "User-Agent", "PycURL", "AlphaServer", "T8Abot", "Syntryx", "WinHttp", "WebBandit", "nicebot", "Teoma", "alexa", "froogle", "inktomi", "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory", "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot", "crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz");

	foreach ($bots as $bot)
		if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
			return true;

	if (empty($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] == " ")
		return true;

	return false;
}


function get_countries($ThisCountry) {
	$fh = @fopen(COUNTRIES, "r") or die ("Couldn't open the country list.");
	while(!feof($fh)) {
		$country = fgetcsv($fh, 4096);
	
		for ($i=0; $i<1; $i++) {
			echo "<option value=\"$country[0]\" "; if ($ThisCountry == $country[0]) { echo "selected=\"selected\""; } echo ">$country[0]</option>";
		}
	}
	fclose($fh);
}

function lastupdate($showdetails = 'yes') {
    $updatesArray = file(UPDATES);
    foreach($updatesArray as $updateValue) {
        list($date,$update) = preg_split("/,(?! )/",$updateValue);
        echo str_replace('|', ',', $date);
        if ($showdetails == "yes" && (!empty($update) || $update != "")) {
            echo "<br /><strong>Update Details:</strong> " . stripslashes(str_replace('|', ',', $update));
        }
    }
}

function blanklinefix($inputfile) {
	ignore_user_abort(true);
	$content = file($inputfile);

	if (count($content) > 0) {
		$content = array_diff(array_diff($content, array("")), array("\n"));

		$newContent = array();
		foreach ($content as $line) {
			$newContent[] = trim($line);
		}
		$newContent = implode("\n", $newContent);
	
		$fl = fopen($inputfile, "w+");
		if (flock($fl, LOCK_EX)) {
			fwrite($fl, $newContent);
			flock($fl, LOCK_UN);
		} else {
			echo 'The file: '.$inputfile.' could not be locked for writing; the blanklinefix function could not be applied at this time.';
		}
		fclose($fl);
	}
	ignore_user_abort(false);
} 


function countfile($fileloc) {
	$file = file($fileloc);
	$count = count($file);
	echo $count;
}

function checkTXTfile($file, $input, $inputtype) {
	$Array = array();
	$fh = @fopen($file, "r") or die ("Couldn't open $file.");
	while(!feof($fh)) {
		$item = fgetcsv($fh, 4096);

		if ($inputtype == "country" || $inputtype == "ip") {
			for ($i=0; $i<1; $i++) {
				$Array[] = $item[0];
			}	
		} elseif ($inputtype == "email") {
			for ($i=0; $i<1; $i++) {
				$Array[] = $item[1];
			}
		}
	}
	fclose($fh);
	
	if (in_array($input, $Array)) {
		return true;
	} else {
		return false;
	}
}

function addmember($member) {
	$data = file_get_contents(NEWBIES);

	$fp = fopen(NEWBIES, "w") or die ("Couldn't open NEWBIES - you weren't added to the fanlisting.");
	if (flock($fp, LOCK_EX)) {
		fwrite($fp, $member);
		flock($fp, LOCK_UN);
	} else {
		echo 'The file: newbies.txt could not be locked for writing; you could not be added at this time.';
	}
	fclose($fp);

	$fp2 = fopen(NEWBIES, "a") or die ("Couldn't open NEWBIES.");
	if (flock($fp2, LOCK_EX)) {
		fwrite($fp2, $data);
		flock($fp2, LOCK_UN);
	} else {
		echo 'The file: newbies.txt could not be locked for writing; other new members could not be re-added to newbies.txt';
	}
	fclose($fp2);

	return true;
}

function breakEmail($email) {
	$email = str_replace('.', 'DOTTY', $email);
	$email = str_replace('@', 'ATTIE', $email);
	$email = str_replace('-', 'DASHY', $email);
	$email = str_replace('_', 'SCORE', $email);

	return $email;
}
function fixEmail($email) {
	$email = str_replace('DOTTY', '.', $email);
	$email = str_replace('ATTIE', '@', $email);
	$email = str_replace('DASHY', '-', $email);
	$email = str_replace('SCORE', '_', $email);

	return $email;
}

function getButtons($width, $height) {
	echo "<p> \n";
	$array = file(BUTTONS);
	foreach ($array as $value) {
		if (preg_match("/$width,$height/i", $value)) {
			list($file,$width2,$height2,$donated,$donator,$donatorUrl) = preg_split("/,(?! )/",$value);
			$donatorUrl = trim($donatorUrl);
			if ($donated == "yes") {
				if ($donatorUrl != "") {
					echo "<a href=\"$donatorUrl\" title=\"donated by $donator\"><img src=\"buttons/$file\" alt=\"{$width2}x{$height2} button\" /></a> \n";
				} else {
					echo "<img src=\"buttons/$file\" alt=\"{$width2}x{$height2} button\" title=\"donated by $donator\" /> \n";
				}
			} else {
				echo "<img src=\"buttons/$file\" alt=\"{$width2}x{$height2} button\" /> \n";
			}
		}
	}
	echo "</p> \n";
}
function getButtonSizes() {
	$array = file(BUTTONS);
	$buttons_found = array();

	foreach ($array as $value) {
		list($file,$width,$height,$donated,$donator,$donatorUrl) = preg_split("/,(?! )/",$value);
		$buttons_found[] = $width . "x" . $height;
	}
	$buttons_found = array_unique($buttons_found);
	natcasesort($buttons_found);

	echo "<ul>";
	foreach ($buttons_found as $size) {
		echo "<li><a href=\"buttons.php?p={$size}\">{$size}</a></li> \n";
	}
	echo "<li><a href=\"buttons.php?p=[0-9]x[0-9]\">View All?</a></li>";
	echo "</ul>";
}

function get_data($var) {
	if (isset($_POST[$var]))
		echo htmlspecialchars($_POST[$var]);
}

blanklinefix(COUNTRIES);
blanklinefix(IPBLOCKLST);
blanklinefix(MEMBERS);
blanklinefix(NEWBIES);
blanklinefix(SPAMWDS);
blanklinefix(BUTTONS);
blanklinefix(AFFILIATES);
blanklinefix(UPDATES);

error_reporting(E_ALL);
?>