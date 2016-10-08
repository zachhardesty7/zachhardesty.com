<?php
/********************************************************************
 * Db Driver File, Autiomatic choice the connection mysql or mysqli *
 * Code-Base : Infinitewp Base Panel                                *
 * Auther : Senthil kumar V - Tech Lead, Revmakx Techonology Pvt    *
 * Copyright (c) 2012 Revmakx                                       *
 * www.revmakx.com                                                  *
 *                                                                  *
 *******************************************************************/

//Code from mysql.php - Start
class DBMysql{
	
	protected $DBLink;
	protected $DBHost;
	protected $DBUsername;
	protected $DBPassword;
	protected $DBName;
	protected $DBPort;
	
	function __construct($DBHost, $DBUsername, $DBPassword, $DBName, $DBPort){
		$this->DBHost = $DBHost;
		$this->DBUsername = $DBUsername;
		$this->DBPassword = $DBPassword;
		$this->DBName = $DBName;
		$this->DBPort = $DBPort;
	}
	
	function connect(){
		$this->DBLink = mysql_connect($this->DBHost.':'.$this->DBPort, $this->DBUsername, $this->DBPassword);
		if (!$this->DBLink) {
			return 'Mysql connect error: (' . mysql_error().') '.$this->error();
		}
		if (!mysql_select_db($this->DBName, $this->DBLink)){
			return 'Mysql connect error: (' . $this->errorNo().') '.$this->error();
		} else {
			return true;
		}
	}
	
	function query($SQL){
		
		$result = mysql_query($SQL, $this->DBLink);
		
		if(empty($result)){			
			$errno = $this->errorNo();
			if ($errno == 2013 || $errno == 2006){
				$this->connect();
				return mysql_query($SQL, $this->DBLink);
			}
		}
		
		return $result;
	}
	
	function insertID(){
		return mysql_insert_id($this->DBLink);
	}
	
	function affectedRows(){
		return mysql_affected_rows($this->DBLink);
	}	
	
	function realEscapeString($val){
		return mysql_real_escape_string($val, $this->DBLink);
	}
	
	function ping(){
		return mysql_ping($this->DBLink);	
	}
	
	function errorNo(){
		return mysql_errno($this->DBLink);
	}
	
	function error(){
		return mysql_error($this->DBLink);
	}
}

class DBMysqlResult{
	
	private $DBResult;
	
	function __construct($newResult)
	{
		$this->DBResult = $newResult;
	}
	function numRows()
	{
		return mysql_num_rows($this->DBResult);
	}
	function nextRow()
	{
		return mysql_fetch_assoc($this->DBResult);
	}
	function rowExists()
	{
		if (!$this->numRows())
			return false;
		return true;
	}
	function free(){
		return mysql_free_result($this->DBResult);
	}
}
//Code from mysql.php - End

//Code from mysqli.php - Start
class DBMysqli{
	
	protected $DBLink;
	protected $DBHost;
	protected $DBUsername;
	protected $DBPassword;
	protected $DBName;
	protected $DBPort;
	
	
	function __construct($DBHost, $DBUsername, $DBPassword, $DBName, $DBPort){
		$this->DBHost = $DBHost;
		$this->DBUsername = $DBUsername;
		$this->DBPassword = $DBPassword;
		$this->DBName = $DBName;
		$this->DBPort = $DBPort;
	}
	
	function connect(){
		$this->DBLink = new mysqli($this->DBHost, $this->DBUsername, $this->DBPassword, $this->DBName, $this->DBPort);
		if ($this->DBLink->connect_errno) {
			return 'Mysql connect error: (' . $this->DBLink->connect_errno.') '.$this->DBLink->connect_error;
		} else {
			return true;
		}
	}
	
	function query($SQL){
		$result = $this->DBLink->query($SQL);
		
		if(empty($result)){			
			$errno = $this->errorNo();
			if ($errno == 2013 || $errno == 2006){
				$this->connect();
				return $this->DBLink->query($SQL);
			}
		}
		
		return $result;
	}
	
	function insertID(){
		return $this->DBLink->insert_id;
	}
	
	function affectedRows(){
		return $this->DBLink->affected_rows;
	}	
	
