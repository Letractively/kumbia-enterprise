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
 * @version 	$Id: Cache.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * FileCache
 *
 * Adaptador que permite almacenar datos en un Cache
 *
 * @category	Kumbia
 * @package		Cache
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class FileCache {

	/**
	 * Opciones del adaptador
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Constructor del adaptador
	 *
	 * @param array $options
	 */
	public function __construct($options){
		$this->_options = $options;
	}

	public function load(){

	}

}