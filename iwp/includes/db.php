<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

#class providing access to the mysql database

class DB{
	
	private static $queryString;
	private static $printQuery;
	private static $printAllQuery;
	private static $showError;
	private static $DBDriver;
	private static $showSQL;
	private static $DBResultClass;
	private static $tableNamePrefix;
	private static $printErrorOnFailure = true;
	
	public static function connect($host, $username, $password, $database, $port){
		if(class_exists('mysqli')){
			$driver = 'mysqli';
		}
		elseif(function_exists('mysql_connect')){
			$driver = 'mysql';
		}
		else{
			die('PHP has no mysql extension installed.');	
		}

		require_once(APP_ROOT.'/includes/dbDrivers/'.$driver.'.php');
		
		$DBClass = 'DB'.ucfirst($driver);
		self::$DBResultClass = $DBClass.'Result';
		
		self::$DBDriver = new $DBClass($host, $username, $password, $database, $port);
		$isConnected = self::$DBDriver->connect();
		self::$showSQL = false;
		self::$showError = $GLOBALS['mysqlShowManualError'];
		return $isConnected;
	}

	public static function setTableNamePrefix($tableNamePrefix){
		self::$tableNamePrefix = $tableNamePrefix;
	}

	public static function setPrintErrorOnFailure($isPrint){
		self::$printErrorOnFailure = $isPrint;
	}
	
	private static function get($params, $type){
		if(empty($params)){
			return false;
		} 
		
		$result = array();		
		$query = self::prepareQ('select', $params);
		
		$query_result = self::doQuery($query);	
		if(!$query_result){
			return $query_result;	
		} 
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
		$is_plain_query = !empty($params) && count($params) == 1;
		if($is_plain_query){
			$table = $params[0];
			return $table;
		}

		$table = $params[0];
		if ($type == 'delete') {
			$params[2] = $params[1];
			unset($params[1]);
		}
		$attributes = self::buildAttributes($params[1]); //for updates
		$where = self::santizeInputs($params[2]);

		if($type == 'select'){
			return "SELECT ".$attributes." FROM ".$table." WHERE ".$where;
		}elseif($type == 'insert'){
			return "INSERT INTO ".$table." SET ".$attributes;
		}elseif($type == 'replace'){
			return "REPLACE INTO ".$table." SET ".$attributes;
		}elseif($type == 'update'){
			return "UPDATE ".$table." SET ".$attributes." WHERE ".$where;
		}elseif($type == 'delete'){
			return "DELETE FROM ".$table." WHERE ".$where;
		}
	}
        
	private static function santizeInputs($where){
		if(is_array($where)){               
			$query = $where['query'];
			$params = $where['params'];

			$find = array_keys($params);
			$replace = array();

			foreach (array_values($params) as $value) {
			    array_push($replace, DB::realEscapeString($value));
			}
			$safe_where = str_replace($find,$replace, $query);
			return $safe_where;
		}
		return $where;
	}

	private static function buildAttributes($attributes){
		if(is_array($attributes)){
			return self::array2MysqlSet($attributes);
		}
		return $attributes;
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
	
		$queryString = str_replace('?:', self::$tableNamePrefix, $queryString);
		
		self::$queryString = $queryString;
		
		if(self::$printAllQuery || self::$printQuery)
			echo '<br>'.self::$queryString.'<br>';

		$query = self::$DBDriver->query(self::$queryString);

		if($query){
			return $query;
		}
		else
		{
			if(self::$printErrorOnFailure){
				self::printError(debug_backtrace());
				echo "\n".self::$queryString."\n<br>";
			}
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

	public static function connectErrorNo(){
		return self::$DBDriver->connectErrorNo();
	}
	
	public static function connectError(){
		return self::$DBDriver->connectError();
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
		$mysqlSet = '';
            $isPrev = false;
            foreach($array as $key => $value){
                if($isPrev){
                    $mysqlSet .= ', ';
                }
                if(isset($value) && is_array($value)){
                    $mysqlSet .= $key." = ".self::realEscapeString($value[0]).""; //without quotes                    
                }else{
                    $mysqlSet .= $key." = '".self::realEscapeString($value)."'";                    
                }
                $isPrev = true;
            }
            return $mysqlSet;
	}
	
	private static function array2MysqlSelect($array){
		 $mysqlSet='';
            $isPrev=false;
            foreach($array as $key => $value){
                if($isPrev){
                    $mysqlSet .= ', ';
                }
                $mysqlSet .= $value;
                $isPrev = true;
            }
            return $mysqlSet;
	}
	
	public static function setPrintQuery($var){
		self::$printQuery = $var;
	}

	public static function version(){
		$serverInfo = self::$DBDriver->getServerInfo();
		return preg_replace( '/[^0-9.].*/', '', $serverInfo );
	}

	public static function hasCap( $DBCap ) {
		$version = DB::version();

		switch ( strtolower( $DBCap ) ) {
			case 'collation' :    // @since 2.5.0
			case 'groupConcat' : // @since 2.7.0
			case 'subqueries' :   // @since 2.7.0
				return version_compare( $version, '4.1', '>=' );
			case 'setCharset' :
				return version_compare( $version, '5.0.7', '>=' );
			case 'utf8mb4' :      // @since 4.1.0
				if ( version_compare( $version, '5.5.3', '<' ) ) {
					return false;
				}

				$clientVersion = self::$DBDriver->getClientInfo();

				/*
				 * libmysql has supported utf8mb4 since 5.5.3, same as the MySQL server.
				 * mysqlnd has supported utf8mb4 since 5.0.9.
				 */
				if ( false !== strpos( $clientVersion, 'mysqlnd' ) ) {
					$clientVersion = preg_replace( '/^\D+([\d.]+).*/', '$1', $clientVersion );
					return version_compare( $clientVersion, '5.0.9', '>=' );
				} else {
					return version_compare( $clientVersion, '5.5.3', '>=' );
				}
		}

		return false;
	}
	
	public static function getSQLCharSetCollation(){
		if(self::hasCap('utf8mb4')){
			$charSetCollation = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		} else {
			$charSetCollation = 'CHARACTER SET utf8 COLLATE utf8_general_ci';
		}	
		return $charSetCollation;
	}
	
	public static function getSQLTableEnv(){
		$charset = 'DEFAULT CHARSET = ';
		if(self::hasCap('utf8mb4')){
			$charset .= 'utf8mb4 COLLATE utf8mb4_unicode_ci';
		}
		else{
			$charset .= 'utf8 COLLATE utf8_general_ci';
		}	
		$tableEnv = 'ENGINE=InnoDB '.$charset;
		return $tableEnv;
	}
	
	public static function esc($data){
		if(is_array($data)){
			foreach($data as $key => $value){
				$data[$key] = self::esc($data[$key]);
			}
		}elseif(is_string($data)){
			$data = DB::realEscapeString($data);
		}
		return $data;	    
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
