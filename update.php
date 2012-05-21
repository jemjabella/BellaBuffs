<?php
if (isset($_GET['p'])) { $page = $_GET['p']; } else { $page = ""; }

switch ($page) {
	case "process":
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

		$exploits = "/(content-type|bcc:|cc:|document.cookie|onclick|onload)/i";
		foreach ($_POST as $key => $val) {
			$clean[$key] = cleanUp($val);

			if (filesize(SPAMWDS) > 0 && (checkTXTfile(SPAMWDS, $val, "spamword") === true)) {
				echo "<p>Your application contains words in the spam list, that means you're not allowed to join at this time. \n</p>";
				exit(include('footer.php'));
			}
			if (preg_match($exploits, $val)) {
				echo "<p>No meta injection, please. \n</p>";
				exit(include('footer.php'));
			}
		}
		if ((filesize(MEMBERS) > 0 && checkTXTfile(MEMBERS, breakEmail(strtolower($clean['email'])), "email") === true) || (filesize(NEWBIES) > 0 && checkTXTfile(NEWBIES, breakEmail(strtolower($clean['email'])), "email") === true)) {
			if (empty($clean['name']) || empty($clean['email'])) {
				echo "<p>Name and e-mail are required fields. Please <a href='javascript:history.back(1)'>go back</a> and try again.\n</p>";
				exit(include('footer.php'));
			} elseif (!ereg("^[A-Za-z' -]",$clean['name']) || strlen($clean['name']) > 15) {
				echo "<p>That name is not valid. Your name must contain letters only, and must be less than 15 characters. Please <a href='javascript:history.back(1)'>go back</a> and try again.\n</p>";
				exit(include('footer.php'));
			} elseif (!ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$", strtolower($clean['email']))) {
				echo "<p>Your old e-mail address is not valid. Please <a href='javascript:history.back(1)'>go back</a> and try again.\n</p>";
				exit(include('footer.php'));
			} elseif (!empty($clean['newemail']) && !ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$", strtolower($clean['newemail']))) {
				echo "<p>Your new e-mail address is not valid. Please <a href='javascript:history.back(1)'>go back</a> and try again.\n</p>";
				exit(include('footer.php'));
			}

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
		} else {
			echo "<p>You're not a member! Only current members can update their information.</p> \n";
			include('footer.php');
			exit;
		}
	break;
	default:
		require_once('prefs.php');
		include_once('header.php');
?>

<h1>Update Your Details</h1>
<p>To update your details, fill in your information below. Please do not use this form to join; use the <a href="join.php">join form</a> instead.</p>

<p>(Name, Old E-mail and Country are required fields.)</p>

<form action="update.php?p=process" method="post"><p>
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
	break;
}
include('footer.php');
?>