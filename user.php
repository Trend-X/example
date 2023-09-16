<?php
// User class
class User
{
	private static $instance = null;
	
	// constructor
	private function __construct(){
	}
	
	// get instance
	public static function getInstance(){
		// check
		if (is_null(self::$instance)) {
			self::$instance = new User();
		}
		return self::$instance;
	}
	
	// check for email address
	public function isEmailUnique($email){
		// create DB object
		$dbObj = DB::getInstance();

		// insert new user
		$result = $dbObj->getOne("select user_id from user where email = '$email'");

		if($result){
			return false;
		}else{
			return true;
		}
	}

	// check for email address
	public function isAdminEmailUnique($email){
		// create DB object
		$dbObj = DB::getInstance();

		// insert new user
		$result = $dbObj->getOne("select admin_user_id from admin_user where email = '$email'");

		if($result){
			return false;
		}else{
			return true;
		}
	}

	// get UID
	public function getUID(){
		// check session
		$SID = session_id();
		if(empty($SID)){
			session_start();
		}

		if(isset($_SESSION['UID'])){
			// create DB object
			$dbObj = DB::getInstance();

			// get UID
			$UID = $_SESSION['UID'];

			// check user id
			$checkUserId = $dbObj->getOne("select user_id from user where user_id = $UID and blocked = 0 and enabled = 1 limit 1");
			if($checkUserId){
				return $UID;
			}else{
				unset($_SESSION['UID']);
				return false;
			}
		}else{
			return false;
		}
	}
	
	// get TID
	public function getTID(){
		// check session
		$SID = session_id();
		if(empty($SID)){
			session_start();
		}

		if(isset($_SESSION['TID']) && isset($_SESSION['UID'])){
			// create DB object
			$dbObj = DB::getInstance();
			
			// get UID
			$UID = $this->getUID();

			// get TID
			$TID = $_SESSION['TID'];

			// check team id
			$checkTeamId = $dbObj->getOne("select team_id from team where team_id = $TID and user_id = $UID limit 1");
			if($checkTeamId){
				return $TID;
			}else{
				unset($_SESSION['TID']);
				return false;
			}
		}else{
			return false;
		}
	}

	// get AID
	public function getAID(){
		// check session
		$SID = session_id();
		if(empty($SID)){
			session_start();
		}

		if(isset($_SESSION['AID'])){
			// create DB object
			$dbObj = DB::getInstance();

			// get AID
			$AID = $_SESSION['AID'];

			// check user id
			$checkUserId = $dbObj->getOne("select admin_user_id from admin_user where admin_user_id = $AID limit 1");
			if($checkUserId){
				return $AID;
			}else{
				unset($_SESSION['AID']);
				return false;
			}
		}else{
			return false;
		}
	}
	
	// set refId
	public function setRefId($refId){
		// check session
		$SID = session_id();
		if(empty($SID)){
			session_start();
		}
		
		// set
		$_SESSION['REFID'] = $refId;
		
		return true;
	}
	
	// get refId
	public function getRefId(){
		// check session
		$SID = session_id();
		if(empty($SID)){
			session_start();
		}

		if(isset($_SESSION['REFID'])){
			// create DB object
			$dbObj = DB::getInstance();

			// get refId
			$refId = $_SESSION['REFID'];

			// check ref id
			$checkRefId = $dbObj->getOne("select user_id from user where blocked = 0 and enabled = 1 and user_id = ".$refId);
			if($checkRefId){
				// get ip address
				$ipAddress = $_SERVER['REMOTE_ADDR'];
				
				// check ip address
				$refererIPAddress = $dbObj->getOne("select ip from user_status where user_id = '".$refId."'");
				if($ipAddress != $refererIPAddress){
					return $refId;
				}else{
					unset($_SESSION['REFID']);
					return false;
				}
			}else{
				unset($_SESSION['REFID']);
				return false;
			}
		}else{
			return false;
		}
	}

