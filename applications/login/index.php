<?

$action = $_REQUEST["action"];

if ($action == "logout") {
	$m->logout();
	header("Location: $ROOT");
	exit;
}

if ($action == "login") {
	$m->login();
	header("Location: $ROOT");
	exit;
}

$m->print_header();

if ($action == "verifyaccount") {
	$id = $_REQUEST["id"];
	$user = $m->dbirow("SELECT * FROM users WHERE id=?;","i",array($id));
	if ($user["name"] != "") {
		$m->verify_user($user["name"]);
		$m->message("Your account has been verified. Thank you!");
		$m->message("Please continue to the <a href='index.php'>Home Page</a>.");
		$_SESSION["username"] = $user["name"];
	} else {
		$m->message("Sorry, there was an error with verification. Please <a href='index.php?app=contact'>contact us</a> for help.");
	}
}

if ($action == "register") {
	$username = trim($_REQUEST["username"]);
	$password = $_REQUEST["password"];
	$password_confirm = $_REQUEST["password_confirm"];
	$email = $_REQUEST["email"];

	$success = true;
	$row = $m->dbirow("SELECT * FROM users WHERE name=?","s",array($username));
	if ($row["id"] != "") {
		$success = false;
		$m->message("That login name already exists. Please try again.");
	} else if ($password != $password_confirm) {
		$success = false;
		$m->message("Your password and password confirmation did not match. Please try again.");
	} else if (strlen($password) <= 4) {
		$success = false;
		$m->message("Your password must be longer than 4 characters. Please try again.");
	} else if (trim($email) == "") {
		$success = false;
		$m->message("You must fill in your correct email address. Please try again.");
	} else if (!$m->valid_username($username)) {
		$success = false;
		$m->message("Your login name can only contain letters, numbers, and underscore character. Please try again.");
	} else if (strlen($password) < 2 || strlen($password) > 20) {
		$success = false;
		$m->message("Your password must be between 2 and 20 characters. Please try again.");
	}
	if ($success != false) {
		$m->insert_new_user($username,$password,$email);
		// put email verification here
	} else {
		// registration failed
	}
}

if ($action == "subnewpass") {
	// THIS NEEDS TO BE RECODED
}

if ($action == "resetpassword") {
	// THIS NEEDS TO BE RECODED
}

if ($action == "forgotpword_request") {
	// THIS NEEDS TO BE RECODED
}

if ($action == "forgotpassword") {
	// NEEDS RECODING
	?>
	<div id="forgot-password">
	Enter your E-mail address and we will send you a link to reset your password.
	<p>
		<form action="index.php?app=login" method="POST">
		<input type="hidden" name="action" value="forgotpword_request" />
		<input type="text" name="email" id="forgot-email" />
		<input type="submit" value="Send" />
		</form>
	</p>
	<hr />
	<a href="index.php?app=login">CANCEL</a>
	</div>
	<?
}

if ($action == "") {
	?>
	<div id="body-container">
	<h2>LOGIN</h2>
	<form action="index.php?app=login&action=login" method="POST">
	<p>
	<input type="text" name="username" placeholder="Name" />
	</p>
	<p>
	<input type="password" name="password" placeholder="Password" />
	</p>
	<p>
	<input type="submit" value="Login" />
	</p>
	</form>
	</div>
	<?
}

$m->print_footer();
?>
