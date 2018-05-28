<?php
ini_set('session.gc_maxlifetime', '31536000');
session_start();

include_once("config.php");

class images {

	public static $imgpath = "";
	public static $image;

	public function set_img_path($path) {
		self::$imgpath = $path;
	}

	public function set($path) {
		self::$imgpath = $path;
		self::make_img();
	}
	public function make_img() {
		$parts = explode(".",self::$imgpath);
		$ext = $parts[count($parts)-1];
		if ($ext == "png" || $ext == "PNG") {
			self::$image = imagecreatefrompng(self::$imgpath);
		}
		if ($ext == "jpg" || $ext == "JPG" || $ext == "jpeg" || $ext == "JPEG") {
			self::$image = imagecreatefromjpeg(self::$imgpath);
		}
		if ($ext == "gif" || $ext == "GIF") {
			self::$image = imagecreatefromgif (self::$imgpath);
		}
	}

	public function draw_text($text) {
		$im = self::$image;
		$black = imagecolorallocate($im, 0x00, 0x00, 0x00);
		$red = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
		$w = imagesx($im);
		$h = imagesy($im);
		imagefilledrectangle($im, 0, $h - 30, $w, $h, $black);
		$font_path = realpath(".")."/arial.ttf";

		$fontsize = 8;
		$font_width = ImageFontWidth($font);
		$font_height = ImageFontHeight($font);

		// add +2 to font_width because we're using all Capitals
		$text_width = ($font_width+2) * strlen($text);
		$position_center = ceil(($w - $text_width) / 2);

		imagefttext($im, $fontsize, 0, $position_center, $h - 10, $red, $font_path, $text);
		self::$image = $im;
	}

	public function get_width() {
		return imagesx(self::$image);
	}

	public function get_height() {
		return imagesy(self::$image);
	}

	public function resize($max_w, $max_h) {
		$orig_w = imagesx(self::$image);
		$orig_h = imagesy(self::$image);
		if ($max_w != 0 && $max_h != 0) {
			if ($orig_w > $orig_h) {
				$new_w = $max_w;
				$new_h = $max_w / $orig_w * $orig_h;
			}
			if ($orig_h > $orig_w) {
				$new_h = $max_h;
				$new_w = $max_h / $orig_h * $orig_w;
			}
			if ($orig_h == $orig_w) {
				$new_h = $max_h;
				$new_w = $max_h;
			}
		} else {
			if ($max_w == 0) {
				$new_h = $max_h;
				$new_w = $max_h / $orig_h * $orig_w;
			}
			if ($max_h == 0) {
				$new_w = $max_w;
				$new_h = $max_w / $orig_w * $orig_h;
			}
		}
        	$newimage = imagecreatetruecolor($new_w, $new_h);
        	imagecopyresampled($newimage, self::$image, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
		self::$image = $newimage;
	}

	public function crop($width, $height) {
		$canvas = imagecreatetruecolor($width,$height);
		// Prepare image resizing and crop -- Center crop location
		$orig_w = imagesx(self::$image);
		$orig_h = imagesy(self::$image);
		$crop_x = ($orig_w/2) - ($width/2);
		$crop_y = ($orig_h/2) - ($height/2);
		// Generate the cropped image
		imagecopyresized($canvas, self::$image, 0, 		0, 
							$crop_x, 	$crop_y, 
							$width,		$height, 
							$width, 	$height);
		self::$image = $canvas;
		// Write image or fail
		//imagedestroy($canvas);
	}

	public function display() {
		header('Content-Type: image/png');
		imagepng(self::$image);
	}

	public function save_img($path) {
		$parts = explode(".",$path);
		$ext = $parts[count($parts)-1];
		if ($ext == "png" || $ext == "PNG") {
			imagepng(self::$image, $path)or die("error saving");
		}
		if ($ext == "jpg" || $ext == "JPG" || $ext == "jpeg" || $ext == "JPEG") {
			imagejpeg(self::$image, $path)or die("error saving");
		}
		if ($ext == "gif" || $ext == "GIF") {
			imagegif (self::$image, $path)or die("error saving");
		}
	}

	public function destroy() {
		imagedestroy(self::$image);
	}
};

class mini {

