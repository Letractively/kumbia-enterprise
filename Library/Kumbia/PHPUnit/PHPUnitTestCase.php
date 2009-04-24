<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.

 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	PHPUnit
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * PHPUnitTestCase
 *
 * Clase que permite definir los metodos de aserci&oacute;n
 * para realizar los test de unidad
 *
 * @category 	Kumbia
 * @package 	PHPUnit
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class PHPUnitTestCase extends Object {

	/**
	 * Hace una assercion si el archivo existe
	 *
	 * @access protected
	 * @param string $path
	 * @return boolean
	 */
	protected function assertFileExists($path){
		if(!Core::fileExists($path)){
			throw new AssertionFailed("No existe el archivo '$path'");
		} else {
			return true;
		}
	}

	/**
	 * Hace una asercion sobre valores iguales
	 *
	 * @param string $value1
	 * @param string $value2
	 * @return boolean
	 */
	protected function assertEquals($value1, $value2){
		if($value1!==$value2){
			if($value1===false){
				$v1 = "false";
			} else {
				if($value1===true){
					$v1 = "true";
				} else {
					$v1 = $value1;
				}
			}
			if($value2===false){
				$v2 = "false";
			} else {
				if($value2===true){
					$v2 = "true";
				} else {
					$v2 = $value2;
				}
			}
			throw new AssertionFailed("El valor (".gettype($value1).") $v1 no es igual a (".gettype($value2).") '$value2'");
		} else {
			return true;
		}
	}

	/**
	 * Hace una aserción sobre si un objeto pertenece a una clase
	 *
	 * @param object $object
	 * @param string $className
	 * @return boolean
	 */
	protected function assertInstanceOf($object, $className){
		if(!is_object($object)){
			throw new AssertionFailed("El valor no es un objeto");
		}
		if(get_class($object)!=$className){
			throw new AssertionFailed("El objeto no pertenece a la clase '$className'");
		}
		return true;
	}

	/**
	 * Hace una aserción sobre si una variable es un recurso
	 *
	 * @param resource $resource
	 * @return boolean
	 */
	protected function assertResource($resource){
		if(!is_object($resource)){
			throw new AssertionFailed("El valor no es un recurso");
		}
	}

	/**
	 * Hace una aserción sobre si un valor es verdadero
	 *
	 * @param bool $value
	 */
	protected function assertTrue($value){
		if($value!==true){
			throw new AssertionFailed("El valor '$value' no es un verdadero");
		}
	}

	/**
	 * Hace una aserción sobre si un valor es nulo
	 *
	 * @param null $value
	 */
	protected function assertNull($value){
		if($value!==null){
			throw new AssertionFailed("El valor '$value' no es un nulo");
		}
	}

}
