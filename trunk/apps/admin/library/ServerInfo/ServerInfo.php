<?php
/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbia.org so we can send you a copy immediately.
 *
 * @category Kumbia
 * @copyright Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license New BSD License
 */

/**
 * Devuelve informacion del Servidor tanto de Hardware/Software
 *
 * @category Kumbia
 * @copyright Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license New BSD License
 */
class ServerInfo extends Object {

	/**
	 * Devuelve una representacion humana de una cantidad en bytes
	 *
	 * @access public
	 * @param integer $bytes
	 * @return string
	 * @static
	 */
	public static function humanSize($bytes){
		return Helpers::toHuman($bytes);
	}

	/**
	 * Devuelve el espacio libre en Disco de acuerdo al SO
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getDiskFreeSpace(){
		if(PHP_OS=="WINNT"){
			return disk_free_space("C:");
		} else {
			$diskFreeSpace = disk_free_space($_SERVER['DOCUMENT_ROOT']);
			return self::humanSize($diskFreeSpace);
		}
	}

	#public static function

}
