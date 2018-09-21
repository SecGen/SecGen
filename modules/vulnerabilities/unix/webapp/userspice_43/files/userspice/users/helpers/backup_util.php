<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//bold("<br><br>custom helpers included");

function csv_to_array($filename='', $delimiter=','){
	/**
 * Convert a comma separated file into an associated array.
 * The first row should contain the array keys.
 *
 * Example:
 *
 * @param string $filename Path to the CSV file
 * @param string $delimiter The separator used in the file
 * @return array
 * @link http://gist.github.com/385876
 * @author Jay Williams <http://myd3.com/>
 * @copyright Copyright (c) 2010, Jay Williams
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

	if(!file_exists($filename) || !is_readable($filename))
		return FALSE;

	$header = NULL;
	$data = array();
	if (($handle = fopen($filename, 'r')) !== FALSE)
	{
		while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
		{
			if(!$header)
				$header = $row;
			else
				$data[] = array_combine($header, $row);
		}
		fclose($handle);
	}
	return $data;

	//Example
	//print_r(csv_to_array('example.csv'));
}

function recurse_copy($src,$dst) {
	/*
	FROM http://php.net/manual/en/function.copy.php
	*/
	global $settings;
	$dest = '@'.rtrim($settings->backup_dest, '/');
    $dir = opendir($src);
    @mkdir($dst,0755,true);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..') && ($file != $dest)) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function zipData($source, $destination) {
	/*
	 * PHP: Recursively Backup Files & Folders to ZIP-File
	 * (c) 2012-2014: Marvin Menzerath - http://menzerath.eu
	 * From https://gist.github.com/MarvinMenzerath/4185113
	*/

	// Make sure the script can handle large folders/files
	//ini_set('max_execution_time', 600);
	//ini_set('memory_limit','1024M');
	// Start the backup!
	//zipData('/path/to/folder', '/path/to/backup.zip');
	global $successes,$errors;

	if (file_exists($source)) {
		$zip = new ZipArchive();
		if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
			$source = realpath($source);
			if (is_dir($source)) {

				//:: from 4.2.9a
				//:: lets exclude files prepended with @
				class BackupDirFilter extends RecursiveFilterIterator {
				    public function accept() {

				        return '@' !== substr($this->current()->getFilename(), 0, 1);

				    }
				}

				//:: from 4.2.9a
				$iterator = new RecursiveDirectoryIterator($source);
				// skip dot files while iterating
				$iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
				$filter = new BackupDirFilter($iterator);
				//$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
				$files = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);

				//$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
				foreach ($files as $file) {

					$file = realpath($file);
					//Following are the original lines, replaced '/' with DIRECTORY_SEPARATOR to support linux and windows
					/*
					if (is_dir($file)) {
						$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
					} else if (is_file($file)) {
						$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
					}
					*/

					if (is_dir($file)) {
						$zip->addEmptyDir(str_replace($source . DIRECTORY_SEPARATOR, '', $file . DIRECTORY_SEPARATOR));
					} else if (is_file($file)) {
						$zip->addFromString(str_replace($source . DIRECTORY_SEPARATOR, '', $file), file_get_contents($file));
					}
				}
			} else if (is_file($source)) {
				$zip->addFromString(basename($source), file_get_contents($source));
			}
		}
		return $zip->close();
	}

	return false;
}

function rrmdir($src) {
	/*
	From http://php.net/manual/en/function.rmdir.php

	*/
	if($src==null){
		return false;
	}
	$dir = opendir($src);
	while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
					$full = $src . '/' . $file;
					if ( is_dir($full) ) {
							rrmdir($full);
					}
					else {
							unlink($full);
					}
			}
	}
	closedir($dir);
	rmdir($src);
	return true;
}

function delete_dir($dir)
{
	/*
	From: http://uk1.php.net/manual/en/function.copy.php#104020
	*/
	if (is_link($dir)) {
			unlink($dir);
	} elseif (!file_exists($dir)) {
			return;
	} elseif (is_dir($dir)) {
			foreach (scandir($dir) as $file) {
					if ($file != '.' && $file != '..') {
							delete_dir("$dir/$file");
					}
			}
			rmdir($dir);
	} elseif (is_file($dir)) {
			unlink($dir);
	} else {
			echo "WARNING: Cannot delete $dir (unknown file type)\n";
	}
}

function backupObjects($backupItems,$backupPath){
	/*
	Inputs:
	$backupItems (the array of all file and folder objects to be backed up)
	$backupPath (the directory to which files will be copied)
	*/

	global $errors,$successes;

	/*
	Cycle through items to backup
	If it is a file, then use copy()
	If it is a directory, then use utility.php:recurse_copy
	Else do nothing since unrecognized object
	*/
	foreach($backupItems as $backupItem){
		/*
		Build the target file name as $backupPath.end(explode('/',$backupItem))
		*/
		$pathArray = explode('/',$backupItem);
		$backupItemFilename = end($pathArray);
		$targetFilename = $backupPath.$backupItemFilename;

		if(is_file($backupItem)){
			copy($backupItem,$targetFilename);
			$successes[] = 'Copied file: '.$backupItem.' to '.$targetFilename;
		}elseif(is_dir($backupItem)){
			recurse_copy($backupItem,$targetFilename);
			$successes[] = 'Copied directory: '.$backupItem.' to '.$targetFilename;
		}else{
			/*
			Do nothing because it isn't a normal file or directory
			*/
			$errors[] = 'Encountered an object that was not a file or a directory: '.$targetFilename;
			return false;
		}
	}
	$successes[] = 'Backup completed successfully.';
	return true;
}

