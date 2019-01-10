<?php
namespace dsvdb;

require_once 'autoload.php';
require_once './src/Table.php';
require_once './src/Database.php';
require_once './src/Operator.php';

$operator = new Operator('1000', '1234');
$operator->authenticate();

$Table = new Table('local', 'test', 'std');
#$Table->insertRowByCol(['ID' => 'a', 'OP' => 2, 'NEXT' => 3], 'ID');
#$Table->deleteRowByCol('a', 'ID');
print_r($Table->getRowsByCol('a', 'ID'));
$Table->view();
#$Table->update();
?>