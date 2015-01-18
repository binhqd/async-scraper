<?php
require ("bootstrap.php");

$round = app()->request->get('round');

$controllerFile = CONTROLLER_PATH . "{$round}.php";

if (file_exists($controllerFile)) {
    require_once ($controllerFile);
}