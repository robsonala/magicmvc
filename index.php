<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_APP', dirname(__FILE__));

require_once(ROOT_APP . DS . 'bootstrap.php');

list($controllerRouter, $actionRouter) = explode('/', trim($_GET['router_url']), 2);

if (!$controllerRouter)
	$controllerRouter = "Home";
if (!$actionRouter)
	$actionRouter = "index";

$controllerRouter = ucfirst(strtolower($controllerRouter));
$actionRouter = trim(str_replace("-", "_", strtolower($actionRouter)), "/");

if (!method_exists($controllerRouter . 'Controller', $actionRouter)){
	$controllerRouter = "Error";
	$actionRouter = "error404";
}

$aux = $controllerRouter . 'Controller';
$obj = new $aux($controllerRouter, $actionRouter);
$obj->$actionRouter();