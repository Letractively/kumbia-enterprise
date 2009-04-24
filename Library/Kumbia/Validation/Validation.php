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
	 * Efectua una validación sobre los valores de la entrada
	 *
	 * @param Controller $controller
	 * @return boolean
	 */
	public static function validateRequired($fields, $base='', $getMode=''){
		$validationFailed = false;
		$this->cleanValidationMessages();
		if(is_array($fields)){
			if(!$base){
				$base = 'Request';
				$getMode = 'getRequestParam';
			}
			foreach($fields as $fieldName => $config){
				if(!is_numeric($fieldName)){
					if(isset($config['filter'])){
						$params = explode('|', $config['filter']);
						array_unshift($params, $fieldName);
						if(in_array(call_user_func_array(array($this, $getMode), $params), array('', null), true)){
							if(isset($config['message'])){
								$message = $config['message'];
							} else {
								$message = "Un valor para '$fieldName' es requerido";
							}
							$this->addValidationMessage($message, $fieldName);
							$validationFailed = true;
						}
					} else {
						if(in_array($this->$getMode($fieldName), array("", null), true)){
							if(isset($config['message'])){
								$message = $config['message'];
							} else {
								$message = "Un valor para '$fieldName' es requerido";
							}
							$this->addValidationMessage($message, $fieldName);
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
							$this->addValidationMessage("Un valor para '{$validation[0]}' es requerido", $validation[0]);
							$validationFailed = true;
						}
					} else {
						if(in_array($this->getRequest($validation[0]), array("", null), true)){
							$this->addValidationMessage("Un valor para '{$validation[0]}' es requerido", $validation[0]);
							$validationFailed = true;
						}
					}
				}
			}
		}
		return !$validationFailed;
	}

}