	// set user to online
	public function setUserToOnline($userId){
		// create DB object
		$dbObj = DB::getInstance();

		// get datetime
		$dateTime = date('Y-m-d H:i:s');

		// check session
		$SID = session_id();
		if(empty($SID)){
			session_start();
		}
		
		// check last login time
		$todayDate = date("Y-m-d");
		$todayLoginCheck = $dbObj->getOne("select user_id from user_status where datetime >= '$todayDate' and user_id = ".$userId);
		if(!$todayLoginCheck){
			// login flag
			$_SESSION['LOGINPRIZE'] = true;
		}

		// get ip address
		$ipAddress = $_SERVER['REMOTE_ADDR'];
		
		// check for online
		$checkForOnline = $dbObj->getOne("select user_id from user_status where user_id = ".$userId);
		if($checkForOnline){
			// update datetime
			$dbObj->query("update user_status set ip = '$ipAddress', datetime = '$dateTime', signed_out = 0 where user_id = $userId limit 1");
		}else{
			// insert new record
			$dbObj->query("insert into user_status (user_id,ip,datetime) values($userId,'$ipAddress','$dateTime') ");
		}
		return true;
	}

	// check sessionId
	public function checkAndRenewSessionId(&$encryptedPage_){
		// conf object
		$confObj = Conf::getInstance();
		$baseDir = $confObj->getBaseDir();

		$UID = $this->getUID();
		if($UID){
			// create DB object
			$dbObj = DB::getInstance();

			// check UID 
			$checkUID = $dbObj->getOne("select user_id from user where user_id = '$UID'");
			if($checkUID){
				// set user to online
				$this->setUserToOnline($UID);

				// user id 
				return $UID;

			}elseif(!$checkUID && $encryptedPage_){
				header("location: ".$baseDir."signin.php?redirect=1");
			}
		}elseif(!$UID && $encryptedPage_){
			header("location: ".$baseDir."signin.php?redirect=1");
		}
		return false;
	}

	// check admin sessionId
	public function checkAndRenewAdminSessionId(&$encryptedPage_){
		// conf object
		$confObj = Conf::getInstance();
		$baseDir = $confObj->getBaseDir();

		$AID = $this->getAID();
		if($AID){
			// create DB object
			$dbObj = DB::getInstance();

			// check AID 
			$checkAID = $dbObj->getOne("select admin_user_id from admin_user where admin_user_id = '$AID'");
			if($checkAID){
				// admin user id 
				return $AID;

			}elseif(!$checkAID && $encryptedPage_){
				header("location: ".$baseDir."admin/signin.php");
			}
		}elseif(!$AID && $encryptedPage_){
			header("location: ".$baseDir."admin/signin.php");
		}
		return false;
	}

	// signin user
	public function signinUser($email,$password){
		// create DB object
		$dbObj = DB::getInstance();
		
		// create Football object
		$footballObj = Football::getInstance();

		// get user Id, check email address
		$userId = $dbObj->getOne("select user_id from user where email = '$email'");
		if($userId){
			// get user info
			$userStatus = $dbObj->getAll("select blocked,enabled from user where user_id = '".$userId."' ");
			if($userStatus[0]['enabled'] && !$userStatus[0]['blocked']){

				// get salt key
				$salt = $dbObj->getOne("select salt from user where user_id = '$userId'");

				// check password
				$md5Password = md5($password.$salt);
				$checkPassword = $dbObj->getOne("select user_id from user where email = '$email' and password = '$md5Password'");
				if($checkPassword){
					// signed in successfuly
					$SID = session_id();
					if(empty($SID)){
						session_start();
					}
					$_SESSION['UID'] = $userId;
					$token = md5(uniqid(rand(),TRUE));
					$_SESSION['token'] = $token;

					// get main team id
					$teamId = $dbObj->getOne("select team_id from team where user_id = '$userId' and type = 1");
					if($teamId){
						$_SESSION['TID'] = $teamId;
					}
					
					// check today login
					$sixHoursAgoDate = date("Y-m-d H:i:s",mktime(date("H")-6,date("i"),date("s"),date("m"),date("d"),date("Y")));
					$sixHoursLoginCheck = $dbObj->getOne("select user_id from user_status where datetime >= '$sixHoursAgoDate' and user_id = ".$userId);
					if(!$sixHoursLoginCheck){
						// set team award chance
						$footballObj->setTeamAwardChance($teamId,3);
					}

					// set user to online
					$this->setUserToOnline($userId);

					return 1;
				}else{
					// password is incorrect
					return -2;
				}
			}elseif(!$userStatus[0]['enabled']){
				// user is not enabled
				return -3;

			}elseif($userStatus[0]['blocked']){
				// user is blocked
				return -4;
			}
		}else{
			// email address is incorrect
			return -1;
		}
	}