	private $dbhost;
	private $dbname;
	private $dbuser;
	private $dbpass;
	private $dbtable;
	private $dbi;
	private $salt;
	
	function __construct() {
		global $dbhost;
		global $dbname;
		global $dbuser;
		global $dbpass;
		global $dbtable;
		global $dbsalt;

		$this->dbhost = $dbhost;
		$this->dbname = $dbname;
		$this->dbuser = $dbuser;
		$this->dbpass = $dbpass;
		$this->dbtable = $dbtable;
		$this->dbsalt = $dbsalt;
	}

	public function start() {
		$this->dbi = $this->dbiopen();
	}

	public function dbiopen() {
		$dbi = new mysqli($this->dbhost,$this->dbuser,$this->dbpass,$this->dbname);
		if ($this->dbi->connect_error) {
			print "Error connecting to database";
			return false;
		}
		return $dbi;
	}

	public function get_dbi_result($Statement) {
		$RESULT = array();
		$Statement->store_result();
		for ( $i = 0; $i < $Statement->num_rows; $i++ ) {
			$Metadata = $Statement->result_metadata();
			$PARAMS = array();
			while ( $Field = $Metadata->fetch_field() ) {
				$PARAMS[] = &$RESULT[ $i ][ $Field->name ];
			}
			call_user_func_array( array( $Statement, 'bind_result' ), $PARAMS );
			$Statement->fetch();
		}
		return $RESULT;
	}

	public function dbiupdate($query,$types,$vars) {
		if ($types != "") {
			$s = $this->dbi->prepare($query);
			if ($s === false) {
				$this->message("SQL Error 1: ".$this->dbi->error);
				return false;
			}
			$a_params = array();
			$a_params[] = & $types;
			for ($i=0; $i<count($vars); $i++) {
				$a_params[] = & $vars[$i];
			}
			call_user_func_array(array($s,'bind_param'), $a_params);
			if ($s->execute()) {
				return true;
			} else {
				return false;
			}
			//$id = $s->insert_id;
		}
		if (!$r = $this->dbi->query($query)) {
			$this->message("SQL Error 2: ".$this->dbi->error);
			//return $r->insert_id;
			return false;
		} else {
			return true;
		}
	}

	public function dbirow($query,$types,$vars) {
		if ($types != "") {
			$s = $this->dbi->prepare($query);
			if ($s === false) {
				print "SQL Error: ". $this->dbi->error;
				return false;
			}
			$a_params = array();
			$a_params[] = & $types;
			for ($i=0; $i<count($vars); $i++) {
				$a_params[] = & $vars[$i];
			}
			call_user_func_array(array($s,'bind_param'), $a_params);
			$s->execute();
			$r = $this->get_dbi_result($s);
			return $r[0];
		}
		if (!$r = $this->dbi->query($query)) {
			$this->message("SQL Error: ".$this->dbi->error);
			return false;
		}
		return $r->fetch_assoc();
	}

	public function dbiarray ($query,$types,$vars) {
		if ($types != "") {
			$s = $this->dbi->prepare($query);
			if ($s === false) {
				print "SQL Error:".$this->dbi->error;
				return false;
			}
			$a_params = array();
			$a_params[] = & $types;
			for ($i=0; $i<count($vars); $i++) {
				$a_params[] = & $vars[$i];
			}
			call_user_func_array(array($s,'bind_param'), $a_params);
			$s->execute();
			return $this->get_dbi_result($s);
		}
		if (!$r = $this->dbi->query($query)) {
			$this->message("SQL Error: ".$this->dbi->error);
			return false;
		}
		$result = array();
		while ($row = $r->fetch_assoc()) {
			$result[] = $row;
		}
		return $result;
	}

	public function get_table_headers($table) {
		$retval = array();
		$rows = $this->dbiarray("SHOW COLUMNS FROM ".$table.";");
		foreach ($rows as $row) {
			$retval[] = $row["Field"];
		}
		return $retval;
	}

	public function get_headers_full($table) {
		$retval = array();
		$rows = $this->dbiarray("SHOW COLUMNS FROM ".$table.";");
		foreach ($rows as $row) {
			$retval[] = $row;
		}
		return $retval;
	}

