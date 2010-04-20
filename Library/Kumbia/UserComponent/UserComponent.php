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
 * @category 	Kumbia
 * @package 	UserComponent
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * UserComponent
 *
 * Implementa funcionalidad que puede ser util para componentes de usuario
 *
 * @category 	Kumbia
 * @package 	UserComponent
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
abstract class UserComponent extends Object {

	/**
	 * Propiedad que realiza un bloqueo sobre las propiedades no definidas
	 *
	 * @var boolean
	 */
	private $_settingLock = false;

	/**
	 * Obliga a que todas las propiedades esten definidas previamente
	 *
	 * @access	public
	 * @param	string $property
	 * @param	string $value
	 */
	public function __set($property, $value){
		if($this->_settingLock==false){
			if(EntityManager::isModel($property)==false){
				throw new UserComponentException("Asignando propiedad indefinida '$property' al controlador");
			}
		} else {
			$this->$property = $value;
		}
	}

	/**
	 * Obliga a que todas las propiedades del controlador esten definidas
	 * previamente
	 *
	 * @access	public
	 * @param	string $property
	 */
	public function __get($property){
		if(EntityManager::isModel($property)==false){
			throw new UserComponentException("Leyendo propiedad indefinida '$property' del controlador");
		} else {
			$entity = EntityManager::getEntityInstance($property);
			$this->_settingLock = true;
			$this->$property = $entity;
			$this->_settingLock = false;
			return $this->$property;
		}
	}

	/**
	 * Filtra un valor
 	 *
 	 * @access	protected
	 * @param	string $paramValue
	 * @return	mixed
	 */
	protected function filter($paramValue){
		//Si hay más de un argumento, toma los demas como filtros
		if(func_num_args()>1){
			$args = func_get_args();
			$args[0] = $paramValue;
			$filter = new Filter();
			return call_user_func_array(array($filter, 'applyFilter'), $args);
		} else {
			throw new UserComponentException('Debe indicar al menos un filtro a aplicar');
		}
		return $paramValue;
	}

	/**
	 * Devuelve un modelo de forma estática
	 *
	 * @param	string $modelName
	 * @return	ActiveRecordBase
	 */
	public static function getModel($modelName){
		return EntityManager::getEntityInstance($modelName);
	}

}