	function realEscapeString($val){
		return $this->DBLink->real_escape_string($val);
	}
	
	function ping(){
		return $this->DBLink->ping();	
	}
	
	function errorNo(){
		return $this->DBLink->errno;
	}
	
	function error(){
		return $this->DBLink->error;
	}
}

class DBMysqliResult{
	
	private $DBResult;
	
	function __construct($newResult)
	{
		$this->DBResult = $newResult;
	}
	function numRows()
	{
		return $this->DBResult->num_rows;
	}
	function nextRow()
	{
		return $this->DBResult->fetch_assoc();
	}
	function rowExists()
	{
		if (!$this->numRows())
			return false;
		return true;
	}
	function free(){
		$this->DBResult->free();
	}
}
//Code from mysqli.php - End

//Code from db.php - Start
class DB{
	
	private static $queryString;
	private static $printQuery;
	private static $printAllQuery;
	private static $DBDriver;
	public static $DBResultClass;
	//private static $showError;
	//private static $showSQL;
	
	
	public static function connect($DBHost, $DBUsername, $DBPassword, $DBName, $DBPort){
		$driver = self::getDriver();
		if(in_array($driver, array('mysql', 'mysqli'))){
			$DBClass = 'DB'.ucfirst($driver);
			self::$DBResultClass = $DBClass.'Result';
			
			self::$DBDriver = new $DBClass($DBHost, $DBUsername, $DBPassword, $DBName, $DBPort);
			$DBConnect = self::$DBDriver->connect();
			if($DBConnect !== true) {
				return $DBConnect;
			}
		} else {
			return "PHP has no mysql extension installed";
		}
		return true;
	}

	public static function getDriver() {
		if(class_exists('mysqli')){
				$driver = 'mysqli';
		}
		elseif(function_exists('mysql_connect')){
				$driver = 'mysql';
		}
		else{
				return false;
		}
		return $driver;
	}
	
	private static function get($params, $type){
		if(empty($params)) return false;
		
		$result = array();		
		$query = self::prepareQ('select', $params);
		
		$query_result = self::doQuery($query);	
		if(!$query_result) return $query_result;	
		$_result = new self::$DBResultClass($query_result);	
		
		
		if($_result){
			if($type == 'array'){
				while($row = $_result->nextRow()){
					if(!empty($params[3])){//array key hash
						$result[ $row[$params[3]] ] = $row;
					}
					else{
					$result[] = $row;
				}
			}
			}
			elseif($type == 'row'){
				$result = $_result->nextRow();
			}
			elseif($type == 'exists'){
				$result = $_result->rowExists();
			}
			elseif($type == 'field'){
				$row = $_result->nextRow();
				$result = ($row && is_array($row)) ? reset($row) : NULL;
			}
			elseif($type == 'fields'){
				while($row = $_result->nextRow()){
					if(!empty($params[3])){//array key hash
						$result[ $row[$params[3]] ] = reset($row);
					}
					else{
					$result[] = reset($row);
					}
				}
			}
			$_result->free();
		}
		return $result;
	}
	
	public static function getArray(){//table, select, conditions
		$args = func_get_args();
		return self::get($args, 'array');
	}
	public static function getRow(){//table, select, conditions
		$args = func_get_args();
		return self::get($args, 'row');
	}
	
	public static function getExists(){//table, select, conditions
		$args = func_get_args();
		return self::get($args, 'exists');
	}
	
	public static function getField(){//table, select, conditions
		$args = func_get_args();
		return self::get($args, 'field');
	}
	
	public static function getFields(){//table, select, conditions
		$args = func_get_args();
		return self::get($args, 'fields');
	}
	
	private static function prepareQ($type, $params){
		
		if(!empty($params) && count($params) == 1){
			return $params[0];
		}
		
		if($type == 'select'){
			if(empty($conditions)){ $conditions = 'true'; }
			return "SELECT ".$params[1]." FROM ".$params[0]." WHERE ".$params[2];
		}
		elseif($type == 'insert' || $type == 'replace'){
			if(is_array($params[1])) $params[1] = self::array2MysqlSet($params[1]);
			return ($type == 'insert' ? "INSERT" : "REPLACE")." INTO ".$params[0]." SET ".$params[1];
		}
		elseif($type == 'update'){
			if(is_array($params[1])) $params[1] = self::array2MysqlSet($params[1]);
			return "UPDATE ".$params[0]." SET ".$params[1]." WHERE ".$params[2];
		}
		elseif($type == 'delete'){
			return "DELETE FROM ".$params[0]." WHERE ".$params[1];
		}
	}
	
