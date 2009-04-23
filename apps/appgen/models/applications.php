<?php

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

