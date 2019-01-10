<?php
namespace dsvdb;
require_once __DIR__ . '/../autoload.php';
require_once 'Table.php';

class Operator {
	function __construct($ID, string $password) {
		/* Set up operator variables */
		$this->password 	= $password;
		$this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
		$this->operatorID 	= $ID;
		
		/* Retrieve information from operators file */
		$Op = new Table('local', 'operators', 'operator');
		$this->data			= $Op->getRowByCol($ID, 'ID');
	}
	
	public function authenticate() {
		return password_verify($this->password, $this->data['PASSWORD']);
	}
	
	public function view() {
		print_r($this);
	}
}
?>