function backupZip($backupPath,$delBackupPath=false){
	global $errors,$successes;

	/*
	Add $backupPath to a zipfile named end(explode('/',$backupPath)).'.zip'
	*/

	$zipCreated = false;
	if (extension_loaded('zip')){
		/*
		$targetZipFile is the $backupPath minus the trailing slash, plus .zip
		*/
		$targetZipFile = substr($backupPath,0,strlen($backupPath)-1).'.zip';
		if(zipData($backupPath, $targetZipFile)){
			$successes[] = 'Successfully created '.$targetZipFile.' from '.$backupPath.'';
			$zipCreated=true;
		}
	}else{
		$errors[] = 'The "zip" PHP extension is not installed. Cannot create zip file.';
		$zipCreated=false;
		return false;
	}
	/*
	If $zipCreated=true, then recursively delete the backup directory.
	If $zipCreated=false, then leave folder and inform user to download folder if desired.
	*/
	if($zipCreated && $delBackupPath){
		/*
		$removePath is the same as $backupPath but without the trailing slash
		*/
		$removePath = substr($backupPath,0,strlen($backupPath)-1);
		rrmdir($removePath);
		$successes[] = 'The backup folder '.$removePath.' has been removed. Please download '.$targetZipFile.' directly.';
	}else{
		$successes[] = 'The backup folder '.$removePath.' has NOT been removed. Please downloaded folder directly.';
	}
	return $targetZipFile;
}

function backupUsTables($backupPath) {

	global $errors, $successes;

    try {

			$dbHost     = Config::get('mysql/host');
			$dbDatabase = Config::get('mysql/db');
			$dbUsername = Config::get('mysql/username');
			$dbPassword = Config::get('mysql/password');

			$usDump = Shuttle_Dumper::create(array(
					'host'     => $dbHost,
					'username' => $dbUsername,
					'password' => $dbPassword,
					'db_name'  => $dbDatabase
			));

			$usDumpFilename = $backupPath.'sql/'.$dbDatabase.'.sql';
			$usDump->dump($usDumpFilename);

			$successes[]='Tables have been backed up to '.$usDumpFilename.'.';

		} catch(Shuttle_Exception $e) {
			echo "<span class=\"alert alert-danger col-md-12\">" . $e->getMessage() . "</span>";
		}

}

function backupUsTable($backupPath) {

	global $errors, $successes, $backupPath;

	$db = DB::getInstance();
	$settingsQ = $db->query("SELECT * FROM settings");
	$settings = $settingsQ->first();

    try {

			$dbHost     = Config::get('mysql/host');
			$dbDatabase = Config::get('mysql/db');
			$dbUsername = Config::get('mysql/username');
			$dbPassword = Config::get('mysql/password');

			$sel_table = $settings->backup_table;

			$usDump = Shuttle_Dumper::create(array(
					'host'     => $dbHost,
					'username' => $dbUsername,
					'password' => $dbPassword,
					'db_name'  => $dbDatabase,
					'include_tables' => array($sel_table),
			));

			$usDumpFilename = $backupPath.'sql/'.$sel_table.'_'.$dbDatabase.'.sql';
			$usDump->dump($usDumpFilename);

			$successes[]='Tables have been backed up to '.$usDumpFilename.'.';

		} catch(Shuttle_Exception $e) {
			echo "<span class=\"alert alert-danger col-md-12\">" . $e->getMessage() . "</span>";
		}

}

function extractZip($restoreFile,$restoreDest){
	global $errors,$successes;
	/*
	From: http://php.net/manual/en/ziparchive.extractto.php
	*/

	$zip = new ZipArchive;
	if ($zip->open($restoreFile) === TRUE) {
			$zip->extractTo($restoreDest);
			$zip->close();
			$successes[] = 'Extracted file';
			return true;
	} else {
			$errors[] = 'Failed to open zip file';
			return false;
	}
}

function importSqlFile($sqlFile){
	global $errors, $successes;
	/*
	From: http://stackoverflow.com/questions/19751354/how-to-import-sql-file-in-mysql-database-using-php
	*/

	$db = DB::getInstance();

	// Temporary variable, used to store current query
	$templine = '';
	// Read in entire file
	$lines = file($sqlFile);
	// Loop through each line
	foreach ($lines as $line)
	{
		// Skip it if it's a comment
		if (substr($line, 0, 2) == '--' || $line == '') continue;

		// Add this line to the current segment
		$templine .= $line;

		// If it has a semicolon at the end, it's the end of the query
		if (substr(trim($line), -1, 1) == ';')
		{
				// Perform the query
				$queryResult = $db->query($templine);
				// Reset temp variable to empty

				if($queryResult){
					//$successes[]='Query successful: '.$templine;
				}else{
					$errors[] = 'Query NOT successful: '.$templine;
					return false;
				}
				$templine = '';
		}
	}
	return true;
}
?>
