<?php
namespace dsvdb;
require_once __DIR__ . '/../autoload.php';

class Parser {
	/* 
	 * Set up data.
	 */
	function __construct(string $pathname, string $schema) {
		$this->pathname 		= $pathname;
		$this->schemaName		= $schema;
		$this->schema			= null;
		$this->contents 		= null;
		$this->byRow			= null;
		$this->contentsExploded = null;
		$this->byCol			= null;
	}
	
	/*
	 * Parse a DSVDB file.
	 * @return array with DSV data.
	 */
	public function parse() {
		$fileContents = '';
		
		/* 
		 * If the file exists (i.e. the table exists, then attempt to parse.
		 * Furthermore, ensure that file size is less than maximum size specified
		 * in configuration file.
		 */
		if ( file_exists($this->pathname) && filesize($this->pathname) < DSVDB_TABLE_MAXSIZE ) {
			try { 
				$fileContents = file($this->pathname); /* Read full file into array */
				$this->contents = $fileContents;	/* Write over class variables while still intact */
			} catch ( Exception $e ) {
				trigger_error(DSVDB_MESSAGES['parser_fileCorrupt'], E_USER_ERROR);
			}
			
		/* Should the file not exist, return a table DNE error. */
		} else trigger_error(DSVDB_MESSAGES['parser_fileDNE'], E_USER_ERROR);
		
		$this->contentsExploded = $fileContents;
		$this->schema = explode(',', DSVDB_SCHEMAS[$this->schemaName]); /* Split schema by comma */
		
		/* Phase Two: parse the given contents according to the predefined schema. */
		if ( $this->isValidSchema() && $this->isValidFile() ) {
			
			/* Next, parse data into a row format (byRow) and a columnar format (byCol).
			 * Loop over the entire file, then over the schema file, assigning the proper
			 * schema ID to each column.
			 */
			for ( $i = 0; $i < count($this->contents); $i++ ) {
				$this->contentsExploded[$i] = explode(DSVDB_DELIMETER, $this->contents[$i]);
				$j = 0; /* j is the horizontal index. Reset every loop. */
				
				foreach ( $this->schema as $colID ) {
					$this->byRow[$i][$colID] = trim($this->contentsExploded[$i][$j]); /* Currently, due to a bug, use trim() */ 
					$this->byCol[$colID][$i] = trim($this->contentsExploded[$i][$j]); /* Insert into columnar format */
					$j++;
				}
			}
		} else trigger_error(DSVDB_MESSAGES['parser_invalidSchema'], E_USER_NOTICE);
		
		/* Destroy unneeded class variables */
		unset($this->contents, $this->contentsExploded, $fileContents, $this->pathname);
		
		/* Return an array with the data stored in columnar and row format. */
		return $this;
	}
	
	/*
	 * @function isValidFile
	 * @description Runs some basic checks to ensure integrity of the file. Currently,
	 *				it only checks to see if each line has the proper number of unit
	 *				breaks. Uses $this->contentsExploded...etc...
	 * @return bool
	 */
	private function isValidFile() {
		$schemaHDE = count($this->schema); /* Number of HDE's expected from schema */
		
		/* Ensure that the size of each line is equal to size of the schema less one. The
		 * way this is designed, it just immediately fails when at least one line
		 * is wrong.
  		 */
		foreach ( $this->contents as $line ) {
			if ( substr_count($line, '^_') != ($schemaHDE - 1) ) {
				trigger_error(DSVDB_MESSAGES['parser_invalidFile'], E_USER_ERROR);
				return false;
			}
		}
		
		return true;
	}	
	
	/*
	 * @function isValidSchema
	 * @description Essentially, this function just checks to see if schema name exists
	 *				as a key in the DSVDB_schemas global.
	 * @param $schemaName - Name of schema as found in global constant DSVDB_SCHEMAS.
	 * @return bool
	 */
	private function isValidSchema() {
		return array_key_exists($this->schemaName, DSVDB_SCHEMAS) ? true : false;
	}
}
?>