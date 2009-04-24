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
 * @package		Validation
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version		$Id$
 */

/**
 * Validation
 *
 * Este componente está integrado a las implementaciones de controladores y
 * permite realizar validaciones sobre la entrada de usuario. Al ser
 * independiente de la capa de lógica de dominio y presentación puede
 * ser usado en los puntos de la aplicación que se requiera sin afectar
 * la arquitectura de la misma.
 *
 * @category	Kumbia
 * @package		Validation
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class Validation {

	/**
	 * Mensajes de Validación
	 *
	 * @var array
	 */
	private static $_validationMessages = array();

	/**
	 * Indica el éxito del proceso de validación
	 *
	 * @var boolean
	 */
	private static $_validationFailed = false;

	/**
	 * Limpia el buffer de mensajes de validación
	 *
	 * @access public
	 * @static
	 */
	public static function cleanValidationMessages(){
		self::$_validationMessages = array();
	}

	/**
	 * Agrega un mensaje de validación al buffer
	 *
	 * @param string $message
	 * @param string $fieldName
	 */
	public static function addValidationMessage($message, $fieldName){
		if(!isset(self::$_validationMessages[$fieldName])){
			self::$_validationMessages[$fieldName] = array();
		}
		self::$_validationMessages[$fieldName][] = $message;
	}

	/**
	 * Efectua una validación sobre los valores de la entrada
	 *
	 * @param 	array $fields
	 * @param 	string $base
	 * @param 	string $getMode
	 * @return 	boolean
	 */
	public static function validateRequired($fields, $base='', $getMode=''){
		$validationFailed = false;
		self::cleanValidationMessages();
		if(is_array($fields)){
			if(!$base){
				$base = 'Request';
				$getMode = 'getParamRequest';
			}
			$controllerRequest = ControllerRequest::getInstance();
			foreach($fields as $fieldName => $config){
				if(!is_numeric($fieldName)){
					if(isset($config['filter'])){
						$params = explode('|', $config['filter']);
						array_unshift($params, $fieldName);
						if(in_array(call_user_func_array(array($controllerRequest, $getMode), $params), array('', null), true)){
							if(isset($config['message'])){
								$message = $config['message'];
							} else {
								$message = "Un valor para '$fieldName' es requerido";
							}
							self::addValidationMessage($message, $fieldName);
							$validationFailed = true;
						}
					} else {
						if(in_array($controllerRequest->$getMode($fieldName), array("", null), true)){
							if(isset($config['message'])){
								$message = $config['message'];
							} else {
								$message = "Un valor para '$fieldName' es requerido";
							}
							self::addValidationMessage($message, $fieldName);
							$validationFailed = true;
						}
					}
				}
			}
		} else {
			if(func_num_args()>1){
				foreach(func_get_args() as $field){
					$validation = explode(':', $field);
					if(!isset($validation[1])){
						if(in_array($this->getRequest($validation[0], $validation[1]), array("", null), true)){
							self::addValidationMessage("Un valor para '{$validation[0]}' es requerido", $validation[0]);
							$validationFailed = true;
						}
					} else {
						if(in_array($this->getRequest($validation[0]), array("", null), true)){
							self::addValidationMessage("Un valor para '{$validation[0]}' es requerido", $validation[0]);
							$validationFailed = true;
						}
					}
				}
			}
		}
		self::$_validationFailed = $validationFailed;
		return !self::$_validationFailed;
	}

	/**
	 * Muestra mensajes de validación para un determinado campo
	 *
	 * @param string $field
	 * @param array $callback
	 */
	public static function showMessagesFor($field, $callback=array('Flash', 'error')){
		if(isset(self::$_validationMessages[$field])){
			if(is_callable($callback)==false){
				throw new ValidationException('El callback para mostrar mensajes no es válido');
			}
			foreach(self::$_validationMessages[$field] as $message){
				call_user_func_array($callback, array($message));
			}
		}
	}

	/**
	 * Indica si existen mensajes de validación
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function hasMessages(){
		return count(self::$_validationMessages)>0 ? true : false;
	}

	/**
	 * Indica si la última validación falló
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function validationWasFailed(){
		return self::$_validationFailed;
	}

}
