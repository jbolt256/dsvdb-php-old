<?php
namespace dsvdb;
require_once __DIR__ . '/../autoload.php';

class Database {
	/* Defines a database, either existing or new */
	function __construct(string $databaseName) {
		$this->name = $databaseName;
		$this->path = DSVDB_DATABASE_DIR . '/' . $databaseName;
		$this->create(); 		/* Not sure if this is wise, but automatically check to create database */
		$this->tables = $this->listTables();
	}
	
	/* 
	 * @function create
	 * If the databsae does not already exist, then create it.
	 */
	private function create() {
		if ( !is_dir($this->path) ) {
			mkdir($this->path);
		}
	}
	
	/*
	 * @function destroy
	 * If the database exists, destroy it.
	 */
	public function destroy() {
		if ( !is_dir($this->path) ) {
			system("del " . $this->path);
			#rmdir($this->path);
		}
	}
	
	/*
	 * @function createTable
	 */
	public function createTable(string $tableName, string $subcat = '') {
		#&& count(scandir($this->path - 2)) < DSVDB_DATABASE_MAXTABLES 
		if ( !file_exists($this->path . '/' . $subcat .  "{$tableName}.db.dsv") ) {
			file_put_contents($this->path . '/' . $subcat . "{$tableName}.db.dsv", "");
		}
		$this->tables = $this->listTables(); /* Update table list, this barely matters */
	}
	
	/*
	 * @function destroyTable
	 */
	public function destroyTable(string $tableName, string $subcat = '') {
		if ( file_exists($this->path . '/' . $subcat . "{$tableName}.db.dsv") ) {
			unlink($this->path . '/' . $subcat . "{$tableName}.db.dsv");
		}
	}
	
	/* 
	 * @function tableExists
	 */
	public function tableExists(string $tableName, string $subcat = '') {
		return file_exists($this->path . '/' . $subcat . "{$tableName}.db.csv");
	}
	
	/*
	 * @function createSub
	 * Creates a new subcategory if it does not already exist.
	 */
	public function createSub(string $sub) {
		if ( !is_dir($this->path . '/' . $subcat) ) {
			mkdir($this->path . '/' . $subcat);
		}
	}
	
	/*
	 * @function destroySub
	 * Destroys a subcategory if it already exists.
	 */
	public function destroySub(string $sub) {
		if ( is_dir($this->path . '/' . $subcat) ) {
			system("del " . $this->path . '/' . $subcat);
			#rmdir($this->path . '/' . $subcat);
		}
	}
	
	/*
	 * @function listTables
	 */
	public function listTables() {
		$tables = [];
		
		/* Use a slightly outdated directory reading mechanism... */
		$handle = opendir($this->path);
		while ($entry = readdir($handle)) {
			($entry != "." && $entry != "..") ? array_push($tables, $entry) : print ''; /* Do nothing if entry is '.' or '..' */
		}
		closedir($handle);
		
		return $tables;
	}
	
	/*
	 * @function view
	 */
	public function view() {
		print_r($this);
	}
}
?>