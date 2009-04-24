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
 * @version 	$Id: Config.php 135 2009-04-16 11:06:19Z gutierrezandresfelipe $
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
	 * El constructor privado impide q la clase sea
	 * instanciada y obliga a usar el metodo read
	 * para obtener la instancia del objeto
	 *
	 * @access private
	 */
	private function __construct(){

	}

	/**
	 * Constructor de la Clase Config
	 *
	 * @access public
	 * @param string $file
	 * @return Config
	 * @static
	 */
	static public function read($file='environment.ini'){
		if(isset(self::$_instance[$file])){
			return self::$_instance[$file];
		}
		$config = new Config();
		if(Core::fileExists($file)==false){
			throw new ConfigException("No existe el archivo de configuración $file");
		}
		$iniSettings = @parse_ini_file(Core::getFilePath($file), true);
		foreach($iniSettings as $conf => $value){
			$config->$conf = new stdClass();
			foreach($value as $cf => $val){
				$config->$conf->$cf = $val;
			}
		}
		self::$_instance[$file] = $config;
		return $config;
	}

}
