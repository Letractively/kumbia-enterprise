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
 * @category 	Kumbia
 * @package 	Script
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * Script
 *
 * Componente que permite escribir scripts para uso en CLI
 *
 * @category 	Kumbia
 * @package 	Script
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class Script extends Object {

	/**
	 * Codificación de salida del script
	 *
	 * @var string
	 */
	private $_encoding = 'UTF-8';

	/**
	 * Parametros recibidos por el script
	 *
	 * @var string
	 */
	private $_parameters = array();

	/**
	 * Parsea los parametros pasados al script
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function parseParameters($parameters=array()){

		$arguments = array();
		$posibleArguments = array();
		foreach($parameters as $parameter => $description){
			if(strpos($parameter, "=")!==false){
				$parameterParts = explode("=", $parameter);
				if(count($parameterParts)!=2){
					throw new ScriptException("Definición inválida para el parámetro '$parameter'");
				}
				if(strlen($parameterParts[0])==""){
					throw new ScriptException("Definición inválida para el parámetro '".$parameter."'");
				}
				if(!in_array($parameterParts[1], array('s', 'i'))){
					throw new ScriptException("Tipo de dato incorrecto en parámetro '".$parameter."'");
				}
				$posibleArguments[] = $parameterParts[0];
				$arguments[$parameterParts[0]] = array(
					'have-option' => true,
					'option-required' => true,
					'data-type' => $parameterParts[1]
				);
			} else {
				if(strpos($parameter, "=")!==false){
					$parameterParts = explode("=", $parameter);
					if(count($parameterParts)!=2){
						throw new ScriptException("Definición invalida para el parámetro '$parameter'");
					}
					if(strlen($parameterParts[0])==""){
						throw new ScriptException("Definición invalida para el parámetro '$parameter'");
					}
					if(!in_array($parameterParts[1], array('s', 'i'))){
						throw new ScriptException("Tipo de dato incorrecto en parámetro '$parameter'");
					}
					$posibleArguments[] = $parameterParts[0];
					$arguments[$parameterParts[0]] = array(
						'have-option' => true,
						'option-required' => false,
						'data-type' => $parameterParts[1]
					);
				} else {
					if(preg_match('/([a-zA-Z0-9]+)/', $parameter)){
						$posibleArguments[] = $parameter;
						$arguments[$parameter] = array(
							'have-option' => false,
							'option-required' => false
						);
					} else {
						throw new ScriptException("Paámetro invalido '$parameter'");
					}
				}
			}
		}

		$paramName = "";
		$allParamNames = array();
		$receivedParams = array();
		$param = "";
		$numberArguments = count($_SERVER['argv']);
		for($i=1;$i<$numberArguments;$i++){
			$argv = $_SERVER['argv'][$i];
			if(substr($argv, 0, 2)=="--"){
				$parameter = substr($argv, 2);
				if(!in_array($parameter, $posibleArguments)){
					throw new ScriptException("Parámetro desconocido '$parameter'");
				}
				if(!strlen($parameter)){
					throw new ScriptException("Parámetro de Script inválido en la posición $i");
				} else {
					if($paramName!=""){
						if(isset($arguments[$parameter])){
							if($param==""){
								if($arguments[$paramName]['have-option']==true){
									throw new ScriptException("El parámetro '$paramName' requiere una opción");
								}
							}
						}
						$receivedParams[$paramName] = $param;
						$param = "";
					}
					$paramName = $parameter;
					$allParamNames[] = $parameter;
				}
			} else {
				$param = $argv;
				if($paramName!=""){
					if(isset($arguments[$paramName])){
						if($param==""){
							if($arguments[$paramName]['have-option']==true){
								throw new ScriptException("El parámetro '$paramName' requiere una opción");
							}
						}
					}
					$receivedParams[$paramName] = $param;
					$param = "";
					$paramName = "";
				} else {
					$receivedParams[$i-1] = $param;
					$param = "";
				}
			}
		}
		if($paramName!=""){
			$receivedParams[$paramName] = $param;
			$params = array();
		} else {
			if($param!=""){
				$receivedParams[$i-1] = $param;
				$param = "";
			}
		}
		$this->_parameters = $receivedParams;
		return $receivedParams;
	}

	/**
	 * Chequea que un conjunto de parametros se haya recibido
	 *
	 * @param array $required
	 */
	public function checkRequired($required){
		foreach($required as $fieldRequired){
			if(!isset($this->_parameters[$fieldRequired])){
				throw new ScriptException("El parámetro '$fieldRequired' es requerido por este script");
			}
		}
	}

	/**
	 * Establece la codificación de la salida del script
	 *
	 * @param string $encoding
	 */
	public function setEncoding($encoding){
		$this->_encoding = $encoding;
	}

	/**
	 * Muestra la ayuda del script
	 *
	 * @param array $posibleParameters
	 */
	public function showHelp($posibleParameters){
		echo basename($_SERVER['PHP_SELF'])." - Modo de uso:\n\n";
		foreach($posibleParameters as $parameter => $description){
			echo html_entity_decode($description, ENT_COMPAT, $this->_encoding)."\n";
		}
	}

	/**
	 * Devuelve el valor de una opción recibida
	 *
	 * @param string $option
	 */
	public function getOption($option){
		if(isset($this->_parameters[$option])){
			return $this->_parameters[$option];
		} else {
			return null;
		}
	}

	/**
	 * Indica si el script recibio una determinada opcion
	 *
	 * @param string $option
	 * @return boolean
	 */
	public function isReceivedOption($option){
		return isset($this->_parameters[$option]);
	}

	/**
	 * Muestra un mensaje en la consola de texto
	 *
	 * @param Exception $exception
	 */
	public static function showConsoleException($exception){

		$isXTermColor = false;
		if(isset($_ENV['TERM'])){
			foreach(array('256color') as $term){
				if(preg_match('/'.$term.'/', $_ENV['TERM'])){
					$isXTermColor = true;
				}
			}
		}

		$isSupportedShell = false;
		if($isXTermColor){
			if(isset($_ENV['SHELL'])){
				foreach(array('bash', 'tcl') as $shell){
					if(preg_match('/'.$shell.'/', $_ENV['SHELL'])){
						$isSupportedShell = true;
					}
				}
			}
		}

		if(!class_exists('ScriptColor', false)){
			require 'Library/Kumbia/Script/Color/ScriptColor.php';
		}

		ScriptColor::setFlags($isSupportedShell && $isSupportedShell);

		$output = "";
		$output.= ScriptColor::colorize(get_class($exception).': ', ScriptColor::RED, ScriptColor::BOLD);
		$message = str_replace("\"", "\\\"", $exception->getMessage());
		$output.= ScriptColor::colorize($message, ScriptColor::WHITE, ScriptColor::BOLD);
		$output.='\\n';

		$output.= Highlight::getString(file_get_contents($exception->getFile()), 'console', array(
			'firstLine' => ($exception->getLine()-3<0 ? $exception->getLine() : $exception->getLine()-3),
			'lastLine' => $exception->getLine()+3
		));

		$i = 1;
		$getcwd = getcwd();
		foreach($exception->getTrace() as $trace){
			$output.= ScriptColor::colorize('#'.$i, ScriptColor::WHITE, ScriptColor::UNDERLINE);
			$output.= ' ';
			$file = str_replace($getcwd, '', $trace['file']);
			$output.= ScriptColor::colorize($file.'\\n', ScriptColor::NORMAL);
			$i++;
		}

		if($isSupportedShell){
			system('echo -e "'.$output.'"');
		} else {
			echo $output;
		}

	}

}

