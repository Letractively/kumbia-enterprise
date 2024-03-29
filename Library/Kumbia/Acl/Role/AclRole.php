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
 * @package		Acl
 * @subpackage	AclRole
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * AclRole
 *
 * Esta clase define los roles y descripcion de cada uno
 *
 * @category	Kumbia
 * @package		Acl
 * @subpackage	AclRole
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 *
 */
class AclRole {

	/**
	 * Nombre del Rol
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Descripcion del Rol
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Constructor de la clase Rol
	 *
	 * @param string $name
	 * @return Acl_Role
	 */
	public function __construct($name, $description=''){
		if($name=='*'){
			$message = CoreLocale::getErrorMessage(-15);
			throw new AclException($message, -15);
		}
		$this->name = $name;
		$this->description = $description;
	}

	/**
	 * Devuelve el nombre del Role
	 *
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Devuelve la descripcion del Role
	 *
	 */
	public function getDescription(){
		return $this->description;
	}

}