	public function count_rows($table) {
		$row = $this->dbirow("SELECT count(*) FROM $table;");
		return $row["count(*)"];
	}
		
	public function add_columns($table,$col_array) {
		foreach($col_array as $key => $val) {
			$query = "ALTER TABLE ".$table." ADD COLUMN $key $val;";
			$this->dbiupdate($query);
		}
	}

	public function drop_columns($headers) {
		foreach($headers as $header) {
			$query = "ALTER TABLE ".$this->table." DROP COLUMN $header;";
			$this->dbiupdate($query);
		}
	}

	public function change_column_type($table, $column, $type) {
		$query = "ALTER TABLE ".$table." CHANGE $column $column $type;";
		$this->dbiupdate($query);
	}

	public function create_table($tablename,$col_array=array()) {
		if (!$this->table_exists($tablename)) {
			$query = "CREATE TABLE $tablename (\n";
			$query .= "`id` INT NOT NULL auto_increment,\n";
			foreach($col_array as $key => $val) {
				$query .= "`$key` ".$val." DEFAULT NULL,\n";
			}
			$query .= "PRIMARY KEY (`id`));\n";
			if ($this->dbiupdate($query) != false) {
				return true;
			} else {
				$this->message("SQL Error 3: ".$this->dbi->error);
				return false;
			}
			return false;
		} else {
			print "Error: Table of name '$tablename' already exists";
			return false;
		}
	}

	public function table_exists($table) {
		$names = $this->get_table_names();
		if (in_array($table,$names)) {
			return true;
		}
		return false;
	}

	public function get_table_names() {
		$retval = array();
		$rows = $this->dbiarray("SHOW TABLES;");
		foreach ($rows as $row) {
			$retval[] = $row["Tables_in_".$this->dbname];
		}
		return $retval;
	}

	// ------------------------------------
	// End Database Functions
	// ------------------------------------


	// ------------------------------------
	// User Functions
	// ------------------------------------

	public function user_exists($username) {
		$u = $this->dbirow("SELECT * FROM users WHERE name=?","s",array($username));
		if ($u["id"] != "") {
			return true;
		}
		return false;
	}

	public function insert_new_user($username,$password,$type,$email="") {
		if (!$this->user_exists($username)) {
			$password = crypt($password,$this->dbsalt);
			$this->dbiupdate("INSERT INTO users (name,password,display_name,email,status,joined,user_type) VALUES (?,?,?,?,?,?,?);","sssssis",array($username,$password,$username,$email,"registered",time(),$type));
		}
	}

	public function check_password($password,$user) {
		if (crypt($password,$this->dbsalt) == $this->get_password($user)) {
			return true;
		}
		return false;
	}

	public function verify_user($username) {
		$this->dbiupdate("UPDATE users SET status='member' WHERE name=?","s",array($username));
	}

	public function get_user_by_name($username) {
		return $this->dbirow("SELECT * FROM users WHERE name=?;","s",array($username));
	}

	public function reset_password($name,$password) {
		$password = crypt($password,$this->dbsalt);
		$this->dbiupdate("UPDATE users SET password=? WHERE name=?;","ss",array($password,$name));
	}

	public function get_user_privileges($name) {
		$row = $this->dbirow("SELECT * FROM users WHERE name=?;","s",array($name));
		return $row;
	}

	public function delete_user($name) {
		$this->dbiupdate("DELETE FROM users WHERE name=?;","s",array($name));
	}

	public function is_admin($user) {
		if ($user == "") {
			$user = $_SESSION["username"];
		}
		$type = $this->get_user_type($user);
		if ($type == "admin" || $type == "superuser") {
			return true;
		}
		return false;
	}

	public function password_update($username,$newpassword) {
		$this->dbiupdate("UPDATE users SET password=? WHERE name=?;","ss",array($newpassword,$username));
	}

	public function get_password($name) {
		$row = $this->dbirow("SELECT * FROM users WHERE name=?","s",array($name));
		return $row["password"];
	}

	public function get_user_type($name) {
		$row = $this->dbirow("SELECT * FROM users WHERE name=?;","s",array($name));
		return $row['user_type'];
	}

