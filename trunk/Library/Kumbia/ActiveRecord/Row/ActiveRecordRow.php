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
 * @package		ActiveRecord
 * @subpackage	ActiveRecordJoin
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * ActiveRecordRow
 *
 * Permite crear una instancia de un resultado de un ActiveRecordJoin
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordJoin
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class ActiveRecordRow extends Object implements ActiveRecordResultInterface {

	/**
	 * Conexion al motor con el que se hara la consulta
	 *
	 * @var dbBase
	 */
	private $_db;

	/**
	 * Constructor de la clase
	 * @access public
	 */
	public function __construct(){

	}

	/**
	 * Establece la conexion interna para obtener los resultados
	 *
	 * @param dbBase $db
	 * @access public
	 */
	public function setConnection($db){
		$this->_db = $db;
	}

	/**
	 * Devuelve el objeto de conexion interna
	 *
	 * @access public
	 * @return dbBase
	 */
	public function getConnection(){
		return $this->_db;
	}

	/**
	 * Devuelve un resultado con los valores establecidos
	 *
	 * @access public
	 * @param array $result
	 */
	public function dumpResult(array $result){
		$objectRow = clone $this;
		foreach($result as $field => $value){
			$objectRow->$field = $value;
		}
		return $objectRow;
	}

	/**
	 * Envia una excepcion cuando se accede a una propiedad no inicializada
	 *
	 * @access public
	 * @param string $property
	 */
	public function __get($property){
		throw new ActiveRecordException("No existe el atributo '$property' en la consulta");
	}

	/**
	 * Lee un atributo del resultado por su nombre
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function readAttribute($property){
		return $this->$property;
	}

	/**
	 * Genera una excepcion cuando se trata de sobreescribir algun miembro del resultado
	 *
	 * @access public
	 * @param string $property
	 * @param string $value
	 */
	#public function __set($property, $value){
		#throw new ActiveRecordException("El resultset es de solo lectura");
	#}

	/**
	 * Permite obtener los valores mediantes
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, $arguments){
		if(substr($method, 0, 3)=="get"){
			$property = Utils::uncamelize(substr($method, 3));
			if(isset($this->$property)){
				return $this->$property;
			}
		}
		throw new ActiveRecordException("El método '{$method}' ó miembro '{$property}' del resultset no existe");
	}

}
