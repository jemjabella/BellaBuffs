<?php
require_once('prefs.php');

$error_msg = null;
$result = null;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (isBot() !== false)
		$error_msg .= "No bots please! UA reported as: ".$_SERVER['HTTP_USER_AGENT'];

	// lets check a few things - not enough to trigger an error on their own, but worth assigning a spam score..
	// score quickly adds up therefore allowing genuine users with 'accidental' score through but cutting out real spam :)
	$points = (int)0;

	$badwords = file(SPAMWDS);

	foreach ($badwords as $word)
		if (
			strpos(strtolower($_POST['comments']), $word) !== false ||
			strpos(strtolower($_POST['name']), $word) !== false
		)
			$points += 2;

	if (strpos($_POST['comments'], "http://") !== false || strpos($_POST['comments'], "www.") !== false)
		$points += 2;
	if (isset($_POST['nojs']))
		$points += 1;
	if (preg_match("/(<.*>)/i", $_POST['comments']))
		$points += 2;
	if (strlen($_POST['name']) < 3)
		$points += 1;
	if (strlen($_POST['comments']) < 15 || strlen($_POST['comments'] > 1500))
		$points += 2;
	// end score assignments

	foreach($requiredFields as $field) {
		trim($_POST[$field]);

		if (!isset($_POST[$field]) || empty($_POST[$field]))
			$error_msg .= "Please fill in all the required fields and submit again.\r\n";
	}

	if (!preg_match("/^[a-zA-Z-'\s]*$/", stripslashes($_POST['name'])))
		$error_msg .= "The name field is required, and must not contain special characters.\r\n";
	if (!preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', strtolower($_POST['email'])))
		$error_msg .= "The e-mail field is required, and must be a valid e-mail address.\r\n";
	if (!empty($_POST['url']) && !preg_match('/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', $_POST['url']))
		$error_msg .= "Invalid website url.\r\n";

	if ($error_msg == NULL && $points <= $maxPoints) {
		$subject = "Contact form submission from ". $title;

		$message = "You received this e-mail message through your fanlisting: \n\n";
		foreach ($_POST as $key => $val) {
			$message .= ucwords($key) . ": " . clean($val) . "\r\n";
		}
		$message .= "\r\n";
		$message .= 'IP: '.$_SERVER['REMOTE_ADDR']."\r\n";
		$message .= 'Browser: '.$_SERVER['HTTP_USER_AGENT']."\r\n";
		$message .= 'Points: '.$points;

		if (strstr($_SERVER['SERVER_SOFTWARE'], "Win")) {
			$headers = "From: {$admin_email}\n";
			$headers .= "Reply-To: {$_POST['email']}";
		} else {
			$headers = "From: {$title} <{$admin_email}>\n";
			$headers .= "Reply-To: {$_POST['email']}";
		}

		if (mail($admin_email,$subject,$message,$headers)) {
			$result = 'Your mail was successfully sent.';
			$disable = true;
		} else {
			$error_msg = 'Your mail could not be sent this time. ['.$points.']';
		}
	} else {
		if (empty($error_msg))
			$error_msg = 'Your mail looks too much like spam, and could not be sent this time. ['.$points.']';
	}
}
include_once('header.php');
?>
<h1>Contact Admin</h1>
<p>This form is for contacting the fanlisting owner only -- it is not to be used to join the fanlisting unless you have been directed to do so. (Name, e-mail and comments are required fields.)</p>

<?php
if ($error_msg != NULL)
	echo '<p class="error">ERROR: '. nl2br($error_msg) . "</p>";

if ($result != NULL)
	echo '<p class="success">'. $result . "</p>";
?>

<form action="contact.php" method="post">
<noscript>
	<p><input type="hidden" name="nojs" id="nojs" /></p>
</noscript>
<p>
	<label for="name">Name *</label><br /> <input type="text" id="name" name="name" value="<?php get_data("name"); ?>" /> <br />
	<label for="email">E-mail *</label><br /> <input type="text" id="email" name="email" value="<?php get_data("email"); ?>" /> <br />
	<label for="url">Website</label><br /> <input type="text" id="url" name="url" value="http://" /> <br />
	<label for="reason">Reason for contact</label><br /> <select name="reason" id="reason">
								<option value="affiliate-request">Affiliate Request</option>
								<option value="couldnt-join">Joining Problem</option>
								<option value="button-donation">Button Donation</option>
								<option value="other">Other</option>
	</select> <br />
	<label for="comments">Comments *</label><br /> <textarea name="comments" id="comments" rows="3" cols="25"><?php get_data("comments"); ?></textarea><br />
</p>
<p>
	<input type="submit" name="submit" id="submit" value="Send" <?php if (isset($disable) && $disable === true) echo ' disabled="disabled"'; ?> />
</p>
</form>

<?php include('footer.php'); ?>