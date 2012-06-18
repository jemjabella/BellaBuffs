//--------------------
// READ ME
//--------------------
BellaBuffs v2 Copyright © Jem Turner 2006-2012

You are free to customise BellaBuffs (php code, style, appearance) 
as much as you like providing the link to http://www.jemjabella.co.uk 
remains ON the fanlisting management script. Because I'm a tightarse.

Installation of BellaBuffs is at your own risk. By using BellaBuffs
you agree not to hold Jem Turner of jemjabella.co.uk responsible for
any damages that may occur upon installing BellaBuffs or related plugins.
You also agree not to sell copies of the script, or services relating to the
script (i.e. installation, customisation, etc) without written permission
of Jem Turner. Because I'm a tightarse.

Support is currently on hold while I pop out sprogs. 
Check the Girls Who Geek forums as most questions have already been answered:
http://girlswhogeek.com/forums/forum/jemjabella-scripts



//--------------------
// INSTRUCTIONS
//--------------------
1. Customise prefs.php - set your username, password and various preferences (yes or no etc)
2. Upload all of the files to a directory 
3. CHMOD all of the txt files to 666 - this makes them writeable
4. CHMOD the buttons directory to 777 
(BEWARE: can be a security risk! CHMOD 'buttons' to 755 when not using the upload feature)

NOTE: some hosts run PHP as CGI, which usually eradicates the need to change
the permissions on files and folders. Try joining as a test member before 
CHMODing any files to see if this is true for you. This makes the script more
secure overall. If you get an error, CHMOD the files as above.


__________________________ HOW DO I CHMOD/CHANGE FILE PERMISSIONS?

There are lots of tutorials on CHMODing which can be found through Google:
http://www.google.com/search?q=chmod+tutorial


__________________________ HOW DO I ADD A LAYOUT TO MY FANLISTING?

The script is set up to use the popular header/footer include system. That
means you add the 'top' of your layout - things like divs, header images
etc. to the header.php file and the bottom of your layout - closing notices
and copyright signs - to the footer.php file.

For more information on PHP includes (for layout purposes) see this tutorial:
http://girlswhogeek.com/tutorials/2006/php-includes


__________________________ HOW DO I UPDATE MY FANLISTING?

Open your admin panel - this will be located in your online BellaBuffs folder
as admin.php. E.g. http://your-domain.com/bellabuffs/admin.php

Login and choose "Add Update". The date will automatically be inserted for you
into the form with the timestamp format set in config.php

Add your update. If you have no details, leave the details field blank and only
the date will be updated. Each update will replace the previous.


__________________________ HOW DO I ADD A BUTTON/CODE?

Open your admin panel - this will be located in your online BellaBuffs folder
as admin.php. E.g. http://your-domain.com/bellabuffs/admin.php

Login and choose "Add Button". Find the button/code on your computer. 

If the button was donated, fill in the fields. If not, leave them blank. Buttons
will automatically be added and sorted on the buttons.php page.


__________________________ HOW DO I DISPLAY THE MEMBER/BUTTON/ETC COUNT?

This is done using the custom countfile() function. Simply add the
name of the file, as defined in config.php, between the brackets. 

For example, to count the members, put: <?php countfile(MEMBERS); ?>
..to count the newbies: <?php countfile(NEWBIES); ?>
..to count the buttons: <?php countfile(BUTTONS); ?> .. etc.


__________________________ WHAT'S A CAPTCHA?

A captcha is generally an image that is automatically generated with a mixture of
letters and numbers on it that a person must fill out exactly right before they
are able to submit information through a form - in the case of the BellaBuffs
captcha, before they are allowed to join.

Because captchas are image based, they have accessibility implications. Please
consider those who browse with images turned off and/or those with eyesight
related disabilities before turning on the captcha - it is only needed if you suffer
from large amounts of bot-based spam.

You can read more about captchas at wikipedia: http://en.wikipedia.org/wiki/Captcha


__________________________ I AM LISTED AT THEFANLISTINGS.ORG, CAN I USE THE CAPTCHA?

I contacted the Senior Staff of TheFanlistings.Org because I wasn't sure - I was
told that the usage of captchas is allowed providing an alternative method is 
displayed so that users who cannot for some reason display/use the captcha can 
join too. This means that you MUST link to an e-mail address or other method of 
contact as an alternative if you use the captcha.


__________________________ CAN I MANAGE MULTIPLE FANLISTINGS THROUGH BELLABUFFS?

You will need to install a separate version of BellaBuffs for each fanlisting you
wish to have on your site. Also, there is currently no collective feature for
BellaBuffs so each fanlisting has to be managed through it's own admin panel. 



//--------------------
// FEATURES
//--------------------
* Spam word and IP blocking
* Valid country checking to prevent text injection
* E-mail scrambling and JavaScript protection
* Optional favourites field and spam-preventing captcha
* 'Paginate' members in admin panel too
* Sort members by country/join date
* Button ('code') upload & management
* Affiliate management (inc. button upload)
* Valid XHTML Transitional by default
* Auto-update date when approving member
* Optional update 'details' (single-entry) log

