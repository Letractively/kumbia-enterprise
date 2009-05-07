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
 * @package		Cache
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * Cache
 *
 * Clase que implementa un componente de cacheo
 *
 * @category	Kumbia
 * @package		Cache
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class Cache extends Object {

	/**
	 * Adaptador utilizado
	 *
	 * @var string
	 */
	private $_adapter;

	/**
	 * Constructor de Cache
	 *
	 * @param string $adapter
	 * @param array $frontendOptions
	 * @param array $backendOptions
	 * @static
	 */
	public static function factory($adapter, $frontendOptions=array(), $backendOptions=array()){
		$path = 'Library/Kumbia/Cache/Adapters/'.$adapter.'.php';
		if(Core::fileExists($path)){
			require $path;
		} else {
			throw new CacheException('No existe el adaptador "'.$adapter."'");
		}
		$adapterClass = $adapter.'Cache';
		$this->_adapter = new $adapterClass($backendOptions);
	}

	/**
	 * Carga un resultado de cache si existe
	 *
	 * @param 	string $keyName
	 * @return	mixed
	 */
	public function load($keyName){
		return $this->_adapter->load($keyName);
	}

	public function save($keyName, $value){

	}

}
