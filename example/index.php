<?php
require_once ('../vendor/autoload.php');
use Metagrid\Redirect;
$redirecter = new Redirect(__DIR__);
$redirecter->handleRequest($_SERVER);
