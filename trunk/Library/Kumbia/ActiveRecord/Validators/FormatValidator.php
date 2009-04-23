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
 * @package		ActiveRecord
 * @subpackage	Validators
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: FormatValidator.php 135 2009-04-16 11:06:19Z gutierrezandresfelipe $
 */

/**
 * FormatValidator
 *
 * Permite validar si el valor de un campo coincide con una expresión regular
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Validators
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class FormatValidator extends ActiveRecordValidator implements ActiveRecordValidatorInterface {

	/**
	 * Chequea que las opciones del validador sean correctas
	 *
	 * @access public
	 */
	public function checkOptions(){
		if($this->isSetOption('format')==false){
			throw new ActiveRecordValidatorException("El Validador Format requiere que indique la expres&oacute;nn regular (Perl-Compatible)");
		}
	}

	/**
	 * Ejecuta el validador
	 *
	 * @access public
	 * @param ActiveRecord $record
	 * @return boolean
	 */
	public function validate(){
		$validateFails = false;
		if(preg_match($this->getOption('format'), $this->getValue(), $matches)){
			if($matches[0]!=$this->getValue()){
				$this->appendMessage("El campo '{$this->getFieldName()}' no tiene un formato valido");
				$validateFails = true;
			}
		} else {
			$this->appendMessage("El campo '{$this->getFieldName()}' no tiene un formato valido");
			$validateFails = true;
		}
		if($validateFails){
			return false;
		} else {
			return true;
		}
	}
}