	public function logged_in() {
		if ($_SESSION["username"] == "" || $_SESSION["username"] == "guest") {
			return false;
		}
		return true;
	}

	public function get_user($id) {
		$row = $this->dbirow("SELECT * FROM users WHERE id=?;","i",array($id));
		return $row;
	}

	public function register_login($name) {
		$u = $this->dbirow("SELECT * FROM users WHERE name=?","s",array($name));
		$logins = $u["logins"] + 1;
		$this->dbiupdate("UPDATE users SET logins='$logins' WHERE name=?;","s",array($name));
	}

	public function get_all_types() {
		$rows = $this->dbiarray("SELECT * FROM user_types ORDER by type_name;");
		$retval = array();
		foreach ($rows as $row) {
			if (!in_array($row["type_name"],$retval)) {
				$retval[] = $row["type_name"];
			}
		}
		return $retval;
	}

	public function get_all_users2() {
		$rows = $this->dbiarray("SELECT * FROM users ORDER BY id;");
		return $rows;
	}

	public function get_all_users() {
		$rows = $this->dbiarray("SELECT * FROM users ORDER BY name;");
		$retval = array();
		foreach ($rows as $row) {
			if (!in_array($row["name"],$retval)) {
				$retval[] = $row["name"];
			}
		}
		return $retval;
	}

	public function get_all_usernames() {
		$rows = $this->dbiarray("SELECT * FROM users;");
		$retval = array();
		foreach ($rows as $row) {
			$retval[] = $row["name"];
		}
		return $retval;
	}

	public function valid_username($name) {
		if (preg_match('/^[a-z\d_]{2,20}$/i', $name)) {
			return true;
		} else {
			return false;
		}
	}

	// ------------------------------------
	// End User Functions
	// ------------------------------------

	// ------------------------------------
	// User Type Functions
	// ------------------------------------

	public function change_user_type($name,$newtype) {
		$this->dbiupdate("UPDATE users SET user_type=? WHERE name=?;","ss",array($newtype,$name));
	}

	public function insert_user_type($typename) {
		$this->dbiupdate("INSERT INTO user_types (row_type, type_name) VALUES('user_type',?);","s",array($typename));
	}

	public function type_exists($typename) {
		$row = $this->dbirow("SELECT * from user_types WHERE type_name=? AND row_type='user_type';","s",array($typename));
		if ($row["id"] != "") {
			return true;
		}
		return false;
	}

	public function get_user_types() {
		$rows = $this->dbiarray("SELECT * from user_types WHERE row_type='user_type' ORDER BY type_name;");
		$retval = array();
		foreach ($rows as $row) {
			$retval[] = $row["type_name"];
		}
		return $retval;
	}

	public function check_auth($username,$app_name) {
		if ($username == "") {
			$username = $_SESSION["username"];
		}
		if ($app_name == "") {
			global $APP;
			$app_name = $APP;
		}
		return $this->check_authorization($username,$app_name);
	}

	public function check_authorization($username,$app_name) {
		$apps = $this->get_user_apps($username);
		if (count($apps) == 0) {
			return false;
		}
		if (in_array($app_name,$apps)) {
			return true;
		}
		return false;
	}

	// ------------------------------------
	//  End User Type Functions
	// ------------------------------------

	// ------------------------------------
	// Session Functions
	// ------------------------------------

	public function logout() {
		global $ROOT;
		session_destroy();
		header("Location: $ROOT");
		exit;
	}

	public function login() {
		global $ROOT;
		if (!isset($_SESSION["username"])) {
			session_regenerate_id();
			if (isset($_POST['username']) && isset($_POST['password'])) {
				$user = $_POST['username'];
				$password = $_POST["password"];
				$successful_login = false;

				//-----------------------------------
				// MySql md5 password check 
				// ----------------------------------
				if ($this->check_password($password,$user)){
					$successful_login = true;
				} else {
					$successful_login = false;
					// here you can put $login_attempts++ in SESSION
					// and block after threshold if required.
				}

				if ($successful_login) {
					setcookie("mini_user", crypt($user,$this->dbsalt), time()+60*60*24*365, "/","mini_user");
					$_SESSION["username"] = $user;
					$this->register_login($user);
					header("Location: $ROOT");
					exit;
				} else {
					// An error with username or password || possible hack attempt
					$_SESSION["login_failed"] = "yes";
					header("Location: $ROOT");
					exit;
				}
			} else {
				// username and/or password not set
				session_destroy();
				header("Location: $ROOT");
				exit;
			}
		} else {
			// session already open and running
			header("Location: $ROOT");
		}
	}

