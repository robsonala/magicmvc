<?php

/**
 * Class responsible for connection with the database
 *
 * @author Robson Alviani
 * @version 1.2
 */

class DB extends mysqli {
	
	public function __construct() {
		parent::__construct(BD_HOST, BD_USER, BD_PASS, BD_BASE, BD_PORT);
		
		if (mysqli_connect_error())
			die('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());

		self::execute("SET NAMES utf8;");
	}
	
	public function __destruct() {
		mysqli::close();
	}

	/**
	 * Execute a script SQL and return the affected lines
	 * @param string	$str 	SQL Script
	 * @return array default with affected lines
	 * @author Robson Alviani
	 * @access public
	 */	
	public function execute($strSQL) {
		try {
			$query = self::query($strSQL);
			
			if (!$this->error)
				return $query;
			else
				throw new Exception($this->error);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
}