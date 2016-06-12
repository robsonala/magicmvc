<?php
/**
 * Abstract class for models
 *
 * @author Robson Alviani
 * @version 1.1
 */

abstract class BaseModel {
	protected $db;
	protected $table;
	protected $columns = array();
	protected $fks = array();
	protected $allSQL;
	protected $columnKey = 'id';

	public $params = array();

	public function __construct($attributes = array(), $db = null){
		$this->db = $db ? $db : new DB();

		if (count($attributes) > 0){
			foreach ($attributes as $k=>$v)
				$this->params[$k] = $v;
		}

		$this->allSQL = sprintf("SELECT SQL_CALC_FOUND_ROWS %s FROM %s WHERE 1=1", 
			implode(',', $this->columns), $this->table);
	}

	public function __set($name, $value){
		$this->params[$name] = $value;
	}
	public function __get($name){
		return array_key_exists($name, $this->params) ? $this->params[$name] : null;
	}
	public function __isset($name){
		return isset($this->params[$name]);
	}
	public function __unset($name){
		unset($this->params[$name]);
	}

	public function all($page = null, $tpp = null, $search = '', $hasActiveColumn = true){
		if (!$page)
			$page = 0;
		if (!$tpp)
			$tpp = 50;

		$ini = ($page * $tpp) - $tpp;

		try {
			$sql = $this->allSQL;

			if ($hasActiveColumn)
				$sql.= " AND active=1 ";

			if ($search)
				$sql.= $search;

			if ($page > 0)
				$sql.= sprintf(" LIMIT %d, %d", $ini, $tpp);

			$query = $this->db->execute($sql);
		
			if ($query->num_rows){
				$items = array();

				while ($row = $query->fetch_assoc())
					$items[] = (object)$row;

				try {
					$query = $this->db->execute("SELECT FOUND_ROWS() AS TTL;");

					$row = $query->fetch_assoc();

					return array(
							'totalItems' => (int)$row['TTL'],
							'totalPages' => $row['TTL'] && $tpp ? ceil($row['TTL']/$tpp) : 1,
							'items' => $items
						);

				} catch (Exception $e) {
					throw $e;
				}
			} else {
				throw new ResourceNotFoundException();
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function one($id = null){
		if (is_null($id)){
			if (!isset($this->{$this->columnKey}))
				throw new ResourceNotFoundException();

			$id = $this->{$this->columnKey};
		}

		try {
			$sql = sprintf("SELECT %s FROM %s WHERE %s=%s", 
				implode(',', $this->columns),
				$this->table,
				$this->columnKey,
				(gettype($id) == "integer" ? $id : "'{$id}'"));

			$query = $this->db->execute($sql);
		
			if ($query->num_rows)
				return (object)$query->fetch_assoc();
			else 
				throw new ResourceNotFoundException();
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function insert(){
		if (count($this->params) == 0)
			throw new Exception(L::columnsnotfound);

		// CHANGE FKs WITHOUT VALUE TO NULL
		if (count($this->fks) > 0){
			foreach ($this->fks as $v){
				if (isset($this->params[$v]) && gettype($this->params[$v]) == "integer" && $this->params[$v] <= 0)
					$this->params[$v] = null;
			}
		}

		$sql = '';
		try {
			$keys = $values = '';
			foreach ($this->params as $k=>$v){
				$keys.= $k . ',';

				if (is_null($v))
					$values.= 'null,';
				elseif (gettype($v) == "integer")
					$values.= $v.',';
				else
					$values.= '\'' . $this->db->real_escape_string($v) . '\',';
			}
			$keys = trim($keys, ',');
			$values = trim($values, ',');

			$sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", 
				$this->table, $keys, $values);

			$ret = $this->db->execute($sql);

			if ($this->db->insert_id)
				$this->params[$this->columnKey] = $this->db->insert_id;

			return true;
		} catch (Exception $e) {
			throw new Exception($e->getMessage() . ' - SQL: ' . ($sql?:''), $e->getCode(), $e);
		}
	}

	public function update($id){
		if (count($this->params) == 0)
			throw new Exception(L::columnsnotfound);

		// CHANGE FKs WITHOUT VALUE TO NULL
		if (count($this->fks) > 0){
			foreach ($this->fks as $v){
				if (isset($this->params[$v]) && gettype($this->params[$v]) == "integer" && $this->params[$v] <= 0)
					$this->params[$v] = null;
			}
		}

		$sql = '';
		try {
			$values = '';

			foreach ($this->params as $k=>$v){
				$values.= $k . '=';

				if (is_null($v))
					$values.= 'null,';
				elseif (gettype($v) == "integer")
					$values.= $v.',';
				else
					$values.= '\'' . $this->db->real_escape_string($v) . '\',';
			}

			$values = trim($values, ',');

			$sql = sprintf("UPDATE %s SET %s WHERE %s=%s", 
				$this->table,
				$values,
				$this->columnKey,
				(gettype($id) == "integer" ? $id : "'{$id}'"));

			$ret = $this->db->execute($sql);

			$this->params[$this->columnKey] = $id;

			return true;
		} catch (Exception $e) {
			throw new Exception($e->getMessage() . ' - SQL: ' . ($sql?:''), $e->getCode(), $e);
		}
	}

	public function delete($id){
		try {
			$sql = sprintf("UPDATE %s SET active=0 WHERE %s=%s", 
				$this->table,
				$this->columnKey,
				(gettype($id) == "integer" ? $id : "'{$id}'"));

			$ret = $this->db->execute($sql);

			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}
}