	// ------------------------------------
	// End Session Functions
	// ------------------------------------

	// ------------------------------------
	// Mail Functions
	// ------------------------------------

	public function send_mail($addresses, $subject, $message, $emb_attchs, $attachments) {
		global $ROOT;
		global $ROOTPATH;
		require_once($ROOTPATH."shell/PHPMailer/class.phpmailer.php");
		require_once($ROOTPATH."shell/PHPMailer/class.smtp.php");

		$mail = new PHPMailer(true);
		$mail->IsHTML(true);
		$mail->Host = "smtp.website.com";
		$mail->Port = 25;
		$mail->SetFrom("info@website.com","Website Name");
		$mail->AddReplyTo("info@website.com","Website Name");
		$mail->Subject = $subject . " - " . time();
		$mail->AddAddress($addresses[0],"Website Recipient");
		for ($i=1; $i<count($addresses); $i++) {
			$mail->AddBCC($addresses[$i], "Website Recipient");
		}
		foreach ($emb_attchs as $attch) {
			$mail->AddEmbeddedImage($attch["file"],$attch["name"],$attch["file"]);
		}
		$mail->Body = $message;
		foreach ($attachments as $attch) {
			$mail->AddAttachment($attch);
		}
		if (!$mail->Send()) {
			return false;
		}
		return true;
	}

	public function get_mail_headers($from,$type="none") {
		$frommail = "info@website.com";
		$name = "website.com";
		$mailheaders = "Reply-To: $name <$from>\r\n";
		$mailheaders .= "Return-Path: $name <$from>\r\n";
		$mailheaders .= "From: $name <$from>\r\n";
		$mailheaders .= "Organization: $name\r\n";
		$mailheaders .= "X-Mailer: PHP/".phpversion()."\r\n";
		$mailheaders .= "MIME-Version: 1.0\r\n";
		if ($type == "html") {
			$mailheaders .= "Content-Type: text/html; charset=UTF-8\r\n";
		} else {
			$mailheaders .= "Content-Type: text/plain\r\n";
		}
		return $mailheaders;
	}

	// ------------------------------------
	// End Mail Functions
	// ------------------------------------

	// ------------------------------------
	// File Functions
	// ------------------------------------

	public function upload($handle, $path) {
		if ($path[strlen($path)-1] != "/") {
			$path .= "/";
		}
		$orig_filename = $_FILES[$handle]["name"];
		$parts = explode(".",$orig_filename);
		$filename = str_replace(" ","_",$parts[0])."_".time();
		$ext = end($parts);
		$path = $path.$filename.".".$ext;
		if ($_FILES[$handle]["error"] > 0) {
			$this->message("Error:" . $_FILES[$handle]["error"].".");
			return false;
		} else {
			if (empty($_FILES[$handle]["name"])) {
				$this->message("Error with file name, click back and try again.");
				return false;
			} else if (empty($_FILES[$handle]["type"])) {
				$this->message("Error with file type, click back and try again.");
				exit;
				return false;
			} else if (empty($_FILES[$handle]["size"])) {
				$this->message("Error with file size, click back and try again.");
				return false;
			} else {
				if (file_exists($path)) {
					$this->message("Error:  That file name already exists.");
					return false;
				} else if (move_uploaded_file($_FILES[$handle]["tmp_name"],$path)) {
					$this->message("<strong>Successful Upload:</strong>".$_FILES[$handle]["name"]);
					return $path;
				} else {
					$this->message("Error:  Couldn't move uploaded file.");
					return false;
				}
			}
		}
	}

	public function resize_image($path,$new_w,$new_h) {
		$i = new images();
		$i->set_img_path($path);
		$i->make_img();
		$i->resize($new_w,$new_h);
		$i->save_img($path);
		$i->destroy();
	}

