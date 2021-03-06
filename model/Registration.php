<?php


class Registration
{

	private $db_connection = null;

	private $user_name = "";

	private $user_email = "";

	private $user_password = "";

	private $user_password_hash = "";

	public $registration_successful = false;

	public $errors = array();

	public $messages = array();


	public function __construct($login)
	{
		if (isset($_POST["register"])) {
			// $login->doLogout();
			$this->registerNewUser();
		}
	}

	private function registerNewUser()
	{
		if (empty($_POST['user_name'])) {
			$this->errors[] = "Empty Username";
		} elseif (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat'])) {
			$this->errors[] = "Empty Password";
		} elseif ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
			$this->errors[] = "Password and password repeat are not the same";
		} elseif (strlen($_POST['user_password_new']) < 6) {
			$this->errors[] = "Password has a minimum length of 6 characters";
		} elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 2) {
			$this->errors[] = "Username cannot be shorter than 2 or longer than 64 characters";
		} elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])) {
			$this->errors[] = "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters";
		} elseif (empty($_POST['user_email'])) {
			$this->errors[] = "Email cannot be empty";
		} elseif (strlen($_POST['user_email']) > 64) {
			$this->errors[] = "Email cannot be longer than 64 characters";
		} elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
			$this->errors[] = "Your email address is not in a valid email format";
		} elseif (!empty($_POST['user_name'])
			&& strlen($_POST['user_name']) <= 64
			&& strlen($_POST['user_name']) >= 2
			&& preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])
			&& !empty($_POST['user_email'])
			&& strlen($_POST['user_email']) <= 64
			&& filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)
			&& !empty($_POST['user_password_new'])
			&& !empty($_POST['user_password_repeat'])
			&& ($_POST['user_password_new'] === $_POST['user_password_repeat'])
		) {

			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

			if (!$this->db_connection->connect_errno) {

				
				$this->user_name = $this->db_connection->real_escape_string(htmlentities($_POST['user_name'], ENT_QUOTES));
				$this->user_email = $this->db_connection->real_escape_string(htmlentities($_POST['user_email'], ENT_QUOTES));

				$this->class_id = $this->db_connection->real_escape_string(htmlentities($_POST['class_id'], ENT_QUOTES));
				$filter_subjects = '';
				for ($idx = 0; $idx <= 100; $idx++) {
					if (isset($_POST["subject_radio_$idx"])) {
						$filter_subjects = $filter_subjects.$_POST["subject_radio_$idx"].';';
					}
				}

				$this->user_password = $_POST['user_password_new'];

              
				$this->user_password_hash = password_hash($this->user_password, PASSWORD_DEFAULT);


				$query_check_user_name = $this->db_connection->query("SELECT * FROM users WHERE user_name = '" . $this->user_name . "';");

				if ($query_check_user_name->num_rows == 1) {
					echo "<span class='alert alert-danger'>Sorry, that user name is already taken. Please choose another one.</span>";
				} else {
					// write new users data into database
					$query_new_user_insert = $this->db_connection->query("INSERT INTO users (user_name, user_password_hash, user_email, user_registration_ip, user_registration_datetime, class_id, filter_subjects) VALUES('" . $this->user_name . "', '" . $this->user_password_hash . "', '" . $this->user_email . "', '".$_SERVER['REMOTE_ADDR']."', now(), '".$this->class_id."', '".$filter_subjects."')");

					if ($query_new_user_insert) {
						echo "Your account has been created successfully. You can now log in.";
						$this->registration_successful = true;
						
					} else {
						echo "<span class='alert alert-danger'>Sorry, your registration failed. Please go back and try again.</span>";
					}
				}
			} else {
				echo "<span class='alert alert-danger'>Sorry, no database connection.</span>";
			}
		} else {
			echo "<span class='alert alert-danger'>An unknown error occurred.</span>";
		}
	}
}
