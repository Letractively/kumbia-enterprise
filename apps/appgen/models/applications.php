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
 * @package 	Kumbia
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright  	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2008-2009 Oscar Garavito (game013@gmail.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * Applications
 *
 * Modelo Applications
 *
 */
class Applications extends ActiveRecord {

	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $resume;

	/**
	 * @var string
	 */
	protected $description;


	/**
	 * Metodo para establecer el valor del campo id
	 * @param integer $id
	 */
	public function setId($id){
		$this->id = $id;
	}

	/**
	 * Metodo para establecer el valor del campo name
	 * @param string $name
	 */
	public function setName($name){
		$this->name = $name;
	}

	/**
	 * Metodo para establecer el valor del campo resume
	 * @param string $resume
	 */
	public function setResume($resume){
		$this->resume = $resume;
	}

	/**
	 * Metodo para establecer el valor del campo description
	 * @param string $description
	 */
	public function setDescription($description){
		$this->description = $description;
	}


	/**
	 * Devuelve el valor del campo id
	 * @return integer
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * Devuelve el valor del campo name
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Devuelve el valor del campo resume
	 * @return string
	 */
	public function getResume(){
		return $this->resume;
	}

	/**
	 * Devuelve el valor del campo description
	 * @return string
	 */
	public function getDescription(){
		return $this->description;
	}

}

