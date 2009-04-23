<?php

class Users extends ActiveRecord {

	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $login;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var integer
	 */
	protected $lastlogin;


	/**
	 * Metodo para establecer el valor del campo id
	 * @param integer $id
	 */
	public function setId($id){
		$this->id = $id;
	}

	/**
	 * Metodo para establecer el valor del campo login
	 * @param string $login
	 */
	public function setLogin($login){
		$this->login = $login;
	}

	/**
	 * Metodo para establecer el valor del campo password
	 * @param string $password
	 */
	public function setPassword($password){
		$this->password = $password;
	}

	/**
	 * Metodo para establecer el valor del campo lastlogin
	 * @param integer $lastlogin
	 */
	public function setLastlogin($lastlogin){
		$this->lastlogin = $lastlogin;
	}


	/**
	 * Devuelve el valor del campo id
	 * @return integer
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * Devuelve el valor del campo login
	 * @return string
	 */
	public function getLogin(){
		return $this->login;
	}

	/**
	 * Devuelve el valor del campo password
	 * @return string
	 */
	public function getPassword(){
		return $this->password;
	}

	/**
	 * Devuelve el valor del campo lastlogin
	 * @return integer
	 */
	public function getLastlogin(){
		return $this->lastlogin;
	}

}

