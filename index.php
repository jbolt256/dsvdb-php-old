<?php
namespace dsvdb;

require_once 'autoload.php';
require_once './src/Request.php';

$Request = new RequestMulti($_POST);
$Request->cycle();
?>