	public function is_img($name) {
		$ext = strtolower(end(explode(".",$name)));
		$exts = array("jpg","png","jpeg","gif");
		if (in_array($ext,$exts)) {
			return true;
		}
		return false;
	}

	// ------------------------------------
	// End File Functions
	// ------------------------------------

	// ------------------------------------
	// Application Functions
	// ------------------------------------

	public function delete_application($name) {
		$this->dbiupdate("DELETE FROM applications WHERE name=?;","s",array($name));
		$this->dbiupdate("DELETE FROM user_types WHERE application=?;","s",array($name));
	}

	public function get_app($app_name) {
		$row = $this->dbirow("SELECT * FROM applications WHERE name=?;","s",array($app_name));
		return $row;
	}

	public function update_application($name,$app_path,$description) {
		$this->dbiupdate("UPDATE applications SET app_path=?, description=? WHERE name=?;","sss",array($app_path,$description,$name));
	}

	public function insert_application($name,$app_path,$description) {
		$row = $this->dbirow("SELECT * FROM applications WHERE name=?;","s",array($name));
		if ($row["id"] != "") {
			return false;
		} else {
			$this->dbiupdate("INSERT INTO applications (name,app_path,description) VALUES (?,?,?);","sss",array($name,$app_path,$description));
		}
		return true;
	}

	public function get_application_names() {
		$retval = array();
		$rows = $this->dbiarray("SELECT * FROM applications;");
		foreach ($rows as $row) {
			$retval[] = $row["name"];
		}
		return $retval;
	}

	public function insert_type_app($type_name,$app_name) {
		$this->dbiupdate("INSERT INTO user_types (row_type, type_name, application) VALUES ('application',?,?);","ss",array($type_name,$app_name));
	}

	public function get_apps() {
		$retval = $this->dbiarray("SELECT * FROM applications;");
		return $retval;
	}

	public function get_app_folder($app_name) {
		$row = $this->dbirow("SELECT * FROM applications WHERE name=?;","s",array($app_name));
		return $row["app_path"];
	}

	public function app_link($app_name) {
		if ($app_name == "") {
			global $APP;
			$app_name = $APP;
		}
		global $ROOT;
		$folder = $this->get_app_folder($app_name);
		return $ROOT."index.php?app=$folder";
	}

	public function get_app_path($product_name) {
		global $dbtable;
		global $ROOT;
		$row = $this->dbirow("SELECT * FROM applications WHERE name=?;","s",array($product_name));
		return $ROOT."applications/".$row["prod_path"];
	}

	public function get_all_apps() {
		$retval = $this->dbiarray("SELECT * FROM applications;");
		return $retval;
	}

	public function get_applications() {
		$retval = $this->dbiarray("SELECT * FROM applications;");
		return $retval;
	}

	public function get_type_apps($type) {
		$rows = $this->dbiarray("SELECT * FROM user_types WHERE row_type='application' AND type_name=? ORDER BY application;","s",array($type));
		$retval = array();
		foreach ($rows as $row) {
			$retval[] = $row["application"];
		}
		return $retval;
	}

	public function get_user_apps($name) {
		$type = $this->get_user_type($name);
		return $this->get_type_apps($type);
	}

	// ------------------------------------
	// END Application Functions
	// ------------------------------------

	// ------------------------------------
	// System Functions
	// ------------------------------------

