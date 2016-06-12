<?php
/**
 * Abstract class for controllers
 *
 * @author Robson Alviani
 * @version 1.1
 */

abstract class BaseController {
	protected $model;
	protected $controller;
	protected $action;

	public function __construct($controller, $action){
		$this->controller = $controller;
		$this->action = $action;
	}
	
	public function getController(){
		return strtolower($this->controller);
	}

	public function getAction(){
		return strtolower($this->action);
	}

	protected function paramRequest($name, $type){
		$value = $_REQUEST[$name];

		if ($value === null)
			return null;

		switch ($type){
			case "boolean":
				return (bool)$value;
				break;
			case "integer":
				return (int)$value;
				break;
			case "double":
				return (double)$value;
				break;
			case "string":
				return filter_var($value, FILTER_SANITIZE_STRING);
				break;
			case "date":
				if (!$value)
					$value = null;
				else if (date('Y-m-d', strtotime($value)) != $value)
					throw new Exception("InvalidDate");

				return $value;
				break;
			default:
				return $value;
				break;
		}
	}

	protected function renderizeView(){
		require_once (ROOT_APP . DS . 'views' . DS . $this->getController() . DS . $this->getAction() . '.phtml');
	}
}