<?php
require_once('prefs.php');
if (isset($_COOKIE['bellabuffs'])) {
	if ($_COOKIE['bellabuffs'] == md5($admin_name.$admin_pass.$secret)) {
		if (isset($_GET['ap'])) { $page = $_GET['ap']; } else { $page = ""; }
		include('header.php');
		switch ($page) {
			case "manage_members":
				if (isset($_GET['s']) && $_GET['s'] == "newbies") {
					$file = file(NEWBIES);
					$wording = "pending";
					$pageurl = "admin.php?ap=manage_members&amp;s=newbies";
					$fileurl = "newbies.txt";
				} else {
					$file = file(MEMBERS);
					$wording = "approved";
					$pageurl = "admin.php?ap=manage_members";
					$fileurl = "members.txt";
				}
				
				echo "<p style='color: red;'><strong>Warning:</strong> Do not try to edit multiple members at once, do not try to approve and delete the same member.</p>";
				
				$count = count($file);
				if ($count == 0) { echo '<p>No '.$wording.' members at this time.</p> <p><a href="admin.php">Back to admin panel?</a></p>'; exit(include('footer.php')); }

				echo '<p style="text-align: center;">'.$count.' '.$wording.' members | ';
				$numpages = ceil($count/$perpage);

				echo "pages: ";
				for ($x=1; $x<=$numpages; $x++) {
					echo '<a href="'.$pageurl.'&amp;page='.$x.'">';
						if (isset($_GET['page']) && $x == $_GET['page']) {
							echo "<strong>$x</strong>";
						} else {
							echo "$x";
						}
					echo "</a> ";
				}
				echo  "</p> \n\n ";
	
				if (isset($_GET['page']) && is_numeric($_GET['page'])) $i = $perpage * ($_GET['page'] - 1);
				else $i = 0;
				
				$end = $i + $perpage;
	
				if ($end > $count) $end=$count;
?>
				<form action="admin.php?ap=do_action" method="post">
				<input type="hidden" name="token" id="token" value="<?php echo md5($secret); ?>" />
				<input type="hidden" name="fileloc" id="fileloc" value="<?php if (isset($_GET['s']) && $_GET['s'] == "newbies") echo 'newbies.txt'; else echo 'members.txt' ?>" />

				<table>
				<tr> <th>Name</th> <th>E-mail</th> <th>Website</th> <th>Country</th> <?php if (isset($favefield) && $favefield == "yes") { echo "<th>Fave</th>"; } ?> <?php if (isset($_GET['s']) && $_GET['s'] == "newbies") echo '<th>Add</th>'; ?>  <th>Edit</th> <th>Delete</th>
				</tr>
<?php 
				while ($i<$end){
					$rowClass = ($i % 2) ? $classA : $classB;
					list($name,$email,$dispemail,$url,$country,$fave) = preg_split("/,(?! )/",$file[$i]);
					$fave = trim($fave, "\"\x00..\x1F");

					echo '<tr class="'.$rowClass.'">';
					$email = "<script type=\"text/javascript\"> document.write('<a href=\"mailto:" . fixEmail($email) . "\">e-mail<\/a>'); </script>";
					if (empty($url) || $url == "http://") $url = "<del>www</del>"; else $url = "<a href=\"$url\" title=\"$name's website\">www</a>";

					echo "<td>$name</td> <td>$email</td> <td>$url</td> <td>$country</td> ";
					if (isset($favefield) && $favefield == "yes") echo "<td>" . str_replace('|', ',', $fave) . "</td>";
					
					if (isset($_GET['s']) && $_GET['s'] == "newbies") 
						echo '<td><input type="checkbox" name="appr['.$i.']" value="'.$i.'"  /></td>'; 
						
					echo '<td><a href="admin.php?ap=edit_member&amp;file='.$fileurl.'&amp;mem='.$i.'"><img src="admin-icons/edit.png" title="edit" alt="edit" /></a></td>';
					echo '<td><input type="checkbox" name="del['.$i.']" value="'.$i.'" /></td>';
					echo "</tr>\r\n";

					$i++;
				}
?>
				</table>
<?php
				echo '<p><input type="submit" name="submit" id="submit" value="Update" /></p>'."\r\n</form>";

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "do_action":
				if (!isset($_POST['token']) || $_POST['token'] != md5($secret)) exit("<p>Invalid token.</p>");

				if (isset($_POST['appr']) && is_array($_POST['appr'])) {
					$newbies = file(NEWBIES);
					$approved = array();
					
					foreach ($_POST['appr'] as $member => $value) {
						if (is_numeric($member) && array_key_exists($member, $newbies)) {
							$approved[] = $newbies[$member];
							unset($newbies[$member]);
						}
					}

					$newbies = implode("", $newbies);
					$fh = fopen(NEWBIES, "w");
					fwrite($fh, $newbies);
					fclose($fh);

					if ($emailapproval == "yes") {
						$apprAmount = count($approved);
						$i = 0;
						while ($i < $apprAmount) {
							list($name,$email,$dispemail,$url,$country,$fave) = preg_split("/,(?! )/",$approved[$i]);

							$subject = "You have been approved at $title";

							$message  = $approvalMsg;
							$message .= "Name: {$name} \r\n";
							$message .= "Email: " . fixEmail($email) . " \r\n";
							$message .= "URL: {$url} \r\n";
							$message .= "Country: {$country} \r\n";
							if (isset($favefield) && $favefield == "yes") $message .= strip_tags($favetext) . ": {$fave} \r\n";	

							if (strstr($_SERVER['SERVER_SOFTWARE'], "Win")) $headers = "From: $admin_email \n";
							else $headers = "From: $title <$admin_email> \n";

							mail(fixEmail($email),$subject,$message,$headers);

							$i++;
						}
					}

					if (isset($defaultSort)) {
						if ($defaultSort == "newest") {
							$newmembers = implode("", $approved) . "\r\n";
							$olddata = file_get_contents(MEMBERS);
							
							$fp = fopen(MEMBERS, "w");
							fwrite($fp, $newmembers);
							fclose($fp);
							
							$fp = fopen(MEMBERS, "a") or die ("Couldn't open members.txt");
							fwrite($fp, $olddata);
							fclose($fp);
						} elseif ($defaultSort == "oldest") {
							$newmembers = "\r\n" . implode("", $approved);
							
							$fp = fopen(MEMBERS, "a") or die ("Couldn't open members.txt");
							fwrite($fp, $newmembers);
							fclose($fp);
						} else {
							exit("<p>Invalid sort option in prefs.php: please ensure you use 'newest' or 'oldest'.</p>");
						}
					} else {
						exit("<p>No sort option in prefs.php: please ensure you're running the latest version.</p>");
					}

					if (isset($updateDate) && $updateDate == "yes") {
						$update = "\n" . date($timestamp) . ",New member(s) added";

						$fp = fopen(UPDATES, "w") or die ("Couldn't open UPDATES - the update could not be stored.");
						fwrite($fp, $update);
						fclose($fp);
					}
					
					blanklinefix(NEWBIES);
					blanklinefix(MEMBERS);

					echo "<p>Member(s) approved.</p>";
				}
				if (isset($_POST['del']) && is_array($_POST['del'])) {
					if (isset($_POST['fileloc']))
						$fileloc = basename($_POST['fileloc']); 
					else exit;
					
					$members = file(MEMBERS);
					$newbies = file(NEWBIES);
					
					foreach ($_POST['del'] as $member => $file) {
						if (is_numeric($member)) {
							if ($fileloc == "newbies.txt" && array_key_exists($member, $newbies)) unset($newbies[$member]);
							elseif ($fileloc == "members.txt" && array_key_exists($member, $members)) unset($members[$member]);
						}
					}
					if ($fileloc == "newbies.txt") $backlink = '<a href="admin.php?ap=manage_members&amp;s=newbies">Delete other pending members?</a>'; else $backlink = '<a href="admin.php?ap=manage_members">Delete other approved members?</a>';
					
					$members = implode("", $members);
					$newbies = implode("", $newbies);

					$fh = fopen(MEMBERS, "w");
					fwrite($fh, $members);
					fclose($fh);
					
					$fb = fopen(NEWBIES, "w");
					fwrite($fb, $newbies);
					fclose($fb);
					
					echo '<p>Member(s) deleted.</p>';
				}
				echo '<p><b>Jump to:</b> <a href="admin.php?ap=manage_members">members</a> / <a href="admin.php?ap=manage_members&amp;s=newbies">pending members</a></p>';
				echo '<p><a href="admin.php">Back to admin panel?</a></p>';
			break;
			case "edit_member":
				echo "<p>Note: editing a member will not approve them. You must do this separately.</p>";

				if (!isset($_GET['mem']) || $_GET['mem'] == "" || !ctype_digit($_GET['mem'])) {
					echo "<p>You didn't select a valid member.</p>";
					include('footer.php');
					exit;
				} elseif (!isset($_GET['file']) || $_GET['file'] == "" || !file_exists($_GET['file'])) {
					echo "<p>You didn't select a valid file.</p>";
					include('footer.php');
					exit;				
				} else {
					if (is_numeric($_GET['mem'])) $mem = $_GET['mem']; else exit("Oops, not a valid member number.");
					if (file_exists($_GET['file'])) $file = $_GET['file']; else exit("Oops, the important .txt files don't exist!");
					
					$fh = fopen($file, "r");
					while(!feof($fh)) {
						$content[] = fgets($fh, 4096);
					}
					fclose($fh);

					if (empty($content[$mem])) {
						echo "<p>That member does not exist.</p>";
						include('footer.php');
						exit;
					}

					$memary = preg_split("/,(?! )/", $content[$mem]);
					if (isset($memary['5'])) {
						$memary['5'] = stripslashes(trim($memary['5'], "\"\x00..\x1F"));
					} else {
						$memary['4'] = trim($memary['4'], "\"\x00..\x1F");
					}
?>
					<form action="?ap=edit_process" method="post"><p>
					<input type="hidden" id="member" name="member" value="<?php echo $mem;?>" />
					<input type="hidden" id="file" name="file" value="<?php echo $file;?>" />
					<label><input type="text" id="name" name="name" value="<?php echo $memary['0'];?>" /> Name</label><br />
					<label><input type="text" id="email" name="email" value="<?php echo fixEmail($memary['1']);?>" /> E-mail</label><br />
					<label><input type="radio" id="dispemailyes" name="dispemail" value="yes" <?php if ($memary['2'] == "yes") { echo "checked=\"checked\""; } ?> /> Yes</label>
					<label><input type="radio" id="dispemailno" name="dispemail" value="no" <?php if ($memary['2'] == "no") { echo "checked=\"checked\""; } ?> /> No</label> Display E-mail?<br />
					<label><input type="text" id="url" name="url" value="<?php echo $memary['3'];?>" /> Website</label><br />
					<label><select name="country" id="country"><?php get_countries($memary['4']); ?></select> Country</label><br />
<?php
					if (isset($favefield) && $favefield == "yes") {
?>
						<label><input type="text" id="fave" name="fave" value="<?php echo $memary['5'];?>" /> <?php echo $favetext; ?></label><br />
<?php
					}
?>
					<input type="submit" name="submit" id="submit" value="continue" /> 
					</p></form>

<?php
				}

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "edit_process":
				foreach ($_POST as $key => $val) {
					$clean[$key] = cleanUp($val);
				}
				if (!isset($favefield) || $favefield == "no") {
					$clean['fave'] = "";
				}
				if (empty($clean['dispemail'])) {
					$clean['dispemail'] = "no";
				}

				$editedMember = $clean['name'] . "," . breakEmail($clean['email']) . "," . $clean['dispemail'] . "," . $clean['url'] . "," . $clean['country'] . "," . $clean['fave'] . "\n";

				$mem = $clean['member'];				
				$file = $clean['file'];

				$fh = fopen($file, "r");
				while(!feof($fh)) {
					$content[] = fgets($fh, 4096);
				}
				fclose($fh);

				$content[$mem] = $editedMember;
				$data = implode($content);
				$data = trim($data);

				$fp = fopen($file, "w") or die ("Couldn't open {$file}.");
				fwrite($fp, $data);
				fclose($fp);

				if ($file == "newbies.txt") {
					echo "<p>Member edited. <a href='admin.php?ap=pending_members'>Edit more pending members?</a></p>";
				} else {
					echo "<p>Member edited. <a href='admin.php?ap=approved_members'>Edit more approved members?</a></p>";
				}

				if (isset($updateDate) && $updateDate == "yes") {
					if (empty($clean['url']) || $clean['url'] == "http://") {
						$updatedMember = $clean['name'];
					} else {
						$updatedMember = "<a href=\"{$clean['url']}\">{$clean['name']}</a>";
					}
					$update = "\n" . date($timestamp) . ",Member edited: $updatedMember";

					$fp = fopen(UPDATES, "w") or die ("<p>Couldn't open UPDATES - the update could not be stored.</p>");
					fwrite($fp, $update);
					fclose($fp);
				}

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "add_spamword":
				echo "<p>These words will be blocked - if the script finds them in the join form, membership will be rejected. Add each new word separately: do <strong>not</strong> use commas to separate spam words.</p>";
				echo "<form action='admin.php?ap=add_spamword_process' method='post'><p>\n";
				echo "<label for='newspamword'>Spam Word: </label><input type='text' name='spamword' id='spamword' /> <br />\n";
				echo "<br /><input type='submit' name='submit' id='submit' value='Submit' />\n";
				echo "</p></form>\n";

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "add_spamword_process":
				if(!ctype_alnum($_POST['spamword'])) {
					echo "<p>That is not a valid spam word: they must only contain numbers and letters. No special characters.</p>";
					include('footer.php');
					exit;
				}
				
				$_POST['spamword'] = cleanUp(str_replace(',','',$_POST['spamword']));

				echo "<p>The following word is now blacklisted:</p>\n\n<p>{$_POST['spamword']}</p>\n\n";
				$newlisting = "\n".$_POST['spamword'];

				$fh = @fopen(SPAMWDS, "a");
				@fwrite($fh, $newlisting);
				fclose($fh);

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "edit_spamword":
				echo "<p>To remove a spam word, simply delete the contents of the input field.</p>";

				if (filesize(SPAMWDS) == 0) {
					echo "<p>No spam words in the list.</p>";
				} else {
					echo "\n<form action='admin.php?ap=edit_spamword_process' method='post'><p>\n";
					$fh = fopen(SPAMWDS, "r") or die ("Couldn't open the spam words file.");
					while(!feof($fh)) {
						$spamword = fgetcsv($fh, 4096);

						for ($i=0; $i<1; $i++) {
							echo "<label for='spamword'>Spam Word: </label><input type='text' name='wordlist[]' value='$spamword[0]' /> <br />\n";
						}
					}
					fclose($fh);
					echo "<br /><input type='submit' name='submit' id='submit' value='Submit' />\n</p></form>\n";
				}

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "edit_spamword_process":
				$wordlist = $_POST['wordlist'];

				echo "<p>The following words are now blacklisted:</p>\n\n<p>";
					foreach ($wordlist as $spamword) {
						echo "$spamword <br />\n";
					}
				echo "</p>";

				$wordlist = cleanUp(implode(",", $wordlist));
				$wordlist = str_replace(',,',',', $wordlist);
				$wordlist = split(',', $wordlist);
				$new_wordlist = implode("\n", $wordlist);

				$fh = fopen(SPAMWDS, "w");
				fwrite($fh, $new_wordlist);
				fclose($fh);

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "block_ip":
				echo "<p>Please note: blocking an IP will stop a user from joining your fanlisting, but not from viewing it.</p>";
				echo "<form action='admin.php?ap=block_ip_process' method='post'><p>\n";
				echo "<label for='newip'>IP Address: </label><input type='text' name='newip' id='newip' /> <br />\n";
				echo "<br /><input type='submit' name='submit' id='submit' value='Submit' />\n";
				echo "</p></form>\n";

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "block_ip_process":
				if (preg_match("^((\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)(?:\.(\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)){3})$^", cleanUp(str_replace(',','',$_POST['newip'])))) {
					echo "<p>The following IP has now been blocked:</p>\n\n<p>{$_POST['newip']}</p>\n\n";
					$newlisting = "\n".$_POST['newip'];

					$fh = @fopen(IPBLOCKLST, "a");
					fwrite($fh, $newlisting);
					fclose($fh);
				} else {
					echo "<p>That's not a valid IP address!</p>";
				}

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "edit_blocked_ips":
				echo "<p>To remove an IP, simply delete the content of the input field.</p>";

				if (filesize(IPBLOCKLST) == 0) {
					echo "<p>No blocked IPs.</p>";
				} else {
					echo "\n<form action='admin.php?ap=edit_blocked_ips_process' method='post'><p>\n";
					$fh = fopen(IPBLOCKLST, "r") or die ("Couldn't open IP block list.");
					while(!feof($fh)) {
						$blockedips = @fgetcsv($fh, 4096);

						for ($i=0; $i<1; $i++) {
							echo "<label for='blockedip'>Blocked IP: </label><input type='text' name='iplist[]' value='$blockedips[0]' /> <br />\n";
						}
					}
					fclose($fh);
					echo "<br /><input type='submit' name='submit' id='submit' value='Submit' />\n</p></form>\n";
				}

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "edit_blocked_ips_process":
				echo "<p>The following IPs are now blocked:</p>\n\n<p>";
					foreach ($_POST['iplist'] as $blockedip) {
						print "$blockedip <br />\n";
					}
				echo "</p>";

				$iplist = cleanUp(implode(",", $_POST['iplist']));
				$iplist = str_replace(',,',',', $iplist);
				$iplist = split(',', $iplist);
				$new_iplist = implode("\n", $iplist);

				$fh = @fopen(IPBLOCKLST, "w");
				@fwrite($fh, $new_iplist);
				fclose($fh);

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "add_button":
				echo "<p style='color: red;'><strong>Note:</strong> On the majority of hosts, the button folders have to have permissions set to 777 for this upload feature to work. This can constitute a security risk. Please be careful when changing the permissions of files and folders.</p>";
?>
				<form method="post" action="?ap=add_button_process" enctype="multipart/form-data"><p>
				<label><input type="file" name="file" id="file" /> Upload Button</label><br />
				<label>Donated?</label><br />
				<input type="radio" id="donatedyes" name="donated" value="yes" /> Yes 
				<input type="radio" id="donatedno" name="donated" value="no" checked="checked" /> No<br />

				<label><input type="text" id="donatorname" name="donatorname" /> Donator Name</label><br />
				<label><input type="text" id="donatorurl" name="donatorurl" /> Donator URL</label><br />
				<input type="submit" name="submit" id="submit" value="Upload" />
				</p></form>
<?php
				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "add_button_process":
				if (!is_dir("buttons/")) {
					echo "<p>The \"buttons\" directory does not exist and therefore the button could not be uploaded.</p>";
					include('footer.php');
					exit;
				}	
				if (empty($_FILES['file'])) {
					echo "<p>You did not choose an image to upload.</p>";
					include('footer.php');
					exit;
				}			
				if (getimagesize($_FILES['file']['tmp_name']) === FALSE) {
					echo "<p>That is not a valid image file.</p>";
					include('footer.php');
					exit;
				}
				list($width, $height, $type, $attr) = getimagesize($_FILES['file']['tmp_name']);
				if ($type == 1 || $type == 2 || $type == 3) {
					if (move_uploaded_file($_FILES['file']['tmp_name'], "buttons/{$_FILES['file']['name']}")) {
						echo "<p>The button was successfully uploaded. <a href='admin.php?ap=add_button'>Add another?</a></p>";

						$string = substr(md5(microtime() * mktime()),0,6);
						$ext = substr(strrchr($_FILES['file']['name'], "."), 1);

						// rename the button so that bad characters don't break things.
						if (rename("buttons/".$_FILES['file']['name'], "buttons/".$string.".".$ext)) {
							$filename = $string.".".$ext;
						} else {
							// if button could not be renamed we check for commas and delete the button if 'bad', or rely on original name if fine
							if (strpos($_FILES['file']['name'], ",") === true) {
								unlink("buttons/".$_FILES['file']['name']);
								echo "<p>File names must not contain commas.</p>";
								include('footer.php');
								exit;
							} else {
								$filename = $_FILES['file']['name'];
							}
						}

						foreach ($_POST as $key => $val) {
							$clean[$key] = cleanUp($val);
						}

						$button = "\n" . $filename . "," . $width . "," . $height . "," . $clean['donated'] . "," . $clean['donatorname'] . "," . $clean['donatorurl'];

						$fp = fopen(BUTTONS, "a") or die ("Couldn't open BUTTONS - the information about the button could not be stored.");
						fwrite($fp, $button);
						fclose($fp);
					} else {
						echo "<p>The button was not uploaded this time.</p>";
						include('footer.php');
						exit;
					}
				} else {
					echo "<p>That file extension not valid.</p>";
					include('footer.php');
					exit;
				}
				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "manage_buttons":
				if (isset($_GET['size'])) {
					list($MANwidth,$MANheight) = preg_split("/x/",$_GET['size']);
?>
				<h4>Manage Buttons: <?php echo $MANwidth;?>x<?php echo $MANheight;?></h4>
				<table>
				<tr> <th>Image</th> <th>Donated?</th> <th>Donator Name</th> <th>Donator URL</th> <th>Admin</th></tr>
<?php
					$array = file(BUTTONS);
					foreach ($array as $key => $value) {
						if (preg_match("/$MANwidth,$MANheight/i", $value)) {
							list($file,$width,$height,$donated,$donator,$donatorUrl) = preg_split("/,(?! )/",$value);
								echo "<tr> <td><img src=\"buttons/$file\" alt=\"{$width}x{$height} button\" /></td> <td>$donated</td> <td>$donator</td> <td>$donatorUrl</td> <td><a href='admin.php?ap=manage_buttons&amp;p=edit&amp;button=$key'><img src='admin-icons/edit.png' title='edit' alt='edit' /></a> <a href='admin.php?ap=manage_buttons&amp;p=del&amp;button=$key' onClick=\"javascript:return confirm('Are you sure you want to delete this button?')\"><img src='admin-icons/delete.png' title='delete' alt='delete' /></a></td> </tr>";
						}
					}
?>
				</table>
<?php
					echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
					include('footer.php');
					exit;
				} elseif (isset($_GET['p']) && $_GET['p'] == "del") {
					if (!isset($_GET['p'])) {
						echo "<p>You did not select a button to delete.</p>";
					} else {
						$button = $_GET['button'];

						$fh = @fopen(BUTTONS, "r");
						while(!feof($fh)) {
							$content[] = fgets($fh, 4096);
						}
						fclose($fh);
						list($file,$width,$height,$donated,$donator,$donatorUrl) = preg_split("/,(?! )/",$content[$button]);
						unlink("buttons/" . $file);

						unset($content[$button]);
						$data = implode("", $content);
						$data = trim($data);

						$fh = @fopen(BUTTONS, "w");
						@fwrite($fh, $data);
						fclose($fh);

						echo "<p>Button deleted. <a href=\"admin.php?ap=manage_buttons\">Manage more buttons?</a></p>";
					}

					echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
					include('footer.php');
					exit;
				} elseif (isset($_GET['p']) && $_GET['p'] == "edit") {
					if (!isset($_GET['button'])) {
						echo "<p>You did not select a button to edit.</p>";
					} else {
						$button = $_GET['button'];

						$fh = fopen(BUTTONS, "r");
						while(!feof($fh)) {
							$content[] = fgets($fh, 4096);
						}
						fclose($fh);

						if (empty($content[$button])) {
							echo "<p>That button does not exist.</p>";
							include('footer.php');
							exit;
						}

						$buttonArray = preg_split("/,(?! )/", $content[$button]);
						if (isset($buttonArray['5'])) {
							$buttonArray['5'] = stripslashes($buttonArray['5']);
							$buttonArray['5'] = trim($buttonArray['5'], "\"\x00..\x1F");
						} else {
							$buttonArray['4'] = trim($buttonArray['4'], "\"\x00..\x1F");
							$buttonArray['3'] = trim($buttonArray['3'], "\"\x00..\x1F");
						}
?>
						<form action="?ap=edit_button" method="post" enctype="multipart/form-data"><p>
							<input type="hidden" id="buttonnum" name="buttonnum" value="<?php echo $button; ?>" />
							<input type="hidden" id="filename" name="filename" value="<?php echo $buttonArray['0']; ?>" />
							<input type="hidden" id="width" name="width" value="<?php echo $buttonArray['1']; ?>" />
							<input type="hidden" id="height" name="height" value="<?php echo $buttonArray['2']; ?>" />
							<img src="buttons/<?php echo $buttonArray['0'];?>" style="vertical-align: middle;" alt="" /> Old Button<br />
							<label><input type="file" name="newbutton" id="newbutton" /> New Button</label><br />
							<label><input type="radio" id="donatedyes" name="donated" value="yes" <?php if (isset($buttonArray['3']) && $buttonArray['3'] == "yes") { echo "checked=\"checked\""; } ?> /> Yes</label>
							<label><input type="radio" id="donatedno" name="donated" value="no" <?php if (isset($buttonArray['3']) && $buttonArray['3'] == "no") { echo "checked=\"checked\""; } ?> /> No</label> Donated?<br />
							<label><input type="text" id="donatorname" name="donatorname" value="<?php echo $buttonArray['4'];?>" /> Donator Name</label><br />
							<label><input type="text" id="donatorurl" name="donatorurl" value="<?php echo $buttonArray['5'];?>" /> Donator URL</label><br />
							<input type="submit" name="submit" id="submit" value="Edit" />
						</p></form>
<?php
					}

					echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
					include('footer.php');
					exit;
				}
				$array = file(BUTTONS);
				$buttons_found = array();
				echo "<ul>";
				foreach ($array as $value) {
					list($file,$width,$height,$donated,$donator,$donatorUrl) = preg_split("/,(?! )/",$value);
					if (!in_array($width . "x" . $height, $buttons_found)) {
						$buttons_found[] = $width . "x" . $height;
						echo "<li><a href=\"admin.php?ap=manage_buttons&amp;size={$width}x{$height}\">{$width}x{$height}</a></li>";
					}
				}
				echo "</ul>";
				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "edit_button":
				foreach ($_POST as $key => $val) {
					$clean[$key] = cleanUp($val);
				}
				if ($_FILES['newbutton']['size'] > 0) {
					if (getimagesize($_FILES['newbutton']['tmp_name']) === FALSE) {
						echo "<p>That is not a valid image file.</p>";
						include('footer.php');
						exit;
					}
					list($width, $height, $type, $attr) = getimagesize($_FILES['newbutton']['tmp_name']);
					if ($type == 1 || $type == 2 || $type == 3) {
						if (move_uploaded_file($_FILES['newbutton']['tmp_name'], "buttons/{$_FILES['newbutton']['name']}")) {
							$string = substr(md5(microtime() * mktime()),0,6);
							$ext = substr(strrchr($_FILES['newbutton']['name'], "."), 1);

							// rename the button so that bad characters don't break things.
							if (rename("buttons/".$_FILES['newbutton']['name'], "buttons/".$string.".".$ext)) {
								$filename = $string.".".$ext;
							} else {
								// if button could not be renamed we check for commas and delete the button if 'bad', or rely on original name if fine
								if (strpos($_FILES['newbutton']['name'], ",") === true) {
									unlink("buttons/".$_FILES['newbutton']['name']);
									echo "<p>File names must not contain commas.</p>";
									include('footer.php');
									exit;
								} else {
									$filename = $_FILES['newbutton']['name'];
								}
							}
							unlink("buttons/".$clean['filename']);
						}
					} else {
						echo "<p>That is not a valid image file.</p>";
						include('footer.php');
						exit;
					}
				} else {
					$filename = $clean['filename'];
				}
				
				$editedButton = $filename . "," . $clean['width'] . "," . $clean['height'] . "," . $clean['donated'] . "," . $clean['donatorname'] . "," . $clean['donatorurl'] . "\n";

				$button = $clean['buttonnum'];				

				$fh = fopen(BUTTONS, "r");
				while(!feof($fh)) {
					$content[] = fgets($fh, 4096);
				}
				fclose($fh);

				$content[$button] = $editedButton;
				$data = implode($content);
				$data = trim($data);

				$fp = fopen(BUTTONS, "w") or die ("Couldn't open BUTTONS.");
				fwrite($fp, $data);
				fclose($fp);

				echo "<p>Button edited.</p>";

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "add_update":
?>
				<h4>Add New Update</h4>
				<p>If updates details is left blank, only a date will be shown.</p>
				
				<form action="admin.php?ap=update_process" method="post"><p>
				<label><input type="text" name="date" id="date" value="<?php echo date($timestamp); ?>" readonly="readonly" /> Date</label><br />
				<label><textarea id="updatedetails" name="updatedetails"></textarea> Details</label><br />
				<input type="submit" id="submit"  value="Update" />
				</p></form>
<?php
				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "update_process":
				foreach ($_POST as $key => $val) {
					$clean[$key] = cleanUp($val);
				}
				$update = "\n" . $clean['date'] . "," . $clean['updatedetails'];

				$fp = fopen(UPDATES, "w") or die ("Couldn't open UPDATES - the update could not be stored.");
				fwrite($fp, $update);
				fclose($fp);

				echo "<p>Update added.</p>";
				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "add_affiliate":
				echo "<p style='color: red;'><strong>Note:</strong> On the majority of hosts, the button folders have to have permissions set to 777 for the upload feature to work. This can constitute a security risk. Please be careful when changing the permissions of files and folders.</p>";
?>
				<form method="post" action="?ap=add_affiliate_process" enctype="multipart/form-data"><p>
				<label><input type="text" name="affName" id="affName" /> Affiliate Name</label><br />
				<label><input type="text" name="affEmail" id="affEmail" /> Affiliate E-mail</label><br />
				<label><input type="text" name="affURL" id="affURL" /> Affiliate URL</label><br />
				<label><input type="text" name="affSitename" id="affSitename" /> Affiliate Site Name</label><br />
				<label><input type="file" name="affButton" id="affButton" /> Affiliate Button</label><br />
				<input type="submit" name="submit" id="submit" value="Upload" />
				</p></form>
<?php
				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "add_affiliate_process":
				if (getimagesize($_FILES['affButton']['tmp_name']) === FALSE) {
					echo "<p>That is not a valid image file.</p>";
					include('footer.php');
					exit;
				}
				list($width, $height, $type, $attr) = getimagesize($_FILES['affButton']['tmp_name']);
				if ($type == 1 || $type == 2 || $type == 3) {
					if (move_uploaded_file($_FILES['affButton']['tmp_name'], "buttons/{$_FILES['affButton']['name']}")) {
						foreach ($_POST as $key => $val) {
							$clean[$key] = cleanUp($val);
						}

						$string = substr(md5(microtime() * mktime()),0,6);
						$ext = substr(strrchr($_FILES['affButton']['name'], "."), 1);

						// rename the button so that bad characters don't break things.
						if (rename("buttons/".$_FILES['affButton']['name'], "buttons/aff_".$string.".".$ext)) {
							$filename = "aff_".$string.".".$ext;
						} else {
							// if button could not be renamed we check for commas and delete the button if 'bad', or rely on original name if fine
							if (strpos($_FILES['affButton']['name'], ",") === true) {
								unlink("buttons/".$_FILES['affButton']['name']);
								echo "<p>File names must not contain commas.</p>";
								include('footer.php');
								exit;
							} else {
								$filename = $_FILES['affButton']['name'];
							}
						}

						$aff = "\n" . $filename . "," . $clean['affName'] . "," . breakEmail($clean['affEmail']) . "," . $clean['affURL'] . "," . $clean['affSitename'];

						$fp = fopen(AFFILIATES, "a") or die ("Couldn't open AFFILIATES - the affiliate details were not uploaded this time.");
						fwrite($fp, $aff);
						fclose($fp);

						echo "<p>The affiliate details were uploaded successfully. <a href='admin.php?ap=add_affiliate'>Add another?</a></p>";
					} else {
						echo "<p>The affiliate details were not uploaded this time.</p>";
						include('footer.php');
						exit;
					}
				} else {
					echo "<p>That file extension not valid.</p>";
					include('footer.php');
					exit;
				}
				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "manage_affiliates":
				if (isset($_GET['p']) && $_GET['p'] == "del") {
					if (!isset($_GET['p'])) {
						echo "<p>You did not select an affiliate to delete.</p>";
					} else {
						$aff = $_GET['aff'];

						$fh = @fopen(AFFILIATES, "r");
						while(!feof($fh)) {
							$content[] = fgets($fh, 4096);
						}
						fclose($fh);
						list($affButton,$affName,$affEmail,$affURL,$affSitename) = preg_split("/,(?! )/",$content[$aff]);
						unlink("buttons/" . $affButton);

						unset($content[$aff]);
						$data = implode("", $content);
						$data = trim($data);

						$fh = @fopen(AFFILIATES, "w");
						@fwrite($fh, $data);
						fclose($fh);

						echo "<p>Affiliate deleted. <a href=\"admin.php?ap=manage_affiliates\">Manage more affiliates?</a></p>";
					}

					echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
					include('footer.php');
					exit;
				} elseif (isset($_GET['p']) && $_GET['p'] == "edit") {
					if (!isset($_GET['aff'])) {
						echo "<p>You did not select an affiliate to edit.</p>";
					} else {
						$aff = $_GET['aff'];

						$fh = fopen(AFFILIATES, "r");
						while(!feof($fh)) {
							$content[] = fgets($fh, 4096);
						}
						fclose($fh);

						if (empty($content[$aff])) {
							echo "<p>That affiliate does not exist.</p>";
							include('footer.php');
							exit;
						}

						$affArray = preg_split("/,(?! )/", $content[$aff]);
						$affArray['4'] = trim($affArray['4'], "\"\x00..\x1F");
?>
						<form action="?ap=edit_affiliate" method="post" enctype="multipart/form-data"><p>
							<input type="hidden" id="affnum" name="affnum" value="<?php echo $aff; ?>" />
							<input type="hidden" id="filename" name="filename" value="<?php echo $affArray['0']; ?>" />
							<img src="buttons/<?php echo $affArray['0'];?>" style="vertical-align: middle;" alt="" /> Old Affiliate Button<br />
							<label><input type="file" name="newbutton" id="newbutton" /> New Affiliate Button</label><br />
							<label><input type="text" name="affName" id="affName" value="<?php echo $affArray['1'];?>" /> Affiliate Name</label><br />
							<label><input type="text" name="affEmail" id="affEmail" value="<?php echo fixEmail($affArray['2']);?>" /> Affiliate E-mail</label><br />
							<label><input type="text" name="affURL" id="affURL" value="<?php echo $affArray['3'];?>" /> Affiliate URL</label><br />
							<label><input type="text" name="affSitename" id="affSitename" value="<?php echo $affArray['4'];?>" /> Affiliate Site Name</label><br />
							<input type="submit" name="submit" id="submit" value="Edit" />
						</p></form>
<?php
					}

					echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
					include('footer.php');
					exit;
				}
				
?>
				<table>
				<tr> <th>Button</th> <th>Name</th> <th>Email</th> <th>URL</th> <th>Site Name</th> <th>Admin</th></tr>
<?php
				$array = file(AFFILIATES);
				foreach ($array as $key => $value) {
					list($affButton,$affName,$affEmail,$affURL,$affSitename) = preg_split("/,(?! )/",$value);
					echo "<tr> <td><img src='buttons/$affButton' alt=''></td> <td>$affName</td> <td><a href='mailto:" . fixEmail($affEmail) . "'>email</a></td> <td><a href='$affURL'>www</a></td> <td>$affSitename</td> <td><a href='admin.php?ap=manage_affiliates&amp;p=edit&amp;aff=$key'><img src='admin-icons/edit.png' title='edit' alt='edit' /></a> <a href='admin.php?ap=manage_affiliates&amp;p=del&amp;aff=$key' onClick=\"javascript:return confirm('Are you sure you want to delete this affiliate?')\"><img src='admin-icons/delete.png' title='delete' alt='delete' /></a></td> </tr>";
				}
?>
				</table>
<?php
			break;
			case "edit_affiliate":
				foreach ($_POST as $key => $val) {
					$clean[$key] = cleanUp($val);
				}
				if ($_FILES['newbutton']['size'] > 0) {
					if (getimagesize($_FILES['newbutton']['tmp_name']) === FALSE) {
						echo "<p>That is not a valid image file.</p>";
						include('footer.php');
						exit;
					}
					list($width, $height, $type, $attr) = getimagesize($_FILES['newbutton']['tmp_name']);
					if ($type == 1 || $type == 2 || $type == 3) {
						if (move_uploaded_file($_FILES['newbutton']['tmp_name'], "buttons/{$_FILES['newbutton']['name']}")) {
							$string = substr(md5(microtime() * mktime()),0,6);
							$ext = substr(strrchr($_FILES['newbutton']['name'], "."), 1);

							// rename the button so that bad characters don't break things.
							if (rename("buttons/".$_FILES['newbutton']['name'], "buttons/".$string.".".$ext)) {
								$filename = $string.".".$ext;
							} else {
								// if button could not be renamed we check for commas and delete the button if 'bad', or rely on original name if fine
								if (strpos($_FILES['newbutton']['name'], ",") === true) {
									unlink("buttons/".$_FILES['newbutton']['name']);
									echo "<p>File names must not contain commas.</p>";
									include('footer.php');
									exit;
								} else {
									$filename = $_FILES['newbutton']['name'];
								}
							}
							unlink("buttons/".$clean['filename']);
						}
					} else {
						echo "<p>That is not a valid image file.</p>";
						include('footer.php');
						exit;
					}
				} else {
					$filename = $clean['filename'];
				}
				$editedAff = $filename . "," . $clean['affName'] . "," . breakEmail($clean['affEmail']) . "," . $clean['affURL'] . "," . $clean['affSitename'] . "\n";
				$aff = $clean['affnum'];				

				$fh = fopen(AFFILIATES, "r");
				while(!feof($fh)) {
					$content[] = fgets($fh, 4096);
				}
				fclose($fh);

				$content[$aff] = $editedAff;
				$data = implode($content);
				$data = trim($data);

				$fp = fopen(AFFILIATES, "w") or die ("Couldn't open AFFILIATES.");
				fwrite($fp, $data);
				fclose($fp);

				echo "<p>Affiliate edited.</p>";

				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "email_affiliates":
				if (isset($_GET['p']) && $_GET['p'] == "process") {
					foreach ($_POST as $key => $val) {
						$clean[$key] = stripslashes(trim($val));
					} 

					$subject = "E-mail from the $FLsubject fanlisting";

					if (strstr($_SERVER['SERVER_SOFTWARE'], "Win")) {
						$clean['to'] = str_replace('>', '', $clean['to']);
						$clean['to'] = str_replace('<', '', $clean['to']);

						$headers   = "From: $admin_email \n";
						$headers  .= "Reply-To: $admin_email";
					} else {
						$headers   = "From: $title <$admin_email> \n";
						$headers  .= "Reply-To: <$admin_email>";
					}

					if (mail($clean['to'],$subject,$clean['message'],$headers)) {
						echo "<p>E-mail sent!</p>";
						echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
					} else {
						echo "<p>The e-mail could not be sent at this time.</p>";
						echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
					}
					exit;
				}

				$array = file(AFFILIATES);
?>
				<form action="admin.php?ap=email_affiliates&amp;p=process" method="post"><p>
				<label><textarea name="to" id="to" style='width: 350px; height: 80px; vertical-align: middle;'>
<?php
				$emailArray = array();

				foreach ($array as $value) {
					list($affButton,$affName,$affEmail,$affURL,$affSitename) = preg_split("/,(?! )/",$value);
					$emailArray[$affName] = $affEmail;
				}
				$emailArray = array_unique($emailArray);

				foreach($emailArray as $key => $value) {
					if (!empty($value)) {
						echo "$key <".fixEmail($value).">, ";
					}
				}
?>
				</textarea> To</label><br />
				<label><textarea name="message" id="message" style='width: 350px; height: 220px; vertical-align: middle;'></textarea> Message</label><br />
				<input type="submit" id="submit" name="submit" value="send" />
				</p></form>
<?php
				echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
			break;
			case "search":
				if (isset($_GET['p']) && $_GET['p'] == "process") {
					if (!ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$", strtolower($_POST['email']))) {
						echo "<p>That is not a valid e-mail address.</p>";
						echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
						include('footer.php');
						exit;
					}
					if (filesize(MEMBERS) > 0 && checkTXTfile(MEMBERS, breakEmail($_POST['email']), "email") === true) {
						$file = MEMBERS;
					} elseif (filesize(NEWBIES) > 0 && checkTXTfile(NEWBIES, breakEmail($_POST['email']), "email") === true) {
						$file = NEWBIES;
					}
					if (!isset($file)) {
						echo "<p>Something went horribly, drastically wrong! Run for your life!</p>";
						echo "<p>...</p>";
						echo "<p>Just kidding &#8212; that member does <strong>not</strong> exist.</p>";
						echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
						include('footer.php');
						exit;
					}

					$members = file($file);
					foreach ($members as $key => $value) {
						if (preg_match("/(".breakEmail($_POST['email']).")/i", $value)) {
							list($name,$email,$dispemail,$url,$country,$fave) = preg_split("/,(?! )/",$value);
							if (empty($url) || $url == "http://" || $url == "") {
								$url = "(none)";
							} else {
								$url = "<a href='$url'>website</a>";
							}
?>
<p>Search results:</p>

<table>
<tr> <th>Name</th> <th>E-mail</th> <th>Website</th> <th>Country</th> <?php if (isset($favefield) && $favefield == "yes") { echo "<th>Fave</th>"; } ?> <th>Admin</th></tr>
<tr> <td><?php echo $name; ?></td> 
<td><?php echo "<a href='mailto:".fixEmail($email)."'>email</a>"; ?></td>
<td><?php echo $url; ?></td>
<td><?php echo $country; ?></td>
<?php						if (isset($favefield) && $favefield == "yes") { ?>
	<td><?php echo $fave; ?></td>
<?php						}
?>
<td><a href='admin.php?ap=edit_member&amp;file=<?php echo $file; ?>&amp;mem=<?php echo $key; ?>'><img src='admin-icons/edit.png' title='edit' alt='edit' /></a> <a href='admin.php?ap=delete_member&amp;file=<?php echo $file; ?>&amp;mem=<?php echo $key; ?>' onClick="javascript:return confirm('Are you sure you want to delete this member?')"><img src='admin-icons/delete.png' title='delete' alt='delete' /></a>
</tr>
</table>

<?php
						echo "<p><a href='admin.php'>Back to admin panel?</a></p>";
						}
					}
					include('footer.php');
					exit;
				}
?>
				<p>Search for member by e-mail address:</p>
				<form action="admin.php?ap=search&amp;p=process" method="post"><p>
				<label><input type="text" name="email" id="email" /> E-mail</label><br />
				<input type="submit" name="submit" id="submit" value="Search" />
				</form>
<?php
			break;
			default:
				echo "<h4>BellaBuffs Admin Panel</h4>";
?>
				<ul>
				<li><a href="admin.php?ap=manage_members">Manage Approved Members</a> (<?php countfile(MEMBERS); ?>)</li>
				<li><a href="admin.php?ap=manage_members&amp;s=newbies">Manage Pending Members</a> (<?php countfile(NEWBIES); ?>)</li>
				<li><a href="admin.php?ap=search">Search for Member</a></li>
				</ul>
				<ul>
				<li><a href="admin.php?ap=add_update">Add Update</a></li>
				</ul>
				<ul>
				<li><a href="admin.php?ap=add_button">Add Button</a></li>
				<li><a href="admin.php?ap=manage_buttons">Manage Buttons</a> (<?php countfile(BUTTONS); ?>)</li>
				</ul>
				<ul>
				<li><a href="admin.php?ap=add_affiliate">Add Affiliate</a></li>
				<li><a href="admin.php?ap=manage_affiliates">Manage Affiliates</a> (<?php countfile(AFFILIATES); ?>)</li>
				<li><a href="admin.php?ap=email_affiliates">E-mail Affiliates</a></li>
				</ul>
				<ul>
				<li><a href="admin.php?ap=add_spamword">Add Spam Word</a></li>
				<li><a href="admin.php?ap=edit_spamword">Edit Spam Words</a> (<?php countfile(SPAMWDS); ?>)</li>
				<li><a href="admin.php?ap=block_ip">Block IP Address</a></li>
				<li><a href="admin.php?ap=edit_blocked_ips">Edit Blocked IPs</a> (<?php countfile(IPBLOCKLST); ?>)</li>
				</ul>
				<ul>
				<li><a href="logout.php">Logout</a></li>
				</ul>
<?php
			break;
		}
		include('footer.php');
		exit;
	} else {
		echo "<p>Bad cookie. Clear 'em out and start again.</p>";
		include('footer.php');
		exit;
	}
}
if (isset($_GET['p']) && $_GET['p'] == "login") {
	if ($_POST['name'] != $admin_name || $_POST['pass'] != $admin_pass) {
		include('header.php');
		echo "<p>Sorry, that username and password combination does not match. Please try again.</p>";
?>
		<form action="admin.php?p=login" method="post"><fieldset>
		<label><input type="text" name="name" id="name" /> Name</label><br />
		<label><input type="password" name="pass" id="pass" /> Password</label><br />
		<input type="submit" id="submit"  value="Login" />
		</fieldset></form>
<?php
		include('footer.php');
		exit;
	} elseif ($_POST['name'] == $admin_name && $_POST['pass'] == $admin_pass) {
		setcookie('bellabuffs', md5($_POST['name'].$_POST['pass'].$secret), time()+(31*86400));
		header("Location: admin.php");
	} else {
		include('header.php');
		echo "<p>Sorry, you could not be logged in at this time. Please try again.</p>";
?>
		<form action="admin.php?p=login" method="post"><fieldset>
		<label><input type="text" name="name" id="name" /> Name</label><br />
		<label><input type="password" name="pass" id="pass" /> Password</label><br />
		<input type="submit" id="submit"  value="Login" />
		</fieldset></form>
<?php
		include('footer.php');
		exit;
	}
	exit;
}
include('header.php');
?>

<form action="admin.php?p=login" method="post"><p>
<label><input type="text" name="name" id="name" /> Name</label><br />
<label><input type="password" name="pass" id="pass" /> Password</label><br />
<input type="submit" id="submit"  value="Login" />
</p></form>

<?php
include('footer.php');
?>