	public function install() {
		if (!$this->table_exists("applications")) {
			print "Preparing Installation <b>Applications</b>...<br />\n";
			$rows = array();
			$rows["name"] = "TINYTEXT";
			$rows["app_path"] = "TINYTEXT";
			$rows["description"] = "MEDIUMTEXT";
			$rows["type"] = "VARCHAR(20)";
			if ($this->create_table("applications",$rows)) {
				print "<b>Applications</b> table successfully installed.<br /><hr />\n";
				$this->insert_application("User Manager","user_manager","A tool for managing website users.");
				$this->insert_application("Application Manager","application_manager","A tool for managing system installed applications.");
				$this->insert_application("Site Manager","site_manager","A tool for managing common website features.");
			} else {
				print "Table Creation FAILED<br /><hr />\n";
			}
		}

		if (!$this->table_exists("users")) {
			print "Preparing Installation <b>Users</b>...<br />\n";
			$rows = array();
			$rows["name"] = "VARCHAR(100)";
			$rows["password"] = "TINYTEXT";
			$rows["user_type"] = "VARCHAR(100)";
			$rows["status"] = "VARCHAR(100)";
			$rows["email"] = "TINYTEXT";
			$rows["display_name"] = "VARCHAR(100)";
			$rows["logins"] = "MEDIUMINT";
			$rows["logouts"] = "MEDIUMINT";
			$rows["joined"] = "DATETIME";
			if ($this->create_table("users",$rows)) {
				print "<b>User</b> table successfully installed.<br /><hr />\n";
				$this->insert_new_user("admin","password","superuser");
				$this->verify_user("admin");
			} else {
				print "Table Creation FAILED<br /><hr />\n";
			}
		}

		if (!$this->table_exists("user_types")) {
			print "Preparing Installation <b>User Types</b>...<br />\n";
			$rows = array();
			$rows["row_type"] = "VARCHAR(100)";
			$rows["type_name"] = "VARCHAR(100)";
			$rows["application"] = "VARCHAR(100)";
			if ($this->create_table("user_types",$rows)) {
				print "<b>User Types</b> table successfully installed.<br /><hr />\n";
				$this->insert_user_type("superuser");
				$this->insert_type_app("superuser","User Manager");
				$this->insert_type_app("superuser","Application Manager");
				$this->insert_type_app("superuser","Site Manager");
			} else {
				print "Table Creation FAILED<br /><hr />\n";
			}
		}
	}

	public function message($message) {
		?>
		<div id="system-message">
		<? print $message; ?>
		</div>
		<?
	}

	public function make_links($text) {
		return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1">$1</a>', $text);
	}

	public function format_date($date) {
		$parts = preg_split("/ /",$date);
		$date = $parts[0];
		$parts = preg_split("/-/",$date);
		return date("m/d/Y",mktime(0,0,0,$parts[1],$parts[2],$parts[0]));
	}

	public function print_header($headinfo) {
		global $ROOT;
		global $ROOTPATH;
		global $LOGGED_IN;
		$path = $ROOTPATH."applications/interface/header.php";
		include_once($path);
	}

	public function print_footer() {
		global $ROOT;
		global $ROOTPATH;
		$path = $ROOTPATH."applications/interface/footer.php";
		include_once($path);
	}

	public function getBrowser() {
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";

		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		} elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		} elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}

		// Next get the name of the useragent yes seperately and for good reason
		if (preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		} elseif (preg_match('/Firefox/i',$u_agent)) {
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		} elseif (preg_match('/Chrome/i',$u_agent)) {
			$bname = 'Google Chrome';
			$ub = "Chrome";
		} elseif (preg_match('/Safari/i',$u_agent)) {
			$bname = 'Apple Safari';
			$ub = "Safari";
		} elseif (preg_match('/Opera/i',$u_agent)) {
			$bname = 'Opera';
			$ub = "Opera";
		} elseif (preg_match('/Netscape/i',$u_agent)) {
			$bname = 'Netscape';
			$ub = "Netscape";
		}

		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
		}

		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)) {
			    $version= $matches['version'][0];
			} else {
			    $version= $matches['version'][1];
			}
		} else {
			$version= $matches['version'][0];
		}

		// check if we have a number
		if ($version==null || $version=="") {$version="?";}

		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'    => $pattern
		);
	}

	public function update_ip_data() {
		$ip = $_SERVER["REMOTE_ADDR"];
		$forwarded_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		$language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
		$ua = getBrowser();
		$browser = $ua["name"]." ".$ua["version"];
		$operating_system = $ua["platform"];
		$row = $this->dbirow("SELECT * FROM ip_data WHERE ip=?;","s",array($ip));
		if ($row["id"] != "") {
			$this->dbiupdate("UPDATE ip_data SET date=NOW(), hits=hits+1 WHERE ip=?;","s",array($ip));
		} else {
			$this->dbiupdate("INSERT INTO ip_data (ip,forwarded_ip,hits,language,browser,operating_system,date) VALUES (?,?,'1',?,?,?,NOW());","sssss",array($ip,$forwarded_ip,$language,$browser,$operating_system));
		}
	}

};

?>
