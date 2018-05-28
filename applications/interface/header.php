<!DOCTYPE html>
<html>
<head>
<title>New Sopi Site</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="<? print $description; ?>" />
<meta name="keywords" content="<? print $site["meta_keywords"]; ?>" />
<meta name="author" content="<? print $site["meta_author"]; ?>" />
<link rel="stylesheet" type="text/css" href="<? print $ROOT; ?>applications/interface/style.css?t=<? print rand(); ?>" />
<link rel="icon" type="image/png" href="<? print $ROOT; ?>data/images/favicon.png" />
<script language="javascript" type="text/javascript" src="<? print $ROOT; ?>applications/interface/scripts/jquery.js"></script>
<!-- Google Analytics -->
<?
if ($site["analytics_on"] == "yes") {
	print $site["analytics"]."\n\n";
}
print $headinfo;
?>
</head>
<body>
<div class="header-area">
	<div id="user-menu"></div>
	<div id="header-logo">
	<a href="<? print $ROOT; ?>">LOGO</a>
	</div>
	<div style="clear:both;"></div>
</div>
<?
if ($_SESSION["login_failed"] == "yes") {
	$this->message("Login failed, please try again.");
	$_SESSION["login_failed"] = "";
}
?>

<? //-- end header area -- ?>

