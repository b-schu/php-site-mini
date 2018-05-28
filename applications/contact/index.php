<?php
$APP = "Contact";

$m->print_header();

?>
<div id="body-container">
<?

$action = $_REQUEST["action"];
if ($action == "") {
	$action = "contact";
}

if ($action == "send") {
	$re = $_POST['re'];
	$content = $_POST['content'];
	$from = $_POST['from'];
	$content .= "\n\nFrom: $from\n\n";
	$key = $_SESSION["key"];
	$captcha = md5($_POST["captcha"]);
	if ($re == "" || $content == "" || $from == "") {
		$m->message("You must fill in all fields.  Please try again.");
		$action = "contact";
	} else if ($key != $captcha) {
		$m->message("You didn't enter the captcha letters/numbers correctly.  Please try again.");
		$action = "contact";
	} else if(mail ( "some@email.com", "website message: ".$re, $content, "From: info@email.com" ) ) {
		print "<div style='font-size:2em;background:#FFF;'>\n";
		print "<center><span style=\"font-size:25px;\">Thank You!</span><br><br>\n";
		print "Mail sent successfully...<br><br>";
		print "</div>\n";
	} else {
		$m->message("Sorry, there was an error!<br><br>Please try again.");
		$action = "contact";
	}
}

if($action == "contact"){
	?>
	<h2>Contact Us</h2>
	<div class="contact-form">
	<form method="POST" name="formy" action="<? print $m->app_link($APP); ?>">
	<input type="hidden" name="action" value="send">
	Your e-mail address:<br>
	<input name="from" type="text" size="20" value="<? print $_REQUEST["from"]; ?>"><br>
	Subject:<br>
	<input name="re" type="text" size="20" value="<? print $_REQUEST["re"]; ?>"><br>
	Message:<br>
	<textarea name="content"><? print $_REQUEST["content"]; ?></textarea><br>
	<br>

	What numbers do you see here?<br>
	<img src="<? print $ROOT; ?>applications/captcha/image_key.php" style="width:5em;margin:4px;"><br>
	<input type="text"  name="captcha" style="width:5em; background-color:white;border:1px solid black;"><br>
	<br>
	<input type="submit" value="Send" style="border:1px solid black;" />
	</form>
	</div>

	<br><br><br><br>
	<?
}

?>
</div>
<?

$m->print_footer();
?>
