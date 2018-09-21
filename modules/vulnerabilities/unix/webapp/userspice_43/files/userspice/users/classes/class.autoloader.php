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

//if you are ever questioning if your classes are being included, uncomment the line below and the words "autoloader class included" should show at the top of your page.
//bold("<br><br>autoloader class included");
class Autoloader {
	/**
	 * File extension as a string. Defaults to ".php".
	 */
	protected static $fileExt = '.php';

	/**
	 * The top level directory where recursion will begin. Defaults to the current
	 * directory.
	 */
	protected static $pathTop = __DIR__;

	/**
	 * A placeholder to hold the file iterator so that directory traversal is only
	 * performed once.
	 */
	protected static $fileIterator = null;

	/**
	 * Autoload function for registration with spl_autoload_register
	 *
	 * Looks recursively through project directory and loads class files based on
	 * filename match.
	 *
	 * @param string $className
	 */
	public static function loader($className) {

		//$directory = new RecursiveDirectoryIterator(static::$pathTop, RecursiveDirectoryIterator::SKIP_DOTS);

		if (is_null(static::$fileIterator)) {

			//static::$fileIterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);
			static::$fileIterator =  new RecursiveIteratorIterator(
					 new RecursiveDirectoryIterator(static::$pathTop, RecursiveDirectoryIterator::SKIP_DOTS),
					   RecursiveIteratorIterator::SELF_FIRST,
					   RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
				         );
		}

		$filename = $className . static::$fileExt;

		foreach (static::$fileIterator as $file) {

			if (strtolower($file->getFilename()) === strtolower($filename)) {

				if ($file->isReadable()) {

					include_once $file->getPathname();

				}
				break;

			}

		}

	}

	/**
	 * Sets the $fileExt property
	 *
	 * @param string $fileExt The file extension used for class files.  Default is "php".
	 */
	public static function setFileExt($fileExt) {
		static::$fileExt = $fileExt;
	}

	/**
	 * Sets the $path property
	 *
	 * @param string $path The path representing the top level where recursion should
	 *                     begin. Defaults to the current directory.
	 */
	public static function setPath($path) {
		static::$pathTop = $path;
	}

}

Autoloader::setFileExt('.php');
spl_autoload_register('Autoloader::loader', true, true);
