<?php
use Dotenv\Dotenv;

require_once __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
// error_reporting(E_ALL);
// ini_set('display_errors',1);

spl_autoload_register(function($className){
    $path="./models/".$className.".php";
    if(file_exists($path))
    {
        require_once $path;
    }
})
?>