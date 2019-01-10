<?php
namespace dsvdb;
require_once __DIR__ . '/../autoload.php';
require_once 'Parser.php';

class Table {
	/*
	 * Table is an OO-based class. When constructing the class, you are constructing
	 * a table from a certain database. All data in the table will be stored in
	 * the Table->data array (i.e. Table->data->byRow).
	 */
	function __construct(string $database, string $table, string $schema, string $subcat = '') {
		$this->pathname 	= DSVDB_DATABASE_DIR . '/' . $database . '/' . $subcat . $table . '.db.dsv';		
		$this->database 	= $database;
		$this->table 		= $table;
		$this->schemaName	= $schema;
		$this->subcat		= $subcat;
		
		/* Parse */
		$Parser = new Parser($this->pathname, $this->schemaName);
		$this->data = $Parser->Parse();
	}
	
	/* 
	 * @function columnExists
	 */
	public function columnExists(string $column) {
		return isset($this->data->byCol[$column]);
	}
	
	/* 
	 * @function deleteRowByCol
	 * Note that this function does NOT require an unique search value. It can delete
	 * as many rows have that value in said column as required.
	 */
	public function deleteRowByCol(string $value, string $column) {
		$pos = array_keys($this->data->byCol[$column], $value);
		$values = 0;
		/* Since each time a row is deleted, the remaining rows are reordered. Thus it is 
		 * necessary to adjust the index of the next row being deleted accordingly.
		 */
		foreach ( $pos as $key => $val ) {
			$val = $val - $values;
			$this->deleteRowByIndex($val);
			$values++;
		}
	}
	
	/*
	 * @function deleteRowByIndex
	 */
	public function deleteRowByIndex(int $index) {
		/* DEVWARN: add check to ensure existence of row, validity of schema? */
		unset($this->data->byRow[$index]);
		$this->data->byRow = array_values($this->data->byRow);
		
		foreach ( $this->data->schema as $colID ) {
			unset($this->data->byCol[$colID][$index]);
			$this->data->byCol[$colID] = array_values($this->data->byCol[$colID]);
		}
	}
	
	/* 
	 * @function insertRowByCol
	 */
	public function insertRowByCol(array $data, string $column) {
		$dummy = $this->data->byCol;
		
		/* Ensure that values are set, and all exist*/
		if ( isset($data[$column]) && isset($this->data->byCol[$column]) ) {
			$dataColVal = $data[$column];
			$dummy[$column][count($dummy)] = $dataColVal; 		/* Insert data to end of row */
			asort($dummy[$column], SORT_REGULAR);				/* Sort column by values */
			$dummy[$column] = array_values($dummy[$column]);	/* Re-order indices */
		} else trigger_error(DSVDB_MESSAGES['table_f']);
		
		/* Find the instance that we just inserted after being resorted. Obtain
		 * the index, then use the other class function to insert the value into
		 * the desired row.
		 */
		$index = array_search($dataColVal, $dummy[$column]);
		$this->insertRowByIndex($data, $index);
		unset($dummy);
	}
	
