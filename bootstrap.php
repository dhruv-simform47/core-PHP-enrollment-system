<?php
use Dotenv\Dotenv;

require_once __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
// error_reporting(E_ALL);
// ini_set('display_errors',1);
?>