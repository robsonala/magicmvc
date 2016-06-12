<?php
class HomeController extends BaseController {

	public function __construct(){
		call_user_func_array(array(parent, "__construct"), func_get_args());
	}

	public function index(){
		$this->renderizeView();
	}
}