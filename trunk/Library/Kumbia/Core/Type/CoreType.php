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
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category	Kumbia
 * @package		Core
 * @subpackage	CoreType
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * CoreType
 *
 * Permite realizar aserciones sobre tipos de datos
 *
 * @category	Kumbia
 * @package		Core
 * @subpackage	CoreType
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class CoreType {

	/**
	 * Realiza una asercion de un valor entero
	 *
	 * @param int $var
	 */
	public static function assertNumeric($var){
		if(is_int($var)==false){
			throw new CoreException("Se esperaba recibir un valor entero");
		}
	}

	/**
	 * Realiza una asercion de un valor booleano
	 *
	 * @param bool $var
	 */
	public static function assertBool($var){
		if(is_bool($var)==false){
			throw new CoreException("Se esperaba recibir un valor booleano");
		}
	}

	/**
	 * Realiza una asercion de un valor string
	 *
	 * @param string $var
	 */
	public static function assertString($str){
		if(is_string($str)==false){
			throw new CoreException("Se esperaba recibir un valor string");
		}
	}

}