//--------------------
// FIXES IN VERS 2
//--------------------
2.2 - re-roll fixes to update.php (matching join.php)
2.2 - fix typos in contact.php / join.php
2.1 - fix stray curly braces in join.php
2.0 - Update to countries list, as provided by Haley
2.0 - Updated contact form based on latest vers of Jem's PHP Mail Form (jemsmailform.com)
2.0 - New functions in functions.php for contact.php
2.0 - Updated join form to fix deprecated ereg errors + update spammy checks
2.0 - $maxpoints added to prefs.php


//--------------------
// FIXES IN VERS 1b-1f
//--------------------
In admin.php on line 216, !ctype_digit($line) replaced with !ctype_digit($_GET['mem'])
In join.php on line 62, checkTXTfile(SPAMWDS, $clean['name'] replaced with (checkTXTfile(SPAMWDS, $clean['name']
In join.php on line 62, checkTXTfile(SPAMWDS, $clean['fave'], "spamword") === true) { replaced with checkTXTfile(SPAMWDS, $clean['fave'], "spamword") === true)) {
More rigorous testing of file names added to prevent broken images if a comma is in button/affiliate file name.
Added check to see if member exists before sending update info form
Search functionality added to admin panel
Added meta injection checks (this should have been added before the script was released!)

//--------------------
// FIXES IN VERS 1g-1k
//--------------------
Fixed XHTML validity issue in admin.php (Thank you Shawna: http://www.eruantale.net)
Added extra checks to join.php & update.php, fixed email check in update.php
Fixed issue with uppercase emails being used in admin search

//--------------------
// FIXES IN VERS 1h: Suggestions and bug reports courtesy of Tea P. (http://colorfilter.net)
//--------------------
Fixed XHTML validity issue in form textareas in: join.php, update.php and contact.php
Changed value of submit button in contact.php (copy&paste error!)
Included footer.php in join.php error messages
Added fixEmail() to "Reply-To: " in e-mail admin section of join.php
Added "Please select a country:" option to join.php and update.php
Added dynamic link to approved/pending members edit section after editing member
Added count of members for each country in members.php
Changed broken $email to $admin_email in "Reply-To: " in approval section of admin.php
More sanitisation of dispemail in join.php to prevent empty lines/commas being submitted
Further improvements to cleanUp() in config.php to strip stray new lines not caught by trim()

//--------------------
// FIXES IN VERS 1l-1p
//--------------------
Fixed typos/wording errors in join.php, contact.php and update.php (Thanks Julie: http://jul13.ju.funpic.org)
checkTXTfile() function altered (config.php) to use in_array instead of preg_match
Footer link changed to match new URL (jemjabella.co.uk/scripts)
Closed file after blanklinefix() (config.php) as a safety precaution
Fixed data sanitisation bug in join.php caused by fix in 1k
Altered lastupdate() function making details optional (config.php)
Fixed dynamic "approve more members"/"no members to be approved" link (admin.php)
Fixed IP issue (blocked IPs caused country error) in config.php & join.php (Thanks Michele: http://www.absolutetrouble.com)
Changed captcha image to make it harder for bots to separate colours

//--------------------
// FIXES IN VERS 1q-1t
//--------------------
Fixed pagination bug created by optimisation in version 1m
Modified join.php to display inline errors; additional spam protection
Implemented two potential fixes for those losing members due to script time-out (config.php)
More tidying of admin.php to reduce superfluous code
Separated functions and preferences to allow for easier upgrading (config.php renamed to prefs.php)
Added "edit affiliate" and "edit button" button replacement functionality
Improved security to reduce CSRF risk (admin.php)
Implemented checkbox for mass approve & delete (admin.php)
Alternating row colours to visually distinguish members (admin.php)
Added default sort option, oldest or newest first (prefs.php, admin.php)
Populated spam words list with common spam and profanity (spamwds.txt)
Further user agent checking to defeat bots (join.php)
Fixed blank fave error caused by changes in 1r (join.php)
Added new line to admin.php when sorting members by oldest first


//--------------------
// CREDITS
//--------------------
Mucho thanks go to the following people for helping with BellaBuffs:

Amelie	- http://not-noticeably.net
Katy	- http://cathode-ray-coma.co.uk

Amelie and Katy were there for my constant swearing, frustrated
coding-related ramblings, bug testings, suggestions, snippy "I know best"
responses and major dense moments. Without them, this script would
not exist, and my partner Karl would get a lot more earache.

The following others also helped with last minute beta/bug testing:

Julie	- http://jul13.ju.funpic.org
Frosty	- http://telperionworld.com
Jenny	- http://www.prism-perfect.net
Ang 	- http://www.silencia.net
Ilona	- http://www.puwing.com
Tea P.	- http://colorfilter.net
Michele - http://www.absolutetrouble.com