	public static function insert(){//table, setCommand
		$args=func_get_args();
		$query = self::prepareQ('insert', $args);
		return self::insertReplace($query);
	}
	
	
	public static function replace(){//table, setCommand
		$args=func_get_args();
		$query = self::prepareQ('replace', $args);
		return self::insertReplace($query);
	}
	
	private static function insertReplace($query){
		
		if(self::doQuery($query)){
			$lastInsertID = self::lastInsertID();
			if(!empty($lastInsertID)) return $lastInsertID;
			return true;
		}
		return false;
	}
	
	public static function update(){//table, setCommand, conditions
		$args=func_get_args();
		$query = self::prepareQ('update', $args);
		return self::doQuery($query);
	}
	
	public static function delete(){//table, conditions
		$args=func_get_args();
		$query = self::prepareQ('delete', $args);
		return self::doQuery($query);
	}

	public static function doQuery($queryString){	
	
		//$queryString = str_replace('?:', Reg::get('config.SQL_TABLE_NAME_PREFIX'), $queryString);
		
		self::$queryString = $queryString;
		
		if(self::$printAllQuery || self::$printQuery)
			echo '<br>'.self::$queryString.'<br>';

		$query = self::$DBDriver->query(self::$queryString);

		if($query)
			 return $query;
		else
		{
			self::printError(debug_backtrace());
			echo "\n".self::$queryString."\n<br>";
			return false;
		}
	}
	
	public static function getLastQuery(){//avoid using this function, it should be called as soon as query is executed
		return self::$queryString;		
	}
	
	private static function lastInsertID(){
		return self::$DBDriver->insertID();
	}
	
	public static function errorNo(){
		return self::$DBDriver->errorNo();
	}
	
	public static function error(){
		return self::$DBDriver->error();
	}
	
	public static function affectedRows(){
		return self::$DBDriver->affectedRows();
	}
	
	public static function realEscapeString($val){
		return self::$DBDriver->realEscapeString($val);
	}
	
	public static function escapse($val){ //same as public static function realEscapeString($val) 
		return self::$DBDriver->realEscapeString($val);
	}
	
	private static function printError($traceback_detail){
		echo "<b>Manual SQL Error</b>: [". self::$DBDriver->errorNo()."] " . self::$DBDriver->error() . "<br />\n
		 in file <b>" . $_SERVER['PHP_SELF'] ."</b> On line <b>" . $traceback_detail[count($traceback_detail) - 1]['line'] . "</b><br> ";
	}
	
	private static function array2MysqlSet($array){
		$mysqlSet='';
		$isPrev=false;
		foreach($array as $key => $value)
		{
			if($isPrev) $mysqlSet .= ', ';
			if(isset($value) && is_array($value))
				$mysqlSet .= $key." = ".self::realEscapeString($value[0]).""; //without quotes
			else
				$mysqlSet .= $key." = '".self::realEscapeString($value)."'";
			$isPrev = true;
		}
		return $mysqlSet;
	}
	
	private static function array2MysqlSelect($array){
		$mysqlSet='';
		$isPrev=false;
		foreach($array as $key => $value)
		{
			if($isPrev) $mysqlSet .= ', ';
			$mysqlSet .= $value;
			$isPrev = true;
		}
		return $mysqlSet;
	}
	
	public static function setPrintQuery($var){
		self::$printQuery = $var;
	}
}

//-------------------------------------------------------------------------------------------------------------------->

# stores a mysql result
class DBResult{
	var $DBResult;
	function __construct($newResult)
	{
		$this->DBResult = $newResult;
	}
	function numRows()
	{
		return $this->DBResult->num_rows;
	}
	function nextRow()
	{
		return $this->DBResult->fetch_assoc();
	}
	function rowExists()
	{
		if (!$this->numRows())
			return false;
		return true;
	}
	function free(){
		$this->DBResult->free();
	}
	
}
//Code from db.php - End