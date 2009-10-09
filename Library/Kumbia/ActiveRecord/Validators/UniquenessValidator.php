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
 * @version 	$Id$
 */

/**
 * UniquenessValidator
 *
 * Valida que un campo ó la combinación de un conjunto de campos no
 * este presente más una vez en los registros de la entidad
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Validators
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class UniquenessValidator extends ActiveRecordValidator implements ActiveRecordValidatorInterface {

	/**
	 * Ejecuta el validador
	 *
	 * @return boolean
	 */
	public function validate(){
		if($this->isRequired()==true){
			$record = clone $this->getRecord();
			$field = $this->getFieldName();
			$value = addslashes($this->getValue());
			$primaryFields = $record->getPrimaryKeyAttributes();
			$condition = array();
			foreach($primaryFields as $primaryField){
				$condition[] = "$primaryField<>'".addslashes($record->readAttribute($primaryField))."'";
			}
			$conditions = join(' AND ', $condition);
			if($record->count("$field='$value' AND (".$conditions.')')>0){
				$options = $this->getOptions();
				if(isset($options['message'])){
					$this->appendMessage($options['message']);
	 			} else {
					$this->appendMessage("Este valor ya se encuentra en el atributo '$field'");
	 			}
				return false;
			}
		}
		return true;
	}
}