	// signin admin user
	public function signinAdminUser($email,$password){

		// create DB object
		$dbObj = DB::getInstance();

		// get user Id, check email address
		$userId = $dbObj->getOne("select admin_user_id from admin_user where email = '$email'");
		if($userId){
			// get salt key
			$salt = $dbObj->getOne("select salt from admin_user where admin_user_id = '$userId'");

			// check password
			$md5Password = md5($password.$salt);
			$checkPassword = $dbObj->getOne("select admin_user_id from admin_user where email = '$email' and password = '$md5Password'");
			if($checkPassword){
				// signed in successfuly
				$SID = session_id();
				if(empty($SID)){
					session_start();
				}
				$_SESSION['AID'] = $userId;

				return 1;
			}else{
				// password is incorrect
				return -2;
			}
		}else{
			// email address is incorrect
			return -1;
		}
	}

	// check admin password
	public function checkAdminPassword($userId,$password){
		// create DB object
		$dbObj = DB::getInstance();

		// get salt key
		$salt = $dbObj->getOne("select salt from admin_user where admin_user_id = '$userId'");

		// check password
		$md5Password = md5($password.$salt);
		$checkPassword = $dbObj->getOne("select admin_user_id from admin_user where admin_user_id = '$userId' and password = '$md5Password'");
		if($checkPassword){
			return true;
		}else{
			// password is incorrect
			return false;
		}
	}

	// check user password
	public function checkUserPassword($userId,$password){
		// create DB object
		$dbObj = DB::getInstance();

		// get salt key
		$salt = $dbObj->getOne("select salt from user where user_id = '$userId'");

		// check password
		$md5Password = md5($password.$salt);
		$checkPassword = $dbObj->getOne("select user_id from user where user_id = '$userId' and password = '$md5Password'");
		if($checkPassword){
			return true;
		}else{
			// password is incorrect
			return false;
		}
	}

	// switch user team
	public function switchUserTeam(){
		// get UID
		$UID = $this->getUID();

		// create DB object
		$dbObj = DB::getInstance();

		$SID = session_id();
		if(empty($SID)){
			session_start();
		}
					
		// switch user team
		if($UID && isset($_SESSION['TID']) && !empty($_SESSION['TID'])){
			$currentTeamId = $_SESSION['TID'];
			$currentTeamType = $dbObj->getOne("select type from team where team_id = '$currentTeamId' ");
			if($currentTeamType == 1){
				$teamId = $dbObj->getOne("select team_id from team where user_id = '$UID' and type = 2");
				if($teamId){
					$_SESSION['TID'] = $teamId;
				}
			}else{
				$teamId = $dbObj->getOne("select team_id from team where user_id = '$UID' and type = 1");
				if($teamId){
					$_SESSION['TID'] = $teamId;
				}
			}
		}
	}
	
	// signout user
	public function signoutUser(){
		// get UID
		$UID = $this->getUID();

		// create DB object
		$dbObj = DB::getInstance();

		// set signout flag
		$dbObj->query("update user_status set signed_out = 1 where user_id = $UID limit 1");

		// destroy session
		$SID = session_id();
		if(empty($SID)){
			session_start();
		}
		unset($_SESSION['UID']);
		unset($_SESSION['TID']);
	}

	// signout admin user
	public function signoutAdminUser(){
		// destroy session
		$SID = session_id();
		if(empty($SID)){
			session_start();
		}
		unset($_SESSION['AID']);
	}

