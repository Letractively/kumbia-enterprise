<?php

class Attributes extends ActiveRecord {

	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $app_name;

	/**
	 * @var string
	 */
	protected $table_name;

	/**
	 * @var string
	 */
	protected $field_name;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $allow_null;

	/**
	 * @var string
	 */
	protected $primary_key;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $size;

	/**
	 * @var string
	 */
	protected $maxlength;

	/**
	 * @var string
	 */
	protected $component;

	/**
	 * @var string
	 */
	protected $hidden;

	/**
	 * @var string
	 */
	protected $browse;

	/**
	 * @var string
	 */
	protected $search;

	/**
	 * @var string
	 */
	protected $report;

	/**
	 * @var string
	 */
	protected $read_only;

	/**
	 * @var string
	 */
	protected $comments;


	/**
	 * Metodo para establecer el valor del campo id
	 * @param integer $id
	 */
	public function setId($id){
		$this->id = $id;
	}

	/**
	 * Metodo para establecer el valor del campo app_name
	 * @param string $app_name
	 */
	public function setAppName($app_name){
		$this->app_name = $app_name;
	}

	/**
	 * Metodo para establecer el valor del campo table_name
	 * @param string $table_name
	 */
	public function setTableName($table_name){
		$this->table_name = $table_name;
	}

	/**
	 * Metodo para establecer el valor del campo field_name
	 * @param string $field_name
	 */
	public function setFieldName($field_name){
		$this->field_name = $field_name;
	}

	/**
	 * Metodo para establecer el valor del campo type
	 * @param string $type
	 */
	public function setType($type){
		$this->type = $type;
	}

	/**
	 * Metodo para establecer el valor del campo allow_null
	 * @param string $allow_null
	 */
	public function setAllowNull($allow_null){
		$this->allow_null = $allow_null;
	}

	/**
	 * Metodo para establecer el valor del campo primary_key
	 * @param string $primary_key
	 */
	public function setPrimaryKey($primary_key){
		$this->primary_key = $primary_key;
	}

	/**
	 * Metodo para establecer el valor del campo label
	 * @param string $label
	 */
	public function setLabel($label){
		$this->label = $label;
	}

	/**
	 * Metodo para establecer el valor del campo size
	 * @param string $size
	 */
	public function setSize($size){
		$this->size = $size;
	}

	/**
	 * Metodo para establecer el valor del campo maxlength
	 * @param string $maxlength
	 */
	public function setMaxlength($maxlength){
		$this->maxlength = $maxlength;
	}

	/**
	 * Metodo para establecer el valor del campo component
	 * @param string $component
	 */
	public function setComponent($component){
		$this->component = $component;
	}

	/**
	 * Metodo para establecer el valor del campo hidden
	 * @param string $hidden
	 */
	public function setHidden($hidden){
		$this->hidden = $hidden;
	}

	/**
	 * Metodo para establecer el valor del campo browse
	 * @param string $browse
	 */
	public function setBrowse($browse){
		$this->browse = $browse;
	}

	/**
	 * Metodo para establecer el valor del campo search
	 * @param string $search
	 */
	public function setSearch($search){
		$this->search = $search;
	}

	/**
	 * Metodo para establecer el valor del campo report
	 * @param string $report
	 */
	public function setReport($report){
		$this->report = $report;
	}

	/**
	 * Metodo para establecer el valor del campo read_only
	 * @param string $read_only
	 */
	public function setReadOnly($read_only){
		$this->read_only = $read_only;
	}

	/**
	 * Metodo para establecer el valor del campo comments
	 * @param string $comments
	 */
	public function setComments($comments){
		$this->comments = $comments;
	}


	/**
	 * Devuelve el valor del campo id
	 * @return integer
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * Devuelve el valor del campo app_name
	 * @return string
	 */
	public function getAppName(){
		return $this->app_name;
	}

	/**
	 * Devuelve el valor del campo table_name
	 * @return string
	 */
	public function getTableName(){
		return $this->table_name;
	}

	/**
	 * Devuelve el valor del campo field_name
	 * @return string
	 */
	public function getFieldName(){
		return $this->field_name;
	}

	/**
	 * Devuelve el valor del campo type
	 * @return string
	 */
	public function getType(){
		return $this->type;
	}

	/**
	 * Devuelve el valor del campo allow_null
	 * @return string
	 */
	public function getAllowNull(){
		return $this->allow_null;
	}

	/**
	 * Devuelve el valor del campo primary_key
	 * @return string
	 */
	public function getPrimaryKey(){
		return $this->primary_key;
	}

	/**
	 * Devuelve el valor del campo label
	 * @return string
	 */
	public function getLabel(){
		return $this->label;
	}

	/**
	 * Devuelve el valor del campo size
	 * @return string
	 */
	public function getSize(){
		return $this->size;
	}

	/**
	 * Devuelve el valor del campo maxlength
	 * @return string
	 */
	public function getMaxlength(){
		return $this->maxlength;
	}

	/**
	 * Devuelve el valor del campo component
	 * @return string
	 */
	public function getComponent(){
		return $this->component;
	}

	/**
	 * Devuelve el valor del campo hidden
	 * @return string
	 */
	public function getHidden(){
		return $this->hidden;
	}

	/**
	 * Devuelve el valor del campo browse
	 * @return string
	 */
	public function getBrowse(){
		return $this->browse;
	}

	/**
	 * Devuelve el valor del campo search
	 * @return string
	 */
	public function getSearch(){
		return $this->search;
	}

	/**
	 * Devuelve el valor del campo report
	 * @return string
	 */
	public function getReport(){
		return $this->report;
	}

	/**
	 * Devuelve el valor del campo read_only
	 * @return string
	 */
	public function getReadOnly(){
		return $this->read_only;
	}

	/**
	 * Devuelve el valor del campo comments
	 * @return string
	 */
	public function getComments(){
		return $this->comments;
	}

	/**
	 * Inicializador del modelo
	 *
	 */
	public function initialize(){
		$this->hasOne("relations");
	}

}

