<?php
class ErrorController extends BaseController {

	public function __construct(){
		call_user_func_array(array(parent, "__construct"), func_get_args());
	}

	public function error404(){
		header("HTTP/1.0 404 Not Found");

		$this->renderizeView();
	}
}