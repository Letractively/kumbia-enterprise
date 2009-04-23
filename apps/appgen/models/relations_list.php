<?php

class RelationsList extends ActiveRecord {

	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * @var integer
	 */
	protected $relations_id;

	/**
	 * @var integer
	 */
	protected $number;

	/**
	 * @var string
	 */
	protected $field_name;


	/**
	 * Metodo para establecer el valor del campo id
	 * @param integer $id
	 */
	public function setId($id){
		$this->id = $id;
	}

	/**
	 * Metodo para establecer el valor del campo relations_id
	 * @param integer $relations_id
	 */
	public function setRelationsId($relations_id){
		$this->relations_id = $relations_id;
	}

	/**
	 * Metodo para establecer el valor del campo number
	 * @param integer $number
	 */
	public function setNumber($number){
		$this->number = $number;
	}

	/**
	 * Metodo para establecer el valor del campo field_name
	 * @param string $field_name
	 */
	public function setFieldName($field_name){
		$this->field_name = $field_name;
	}


	/**
	 * Devuelve el valor del campo id
	 * @return integer
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * Devuelve el valor del campo relations_id
	 * @return integer
	 */
	public function getRelationsId(){
		return $this->relations_id;
	}

	/**
	 * Devuelve el valor del campo number
	 * @return integer
	 */
	public function getNumber(){
		return $this->number;
	}

	/**
	 * Devuelve el valor del campo field_name
	 * @return string
	 */
	public function getFieldName(){
		return $this->field_name;
	}

}