	// create random salt
	public function createRandomSalt(){
		$aSalt = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
				'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
				'1','2','3','4','5','6','7','8','9','0');
		$aSaltCount = count($aSalt);
		$saltKey = '';
		for($i = 0; $i < 5; $i++){
			$saltKey .= $aSalt[rand(0,$aSaltCount-1)];
		}
		return $saltKey;
	}

	// insert a new user
	public function insertNewUser($aData){
		// create DB object
		$dbObj = DB::getInstance();

		// create jCalendar object
		$calendarObj = new jCalendar;
		$addDateFa = $calendarObj->date("Y/m/d H:i:s");
		$addDate = date("Y-m-d H:i:s");

		// create salt key
		$salt = $this->createRandomSalt();

		// create password & salt md5
		$md5Password = md5($aData['password'].$salt);
		
		// create confirm code
		$confirmCode = $this->createRandomCode();
		
		// get ref id
		$refId = $this->getRefId();

		// insert new user
		$result = $dbObj->query("insert into user (email,password,salt,confirm_code,first_name,last_name,professional_account_date,enabled,add_date,add_date_fa)
			values ('".$aData['emailAddr']."','".$md5Password."','".$salt."','".$confirmCode."','".$aData['firstname']."','".$aData['lastname']."','".$addDate."','1','".$addDate."','".$addDateFa."')");

		// get user id
		$userId = mysql_insert_id();

		// insert referer
		if($refId){
			$dbObj->query("insert into affiliate (user_id,ref_id) values ('".$userId."','".$refId."')");
		}

		// get user cash
		$userCash = $dbObj->getOne("select value from game_config where config_id = 31 ");

		// insert cash record
		$dbObj->query("insert into user_cash (user_id,cash,sms_cash) values ('".$userId."',".$userCash.",0)");

		// insert sms config record
		$dbObj->query("insert into user_sms_conf (user_id) values ('".$userId."')");

		// insert email config record
		$dbObj->query("insert into user_email_conf (user_id) values ('".$userId."')");

		// insert stat record
		$dbObj->query("insert into user_stat (user_id,profile_visit) values ('".$userId."',0)");

		// send email
		$aData['code'] = $confirmCode;
		$mailObj = Mail::getInstance();
		$mailObj->sendSignUpMail($aData);
		//$mailObj->sendSignUpMailToAdmin($aData);

		return $result;
	}

	// insert a new admin user
	public function insertNewAdminUser($aData){
		// create DB object
		$dbObj = DB::getInstance();

		// create salt key
		$salt = $this->createRandomSalt();

		// create password & salt md5
		$md5Password = md5($aData['password'].$salt);

		// insert new admin user
		$result = $dbObj->query("insert into admin_user (email,password,salt,first_name,last_name) 
			values ('".$aData['emailAddr']."','".$md5Password."','".$salt."','".$aData['firstname']."','".$aData['lastname']."')");

		return $result;
	}

	// update admin user
	public function updateAdminUser($userId,$aData){
		// create DB object
		$dbObj = DB::getInstance();

		// create salt key
		$salt = $this->createRandomSalt();

		// create password & salt md5
		$md5Password = md5($aData['newPassword'].$salt);

		// update user
		$result = $dbObj->query("update admin_user set 
				salt = '".$salt."',
				password = '".$md5Password."'
				where admin_user_id = $userId limit 1");

		return $result;
	}

	// update user password
	public function updateUserPassword($userId,$aData){
		// create DB object
		$dbObj = DB::getInstance();

		// create salt key
		$salt = $this->createRandomSalt();

		// create password & salt md5
		$md5Password = md5($aData['newPassword'].$salt);

		// update user
		$result = $dbObj->query("update user set 
				salt = '".$salt."',
				password = '".$md5Password."'
				where user_id = $userId limit 1");

		return $result;
	}

	// update user
	public function updateUserProfile($aData){
		$UID = $this->getUID();

		// create DB object
		$dbObj = DB::getInstance();

		// update user
		$result = $dbObj->query("update user set 
				first_name = '".$aData['firstname']."',
				last_name = '".$aData['lastname']."',
				comment = '".$aData['comment']."',
				gender = '".$aData['gender']."',
				age = '".$aData['age']."',
				country_id = '".$aData['country']."',
				state_id = '".$aData['state']."',
				city_id = '".$aData['city']."',
				mobile = '".$aData['mobile']."',
				yahoo_id = '".$aData['yahooId']."',
				skype_id = '".$aData['skypeId']."'
				where user_id = $UID limit 1");

		return $result;
	}

	// create random password
	public function createRandomPassword(){
		$aPassword = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
				'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
				'1','2','3','4','5','6','7','8','9','0');
		$aPasswordCount = count($aPassword);
		$password = '';
		for($i = 0; $i < 6; $i++){
			$password .= $aPassword[rand(0,$aPasswordCount-1)];
		}
		return $password;
	}

	// create random code
	public function createRandomCode(){
		$aCode = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
				'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
				'1','2','3','4','5','6','7','8','9','0');
		$aCodeCount = count($aCode);
		$code = '';
		for($i = 0; $i < 20; $i++){
			$code .= $aCode[rand(0,$aCodeCount-1)];
		}
		return $code;
	}

	// send user password reset mail
	public function userPasswordResetMail($email){
		// create DB object
		$dbObj = DB::getInstance();

		// get date
		$addDate = date("Y-m-d H:i:s");

		// create random code
		$codeFlag = false;
		while(!$codeFlag){
			$code = $this->createRandomCode();
			$checkCode = $dbObj->getOne("select user_password_reset_code_id from user_password_reset_code where code = '".$code."'");
			if(!$checkCode){
				$codeFlag = true;
			}
		}

		// get user Id
		$userId = $dbObj->getOne("select user_id from user where email = '".$email."'");

		// insert code
		$result = $dbObj->query("insert into user_password_reset_code (user_id,code,add_date) 
					values ('".$userId."','".$code."','".$addDate."')");

		// send email
		$mailObj = Mail::getInstance();
		$mailObj->sendUserPasswordResetMail($email,"لینک تجدید رمزعبور",$code);

		return $result;
	}

	// reset user password
	public function userPasswordReset($code){
		// create DB object
		$dbObj = DB::getInstance();

		// create random password
		$password = $this->createRandomPassword();

		// get user Id
		$userId = $dbObj->getOne("select user_id from user_password_reset_code where code = '".$code."'");

		// get user salt
		$salt = $dbObj->getOne("select salt from user where user_id = '".$userId."'");

		// create password & salt md5
		$md5Password = md5($password.$salt);

		// update password
		$result = $dbObj->query("update user set password = '".$md5Password."' where user_id = '".$userId."' limit 1");

		// delete reset code
		$dbObj->query("delete from user_password_reset_code where code = '".$code."'");

		// get user email
		$email = $dbObj->getOne("select email from user where user_id = '".$userId."'");

		// send email
		$mailObj = Mail::getInstance();
		$mailObj->sendUserResetedPasswordMail($email,"رمزعبور جدید",$password);

		return $result;
	}

	// delete old password reset codes
	public function deleteOldPasswordResetCodes(){
		// create DB object
		$dbObj = DB::getInstance();

		$yesterdayTimeStamp  = mktime(date("H"),date("i"),date("s"),date("m"),date("d")-1,date("Y"));
		$yesterdayDate = date("Y-m-d H:i:s",$yesterdayTimeStamp);

		// delete reset codes
		$dbObj->query("delete from user_password_reset_code where add_date < '".$yesterdayDate."'");
		$dbObj->query("delete from customer_password_reset_code where add_date< '".$yesterdayDate."'");

		return true;
	}

	// is user online
	public function isUserOnline($userId){
		// create DB object
		$dbObj = DB::getInstance();

		$someMinAgoTimeStamp  = mktime(date("H"),date("i")-30,date("s"),date("m"),date("d"),date("Y"));
		$someMinAgo = date("Y-m-d H:i:s",$someMinAgoTimeStamp);

		// get online user
		$onlineUser = $dbObj->getOne("select u.user_id from user as u, user_status as us 
					where us.datetime > '$someMinAgo' and 
					us.signed_out = 0 and
					u.user_id = us.user_id and
					u.user_id = '$userId'");
		if($onlineUser){
			return true;
		}else{
			return false;
		}
	}
}

?>
