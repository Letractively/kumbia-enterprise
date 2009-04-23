<?php

class Relations extends ActiveRecord {

	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * @var integer
	 */
	protected $attributes_id;

	/**
	 * @var string
	 */
	protected $table_relation;

	/**
	 * @var string
	 */
	protected $field_detail;

	/**
	 * @var string
	 */
	protected $field_order;


	/**
	 * Metodo para establecer el valor del campo id
	 * @param integer $id
	 */
	public function setId($id){
		$this->id = $id;
	}

	/**
	 * Metodo para establecer el valor del campo attributes_id
	 * @param integer $attributes_id
	 */
	public function setAttributesId($attributes_id){
		$this->attributes_id = $attributes_id;
	}

	/**
	 * Metodo para establecer el valor del campo table_relation
	 * @param string $table_relation
	 */
	public function setTableRelation($table_relation){
		$this->table_relation = $table_relation;
	}

	/**
	 * Metodo para establecer el valor del campo field_detail
	 * @param string $field_detail
	 */
	public function setFieldDetail($field_detail){
		$this->field_detail = $field_detail;
	}

	/**
	 * Metodo para establecer el valor del campo field_order
	 * @param string $field_order
	 */
	public function setFieldOrder($field_order){
		$this->field_order = $field_order;
	}


	/**
	 * Devuelve el valor del campo id
	 * @return integer
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * Devuelve el valor del campo attributes_id
	 * @return integer
	 */
	public function getAttributesId(){
		return $this->attributes_id;
	}

	/**
	 * Devuelve el valor del campo table_relation
	 * @return string
	 */
	public function getTableRelation(){
		return $this->table_relation;
	}

	/**
	 * Devuelve el valor del campo field_detail
	 * @return string
	 */
	public function getFieldDetail(){
		return $this->field_detail;
	}

	/**
	 * Devuelve el valor del campo field_order
	 * @return string
	 */
	public function getFieldOrder(){
		return $this->field_order;
	}

	/**
	 * Inicializa el modelo
	 *
	 */
	public function initialize(){
		$this->hasMany("relations_list");
	}

}

