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
 * @category 	Kumbia
 * @package 	Session
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: SessionRecord.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * SessionRecord
 *
 * Clase que actua como un ActiveRecord de Session
 *
 * @category 	Kumbia
 * @package 	Session
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @access 		public
 */
class SessionRecord extends ActiveRecordBase {

	private $_bindSessionId = "sid";

	/**
	 * Constructor de SessionRecord
	 *
	 */
	public function __construct(){
		$this->{$this->_bindSessionId} = Session::getId();
	}

	/**
	 * Establece el campo Sid en el modelo
	 *
	 * @param string $sidField
	 */
	public function bindSessionId($sidField){
		$this->_bindSessionId = $sidField;
		parent::__construct();
	}

	/**
	 * Find data on Relational Map table
	 *
	 * @access	public
	 * @param 	string $params
	 * @return 	ActiveRecordResulset
	 */
	public function find(){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params[0])){
			if(isset($params['conditions'])){

			}
		}
		parent::find($arguments);
	}

	public function save(){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		parent::find($arguments);
	}

}
