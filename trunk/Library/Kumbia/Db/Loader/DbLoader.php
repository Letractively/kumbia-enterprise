<?php

/**
 * Kumbia Enteprise Framework
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
 * @package 	Db
 * @subpackage	Loader
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * DbLoader
 *
 * Clase encargada de cargar el adaptador de conexión a bases de datos
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Loader
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license		New BSD License
 */
abstract class DbLoader {

	/**
	 * Carga un adaptador de base de datos segun parámetros
	 *
	 * @param	string $adapterName
	 * @param	array $options
	 * @return	DbBase
	 */
	public static function factory($adapterName, $options){
		$descriptor = new stdClass();
		if(!is_array($options)){
			throw new DbLoaderException("El parámetro 'options' debe ser un Array");
		}
		foreach($options as $key => $value){
			$descriptor->$key = $value;
		}
		if(isset($descriptor->layer)){
			$layer = $descriptor->layer;
		} else {
			$layer = 'native';
		}
		$className = self::_loadAdapterClass($layer, $adapterName);
		return new $className($descriptor);
	}

	/**
	 * Carga el archivo y devuelve la clase a cargar
	 *
	 * @param string $layer
	 * @param string $type
	 */
	private static function _loadAdapterClass($layer, $type){
		switch($layer){
			case 'native':
				$className = 'Db'.$type;
				if(class_exists($className, false)==false){
					require 'Library/Kumbia/Db/Adapters/Native/'.ucfirst($type).'.php';
				}
				break;
			case 'pdo':
				/**
				 * @see DbPDO
				 */
				$className = 'DbPDO'.$type;
				if(class_exists($className, false)==false){
					require 'Library/Kumbia/Db/Adapters/Pdo.php';
					require 'Library/Kumbia/Db/Adapters/Pdo/'.ucfirst($type).'.php';
				}
				break;
			case 'jdbc':
				/**
				 * @see DbJDBC
				 */
				$className = 'DbJDBC'.$type;
				if(class_exists($className, false)==false){
					require 'Library/Kumbia/Db/Adapters/Jdbc.php';
					require 'Library/Kumbia/Db/Adapters/Jdbc/'.ucfirst($type).'.php';
				}
				break;
			case 'nosql':
				$className = 'Db'.$type;
				if(class_exists($className, false)==false){
					require 'Library/Kumbia/Db/Adapters/NoSQL/'.ucfirst($type).'.php';
				}
				break;
			case 'none':
				break;
			default:
				throw new DbLoaderException('No se pudo determinar el tipo de capa de acceso a gestores relacionales', 0);
		}
		if(!class_exists($className, false)){
			throw new DbLoaderException('No existe la clase '.$className.', necesaria para iniciar el adaptador', 0);
		}

		//Verificar si requiere de un SQLDialect
		$sqlDialect = call_user_func_array(array($className, 'getSQLDialect'), array());
		if($sqlDialect!==null){
			if(!class_exists($type.'SQLDialect', false)){
				require 'Library/Kumbia/Db/SQLDialects/'.ucfirst($type).'.php';
			}
		}
		return $className;
	}

	/**
	 * Carga un driver según lo especificado en environment.ini
	 *
	 * @static
	 * @return boolean
	 */
	public static function loadDriver(){
		$config = CoreConfig::readEnviroment();
		if(isset($config->database->layer)){
			$layer = $config->database->layer;
		} else {
			$layer = 'native';
		}
		if(isset($config->database->type)){
			$type = $config->database->type;
		}
		$className = self::_loadAdapterClass($layer, $type);
		eval('class Db extends '.$className.' {}');
		#if[compile-time]
		$extensionRequired = Db::getPHPExtensionRequired();
		if(is_array($extensionRequired)){
			$someExtension = false;
			foreach($extensionRequired as $extension){
				if(extension_loaded($extension)){
					$someExtension = true;
					break;
				}
			}
			if($someExtension==false){
				throw new DbException("Debe cargar alguna de las siguientes extensiones de PHP: ".join(", ", $extensionRequired), 0);
			}
		} else {
			if(extension_loaded($extensionRequired)==false){
				throw new DbException("Debe cargar la extensión de PHP llamada php_$extensionRequired", 0);
			}
		}
		#endif
		return true;
	}

	/**
	 * Crea una conexión apartir de un descriptor
	 *
	 * @param string $descriptor
	 * @static
	 */
	static public function factoryFromDescriptor($descriptor){
		$descriptorParts = explode(':', $descriptor);
		$adapterName = $descriptorParts[0];
		$settings = explode(";", $descriptorParts[1]);
		$dbDescriptor = array();
		foreach($settings as $param){
			$paramData = explode('=', $param);
			$dbDescriptor[$paramData[0]] = $paramData[1];
		}
		return self::factory($adapterName, $dbDescriptor);
	}

}
