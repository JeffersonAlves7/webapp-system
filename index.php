<?php
session_start();
require_once "show-error.php";

$url = isset($_GET['url']) ? $_GET['url'] : 'home'; // Página inicial por padrão

$urlParts = explode('/', $url);

$controllerName = ucfirst(array_shift($urlParts)) . 'Controller';

$actionName = !empty($urlParts) ? array_shift($urlParts) : 'index';
$params = $urlParts;

$controllerFile = "./Controllers/$controllerName.php";
if (file_exists($controllerFile)) {
    require_once $controllerFile;

    $controller = new $controllerName();

    if (method_exists($controller, $actionName)) {
        call_user_func_array([$controller, $actionName], $params);
    } else {
        echo "404 - Página não encontrada";
    }
} else {
    echo "404 - Página não encontrada";
}
