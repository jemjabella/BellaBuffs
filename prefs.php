<?php
// FANLIST SETTINGS
$title = "My Fanlisting";   // name of fanlisting
$FLsubject = "subject";    // subject of fanlisting (e.g "coffee")
$FLurl = "http://yourdomain.com/fanlisting";   // url of fanlisting - no trailing slash (don't add a '/' at the end)!


// ADMIN SETTINGS
$admin_name = "admin";   // admin username
$admin_pass = "password";   // admin password
$admin_email = "you@your-domain.com";   // admin e-mail address
$secret = "pleasechangeme123";   // this is like a second password. you won't have to remember it, so make it random


// EMAIL SETTINGS
$emailnewbies = "no";	// (yes or no) email new members after joining ($admin_email must be filled in, above)
$emailadmin = "no";   // (yes or no) email admin when new member joins ($admin_email must be filled in, above)
$emailapproval = "no";   // (yes or no) email member when approved ($admin_email must be filled in, above)

// message e-mailed to new members on join. use \n for a new line
// use \r\n for a new line, member details will automatically appear underneath
$thanksjoinMsg = "Thank you for joining my fanlisting: $title \r\nWe will process your application shortly."; 

// message e-mailed to new members on approval. 
// use \r\n for a new line, member details automatically appear underneath
$approvalMsg = "You've been approved at the fanlisting: $title with the following details: \r\n\r\n";   


// GENERAL SETTINGS
$perpage = 30;   // number of members per page
$captcha = "no";   // (yes or no) enable captcha on join form?
$favefield = "no";   // (yes or no) have a favourite field? yes or no
$favetext = "Your favourite .. ?";   // the text to display next to the fave field
$timestamp = "dS F, y";   // timestamp for last update on index.php (see php.net/date)
$updateDate = "yes"; // (yes or no) update date on index when new member approved or member edited
$defaultSort = "newest"; // newest or oldest first in the members list?
$maxPoints = 4; // max spam points a person can hit before contact refuses to submit - recommend 4



// REQUIRED TO WORK
require_once('functions.php');
?>