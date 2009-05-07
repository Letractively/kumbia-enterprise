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
 * @package		Config
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * Config
 *
 * Clase para la carga de Archivos .INI y de configuracion
 *
 * Aplica el patrón Singleton que utiliza un array
 * indexado por el nombre del archivo para evitar que
 * un .ini de configuracion sea leido más de una
 * vez en runtime con lo que se aumenta la velocidad.
 *
 * @category	Kumbia
 * @package		Config
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class Config extends Object {

	/**
	 * Contenido cacheado de los diferentes archivos leidos
	 *
	 * @var array
	 */
	static private $_instance = array();

	/**
	 * Instancias de adaptadores de Config
	 *
	 * @var array
	 */
	static private $_adapterInstances = array();

	/**
	 * El constructor privado impide q la clase sea
	 * instanciada y obliga a usar el metodo read
	 * para obtener la instancia del objeto
	 *
	 * @access private
	 */
	private function __construct(){

	}

	/**
	 * Lee un archivo de configuración
	 *
	 * @access 	public
	 * @param 	string $file
	 * @param 	string $adapter
	 * @return 	Config
	 * @static
	 */
	static public function read($file, $adapter){
		if(isset(self::$_instance[$file])){
			return self::$_instance[$file];
		}
		$config = new Config();
		if(Core::fileExists($file)==false){
			throw new ConfigException("No existe el archivo de configuración '$file'");
		}
		$adapterInstance = self::factory($adapter);
		$config = $adapterInstance->read($config, $file);
		self::$_instance[$file] = $config;
		return $config;
	}

	/**
	 * Devuelve una instancia de un adaptador de lectura de configuración
	 *
	 * @access 	public
	 * @param 	string $adapter
	 * @static
	 */
	public static function factory($adapter){
		if(!isset(self::$_adapterInstances[$adapter])){
			$className = $adapter.'Config';
			if(class_exists($className)==false){
				$path = 'Library/Kumbia/Config/Adapters/'.$adapter.'.php';
				if(Core::fileExists($path)){
					require $path;
				} else {
					throw new ConfigException("No existe el adaptador de configuración '$adapter'");
				}
			}
			self::$_adapterInstances[$adapter] = new $className();
		}
		return self::$_adapterInstances[$adapter];
	}

	/**
	 * Método mágico para obtener los valores usando getters
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, $arguments){
		$property = Utils::uncamelize(substr($method, 3));
		if(isset($this->{$property})){
			return $property;
		} else {
			throw new CoreException("No existe la propiedad $method en el objeto Config");
		}
	}

}
