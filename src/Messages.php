<?php
const DSVDB_MESSAGES = [
	'parser_fileDNE'		=> '|A001| - File does not exist or exceeds maximum table filesize specified in config file',
	'parser_fileCorrupt' 	=> '|A002| - Unable to parse file',
	'parser_invalidFile'	=> '|A004| - File and provided schema are incompatible',
	'parser_invalidSchema'	=> '|A003| - Nonexistant or invalid schema used while parsing file',
	'request_invalidAuth'	=> '|A010| - Authentication credentials provided were incorrect or invalid',
	'request_tooFewParams'  => '|A009| - Too few parameters provided for request',
	'table_byColFail'		=> '|A006| - An error ocurred while trying to update table by column',
	'table_byRowFail'		=> '|A007| - An error ocurred while trying to update table by row',
	'table_indexTooLarge' 	=> '|A005| - Index too large to insert',
	'table_uniqueUsed'		=> '|A008| - Attempting to insert non-unique row.'
	];
?>