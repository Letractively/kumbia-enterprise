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
 * @license		New BSD License
 * @version 	$Id: Config.php 34 2009-05-05 01:54:23Z gutierrezandresfelipe $
 */

/**
 * IniConfig
 *
 * Clase para la carga de archivos .ini
 *
 * @category	Kumbia
 * @package		Config
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class IniConfig {

	/**
	 * Constructor de la Clase Config
	 *
	 * @access 	public
	 * @param 	Config $config
	 * @param 	string $file
	 * @return 	Config
	 * @static
	 */
	public function read(Config $config, $file){
		$iniSettings = @parse_ini_file(Core::getFilePath($file), true);
		if($iniSettings==false){
			throw new ConfigException("El archivo de configuración '$file' tiene errores '$php_errormsg'");
		} else {
			foreach($iniSettings as $conf => $value){
				$config->$conf = new stdClass();
				foreach($value as $cf => $val){
					$config->$conf->$cf = $val;
				}
			}
		}
		return $config;
	}

}