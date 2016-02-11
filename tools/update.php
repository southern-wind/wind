#!/usr/bin/env php
<?php 
/*

Updating schema requires edits in the following files:
1. Update schema.sql for change. This should reflect the latest version of the schema.  The last line should log the updated version (say x.y).
2. Create a module in /updates/schema-vx.y.inc.php containing code to update the schema from the previous version.
3. Edit this file (update.php) to include reference to that file in $updates array
3. Update the version in updateTo below to reflect x,y.

*/


if (!php_sapi_name() == 'cli')
	die('This is a command line only script.');

require_once dirname(__FILE__) . "/../globals/functions.php";
require_once dirname(__FILE__) . "/../globals/classes/DBUpdater.class.php";

// Load configuration
require dirname(__FILE__) . "/../config/config.php";

try {
	// Start-up updater
	$updater = new DBUpdater(
		$config['db']['server'],
		$config['db']['username'],
		$config['db']['password'],
		$config['db']['database']);
	
	$updates = array(
                include dirname(__FILE__) . "/updates/schema-v1.1.inc.php",
                include dirname(__FILE__) . "/updates/schema-v1.2.inc.php",
	        );

	$updater->updateTo(new SchemaVersion(1, 2), $updates);
} catch (Exception $e){
	die($e->getMessage() . "\n");
} 
