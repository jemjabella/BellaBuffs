<?php
$show_form = true;
$error_msg = NULL;

if (isset($_POST['submit'])) {
	require_once('prefs.php');
	if (isset($captcha) && $captcha == "yes") {
		session_start();
		if (isset($_SESSION['key'])) {
			if(md5($_POST['captcha']) != $_SESSION['key']) {
				setcookie(session_name(), '', time()-36000, '/');
				$_SESSION = array();
				session_destroy();

				echo "<p>The text you entered didn't match the image, please <a href='update.php'>try again</a>.</p>";
				include('footer.php');
				exit;
			}
			if (isset($_SESSION['key']) && isset($_COOKIE[session_name()])) {
				setcookie(session_name(), '', time()-36000, '/');
				$_SESSION = array();
				session_destroy();
			}
		} else {
			echo "<p>The text you entered didn't match the image, please <a href='update.php'>try again</a>.</p>";
			include('footer.php');
			exit;
		}
	}
	include_once('header.php');

	if (!isset($_POST['submit']) || $_SERVER['REQUEST_METHOD'] != "POST") {
		echo "<p>Accessing this page directly is not allowed.</p>\n\n";
		include('footer.php');
		exit;
	}
	
	if (isBot() !== false)
		$error_msg .= "No bots please! UA reported as: ".$_SERVER['HTTP_USER_AGENT'] . "\r\n";
	
	if (substr_count($_POST['comments'], 'http://') > 1)
		$error_msg .= "Too many URLs; we've assumed you're spam and 'lost' your application. Please try again without any extra URLs if you're a geniune person :)\r\n";

	$exploits = "/(content-type|bcc:|cc:|document.cookie|onclick|onload|javascript|alert)/i";
	if (filesize(SPAMWDS) > 0) $spamlist = file(SPAMWDS);

	foreach ($_POST as $key => $val) {
		if (isset($spamlist)) {
			foreach ($spamlist as $spamword) {
				if (preg_match("/(".trim($spamword).")/i", $val)) {
					$error_msg .= "Your updated info contains words in the spam list, please go back and remove references to obvious 'spam' material.\r\n";
				}
			}
		}
		if (preg_match($exploits, $val))
			$error_msg .= "No meta injection, please.\r\n";

		if (preg_match("/(<.*>)/i", $val))
			$error_msg .= "No HTML, please.\r\n";

		$clean[$key] = cleanUp($val);
	}
	if ((filesize(MEMBERS) > 0 && checkTXTfile(MEMBERS, breakEmail(strtolower($clean['email'])), "email") === true) || (filesize(NEWBIES) > 0 && checkTXTfile(NEWBIES, breakEmail(strtolower($clean['email'])), "email") === true)) {
		// set default values for empty/unset fields
		if (empty($clean['dispemail']))
			$clean['dispemail'] = "no";

		if (!isset($favefield) || $favefield == "no" || !isset($clean['fave']))
			$clean['fave'] = NULL;

		// let's do some security and spam checks
		if (empty($clean['name']) || empty($clean['email']) || empty($clean['country']))
			$error_msg .= "Name, e-mail and country are required fields. \r\n";
		if (!preg_match("/^[a-zA-Z-'\s]*$/", $clean['name']))
			$error_msg .= "That name is not valid. Your name must contain letters only, and must be less than 15 characters. \r\n";
		if ($clean['dispemail'] != "yes" && $clean['dispemail'] != "no")
			$error_msg .= "You didn't choose whether or not you'd like to show your e-mail address on the member list. \r\n";
		if ($clean['fave'] != "" && (!preg_match("/^[a-zA-Z0-9-'\s]*$/", $clean['fave']) || strlen($clean['fave']) > 20))
			$error_msg .= "Your chosen \"favourite\" is not valid. It must contain letters and numbers only, and must be less than 20 characters. \r\n";
		if (!preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', strtolower($clean['email'])))
			$error_msg .= "The email address you have used is not valid. \r\n";
		if (!empty($clean['newemail']) && !preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', strtolower($clean['newemail'])))
			$error_msg .= "The new email address you have used is not valid. \r\n";
		if (!empty($clean['url']) && !preg_match('/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', $clean['url']))
			$error_msg .= "The website url you provided is not valid. Please remove and try again or fix the URL.\r\n";
		if ($clean['country'] == "null" || (filesize(COUNTRIES) > 0 && checkTXTfile(COUNTRIES, $clean['country'], "country") === false))
			$error_msg .= "Please select a valid country. \r\n";

		if ($error_msg == NULL) {
			$show_form = false;
			
			$subject = "Update member at $title";

			$message  = "A member at your $FLsubject fanlisting wants updating with following details: \n\n";

			$message .= "Name: {$clean['name']} \n";
			$message .= "Email: ".strtolower($clean['email'])." \n";
			$message .= "New Email: {$clean['newemail']} \n";
			$message .= "New URL: {$clean['newurl']} \n";
			$message .= "Country: {$clean['country']} \n";
			$message .= "Comments: {$clean['comments']} \n";
			$message .= "IP: {$_SERVER['REMOTE_ADDR']} \n\n";

			$message .= "Manage members: {$FLurl}/admin.php";

			if (!strstr($_SERVER['SERVER_SOFTWARE'], "Win")) {
				$headers   = "From: $admin_email \n";
				$headers  .= "Reply-To: $clean[email]";
			} else {
				$headers   = "From: $title <$admin_email> \n";
				$headers  .= "Reply-To: <$clean[email]>";
			}

			if (mail($admin_email,$subject,$message,$headers)) {
				echo "<h1>Update Sent!</h1> \n <p>Your updated information has been sent.</p>";
			} else {
				echo "<h1>Oops!</h1> \n <p>Your updated information could not be sent this time, please contact the fanlisting owner.</p>";
			}
		}
	} else {
		echo "<p>You're not a member! Only current members can update their information.</p> \n";
		include('footer.php');
		exit;
	}
}
if (!isset($_POST['submit']) || $show_form == true) {
	require_once('prefs.php');
	include_once('header.php');
?>

	<h1>Update Your Details</h1>
	<p>To update your details, fill in your information below. Please do not use this form to join; use the <a href="join.php">join form</a> instead.</p>

	<p>(Name, Old E-mail and Country are required fields.)</p>

<?php
	if ($error_msg != NULL) {
		echo "<p><strong style='color: red;'>ERROR:</strong><br />";
		echo nl2br($error_msg) . "</p>";
	}
?>

<form action="update.php" method="post"><p>
	<label>Name *</label><br /> <input type="text" id="name" name="name" /> <br />
	<label>Old E-mail *</label><br /> <input type="text" id="email" name="email" /> <br />
	<label>New E-mail</label><br /> <input type="text" id="newemail" name="newemail" /> <br />
	<label>Display E-mail?</label><br />
	<input type="radio" id="dispemailyes" name="dispemail" value="yes" checked="checked" /> Yes
	<input type="radio" id="dispemailno" name="dispemail" value="no" /> No<br />
	<label>New Website?</label><br /> <input type="text" id="newurl" name="newurl" value="http://" /> <br />
	<label>Country *</label><br /> <select name="country" id="country"><option value="null">Please select a country:</option><?php get_countries("null"); ?></select> <br />
<?php
	if (isset($captcha) && $captcha == "yes") {
?>
	<img src="captcha.php" alt="" /><br />
	<label>Captcha</label><br /> <input type="text" name="captcha" id="captcha" /> <br />
<?php
	}
?>
	<label>Comments</label><br /> 
	<textarea name="comments" id="comments" rows="3" cols="25"></textarea><br />
	<input type="submit" name="submit" id="submit" value="Update" /> 
</p></form>

<?php
}
include('footer.php');
?>