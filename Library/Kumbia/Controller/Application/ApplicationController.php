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
 * @package		Controller
 * @subpackage	ApplicationController
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version		$Id$
 */

/**
 * ApplicationController
 *
 * Es la clase principal para controladores de Kumbia
 *
 * @category	Kumbia
 * @package		Controller
 * @subpackage	ApplicationController
 * @copyright 	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class ApplicationController extends Controller  {

	/**
	 * Visualiza una vista en el controlador actual
	 *
	 * @access 	protected
	 * @param 	string $view
	 */
	protected function render($view){
		$viewsDir = Core::getActiveViewsDir();
		$path = $viewsDir.'/'.$view.'.phtml';
		if(Core::fileExists($path)){
			foreach(EntityManager::getEntities() as $modelName => $model){
				$$modelName = $model;
			}
			foreach($this as $_var => $_value){
				$$_var = $_value;
			}
			foreach(View::getViewParams() as $_key => $_value){
				$$_key = $_value;
			}
			include $path;
		} else {
			throw new ApplicationControllerException('No existe la vista ó no se puede leer el archivo');
		}
	}

	/**
	 * Visualiza un texto en la vista actual
	 *
	 * @access protected
	 * @param string $text
	 */
	protected function renderText($text){
		print $text;
	}

	/**
	 * Visualiza una vista parcial en el controlador actual
	 *
	 * @access protected
	 * @param string $partial
	 * @param string $values
	 */
	protected function renderPartial($partial, $values = ''){
		View::renderPartial($partial, $values);
	}

	/**
	 * Valida que los campos requeridos enten presentes
	 *
	 * @access protected
	 * @param string $fields
	 * @param string $base
	 * @param string $callback
	 * @return boolean
	 */
	protected function validateRequired($fields, $base='', $getMode=''){
		return Validation::validateRequired($fields, $base, $getMode);
	}

	/**
	 * Limpia la lista de Mensajes
	 *
	 * @access protected
	 */
	protected function cleanValidationMessages(){
		Validation::cleanValidationMessages();
	}

	/**
	 * Agrega un mensaje a la lista de mensajes
	 *
	 * @access 	protected
	 * @param 	string $fieldName
	 * @param 	string $message
	 */
	protected function addValidationMessage($message, $fieldName=''){
		Validation::addValidationMessage($message, $fieldName);
	}

	/**
	 * Devuelve los mensajes de validación generados
	 *
	 * @access 	protected
	 * @return 	array
	 */
	public function getValidationMessages(){
		return Validation::getMessages();
	}

	/**
	 * La definición de este método indica si se debe exportar
	 * las variables públicas
	 *
	 * @access 	public
	 * @return 	true
	 */
	public function isExportable(){
		return true;
	}

}