	/*
	 * @function insertRowByIndex
	 */
	public function insertRowByIndex(array $data, int $index) {
		$sizeof = count($this->data->byRow);
		
		/* Ensure that index is not out of bounds and that $data is complete*/
		if ( $index <= $sizeof + 1 && count($data) === count($this->data->byRow[0]) ) {

			/* Variables $i, $j, and $dummy are used more than once in this
			 * block of code.
			 */
			/* Insert into byRow by reordering all rows */
			try { 
				$dummy = $this->data->byRow;
				for ( $i = $index; $i < $sizeof - $index; $i++ ) {
					$j = $i + 1;
					$dummy[$j] = $this->data->byRow[$i];
				}
				$this->data->byRow = $dummy;
			} catch ( Exception $e ) {
				trigger_error(DSVDB_MESSAGES['table_byRowFail']);
			}

			/* Next, insert into byCol */
			try { 
				$dummy = $this->data->byCol;
				for ( $k = 0; $k < count($this->data->schema); $k++ ) {
					/* Iterate over each column */
					for ( $i = 0; $i < $sizeof; $i++ ) {
						$j = $i + 1;
						$dummy[$this->data->schema[$k]][$j] =$this->data->byCol[$this->data->schema[$k]][$i];
					}
					
					/* Insert data from array into table */
					$dummy[$this->data->schema[$k]][$index] = $data[$this->data->schema[$k]];
					$this->data->byCol = $dummy;
				}
			} catch ( Exception $e ) {
				trigger_error(DSVDB_MESSAGES['table_byColFail'], E_USER_ERROR);
			}
							
			/* Insert information from dummy variable back to class variable */
			$this->data->byRow[$index] = $data;
		} else trigger_error(DSVDB_MESSAGES['table_indexTooLarge'], E_USER_NOTICE);
	}
	
	/*
	 * @function insertRowUnique
	 * Only adds row if no other row has the same value in a given column. Also,
	 * it inserts the value in a sorted manner.
	 */
	public function insertRowUnique(array $data, string $column) {
		if ( !$this->rowExistsByCol($data[$column], $column) ) {
			$this->insertRowByCol($data, $column);
		} else trigger_error(DSVDB_MESSAGES['table_uniqueUsed'], E_USER_NOTICE);
	}
	
	/*
	 * @function getRowByCol
	 * Returns -1 if row DNE.
	 */
	public function getRowByCol(string $value, string $column) {
		return $this->rowExistsByCol($value, $column) ? $this->data->byRow[array_search($value, array_column($this->data->byRow, $column))] : -1;
	}
	
	/* 
	 * @function getRowsByCol
	 * Returns -1 if row DNE.
	 */
	public function getRowsByCol(string $value, string $column) {
		$rows = [];
		
		/* If the row exists, get all ocurrences of searched for string */
		if ( $this->rowExistsByCol($value, $column) ) {
			$pos = array_keys($this->data->byCol[$column], $value);
			/* Obtain location of each key */
			foreach ( $pos as $key => $loc ) {
				$rows[$loc] = $this->data->byRow[$loc];
			}
			return $rows;
		} else return -1;
	}
	
	/*
	 * @function getRowByIndex
	 * Returns -1 if row DNE.
	 */
	public function getRowByIndex(int $index) {
		return $this->rowExistsByIndex($index) ? $this->data->byRow[$index] : -1;
	}
	
	/*
	 * @function rowExistsByCol
	 */
	public function rowExistsByCol(string $value, string $column) {
		$pos = array_search($value, array_column($this->data->byRow, $column));
		return $pos === false ? false : isset($this->data->byRow[$pos]);
	}
	
	/* 
	 * @function rowExistsByIndex
	 */
	public function rowExistsByIndex(int $row) {
		return isset($this->data->byRow[$row]);
	}

	/*
	 * @function update 
	 * Push current table instance to table file.
	 */
	public function update() {
		$buffer = '';
		$tsizeof = count($this->data->byRow); /* For optimization, count tablesize once */
		
		/* For each row, gather data, then parse according to specified delimeter */
		foreach ( $this->data->byRow as $rowNum => $rowData ) {
			$i = 0;
			foreach ( $rowData as $col => $data ) {
				$i++;
				$buffer = (count($rowData) == $i) ? $buffer . $data : $buffer . $data . DSVDB_DELIMETER; /* Prevent additional unit separators */
			}
			$buffer = ($tsizeof - 1 != $rowNum ) ? $buffer . "\r\n" : $buffer; /* Prevent an additional blank \r\n line */
		}
		
		/* Write to file and destroy buffer */
		file_put_contents($this->pathname, $buffer);
		unset($buffer);
	}
	
	/*
	 * @function view
	 * @description print_r's the table instance.
	 * @param N/A
	 * @return void
	 */
	public function view() {
		print_r($this);
	}
}
?>