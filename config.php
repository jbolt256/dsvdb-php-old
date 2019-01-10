<?php
define('DSVDB_DATABASE_DIR', __DIR__ . '/db');
define('DSVDB_DATABASE_MAXTABLES', 1024);
define('DSVDB_DEBUG', true);
define('DSVDB_DELIMETER', '^_');
define('DSVDB_SCHEMAS', [
	'std' => 'ID,OP,NEXT',
	'operator' => 'ID,NAME,PASSWORD,CREATED,STORES'
	]);
define('DSVDB_TABLE_MAXSIZE', 2048000);
?>