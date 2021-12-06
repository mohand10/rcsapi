<?php
declare(strict_types=1);

require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo $_ENV['KEY'] . "\n";
//echo $_SERVER['NAME'] . "\n";

