<?php
// DB class
class DB
{
	// variables
	public $con;
	private static $instance = null;
	
	// constructor
	private function __construct(){
		// connect
		$this->mysqlConnect();
	}
	
	// get instance
	public static function getInstance(){
		// check
		if (is_null(self::$instance)) {
			self::$instance = new DB();
		}
		return self::$instance;
	}

	// connect to database
	public function mysqlConnect(){
		// check
		if(!$this->con){
			// conf object
			$confObj = Conf::getInstance();
			$aDBInfo = $confObj->getDBInfo();

			// connect to database
			$this->con = mysql_connect($aDBInfo['hostname'],$aDBInfo['dbuser'],$aDBInfo['dbpassword']);
			if (!$this->con) {
			    die('Could not connect: ' . mysql_error());
			}
			mysql_select_db($aDBInfo['dbname'],$this->con);
		}
	}

	// run a sql query
	public function query($query){
		if(!empty($query)){
			$this->mysqlConnect();
			$result = mysql_query($query,$this->con);
			return $result;
		}else{
			return false;
		}
	}

	// get all of sql query results
	public function getAll($query){
		$this->mysqlConnect();
		$aResult = array();
		$aCounter = 0;
		$result = mysql_query($query,$this->con);
		while($row = mysql_fetch_assoc($result)){
			foreach($row as $key => $value){
				$aResult[$aCounter][$key] = $value;
			}
			$aCounter++;
		}
		return $aResult;
	}

	// get first index of sql query results
	public function getOne($query){
		$this->mysqlConnect();
		$result = mysql_query($query,$this->con);
		$row = mysql_fetch_row($result);
		return $row[0];
	}

	public function numRows($query){
		$this->mysqlConnect();
		$result = mysql_query($query,$this->con);
		$row = mysql_num_rows($result);
		return $row;
	}
}
?>
