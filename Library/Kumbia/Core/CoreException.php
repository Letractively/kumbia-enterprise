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
 * @package		Core
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: CoreException.php 135 2009-04-16 11:06:19Z gutierrezandresfelipe $
 */

/**
 * CoreException
 *
 * Clase principal de Implementación de Excepciones
 *
 * @category	Kumbia
 * @package		Core
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class CoreException extends Exception {

	/**
	 * Codigo de error de la Excepcion
	 */
	protected $error_code = 0;

	/**
	 * Mostrar Trace o no
	 *
	 * @var boolean
	 */
	protected $show_trace = true;

	/**
	 * Indica si la excepción puede ser capturada por el usuario
	 *
	 * @var boolean
	 */
	protected $_userCatchable = true;

	/**
	 * Backtrace adicional
	 *
	 * @var array
	 */
	protected $extendedBacktrace = array();

	/**
	 * Constructor de la clase
	 *
	 * @access public
	 * @param string $message
	 * @param int $errorCode
	 * @param boolean $showTrace
	 * @param array $backtrace
	 */
	public function __construct($message, $errorCode = 0, $showTrace=true, $backtrace=array()){
		$this->show_trace = $showTrace;
		$this->extendedBacktrace = $backtrace;
		if(is_numeric($errorCode)){
			parent::__construct($message, $errorCode);
		} else {
			$this->error_code = $errorCode;
			parent::__construct($message, 0);
		}
	}

	/**
	 * Establece si la excepcion puede ser capturada
	 *
	 * @param boolean $catchable
	 */
	public function setUserCatchable($catchable){
		$this->_userCatchable = $catchable;
	}

	/**
	 * Indica si la excepcion puede ser capturada por el usuario
	 *
	 * @return boolean
	 */
	public function isUserCatchable(){
		return $this->_userCatchable;
	}

	/**
	 * Genera la salida de la excepcion
	 *
	 * @access public
	 */
	public function showMessage(){
		if(Session::isStarted()==false){
			Session::startSession();
		}
		Core::setInstanceName();
		Core::setTimeZone();
		$instanceName = Core::getInstanceName();
		if(isset($_SERVER['DOCUMENT_ROOT'])){
			$file = str_replace($_SERVER['DOCUMENT_ROOT'], "", $this->getFile());
		} else {
			$file = $this->getFile();
		}
		print "\n<div style='background: #FFFFFF; padding: 5px;'>\n";
		Flash::error(get_class($this).": $this->message ({$this->getCode()})<br>
		<span style='font-size:12px'>En el archivo <i>{$file}</i> en la l&iacute;nea: <i>{$this->getLine()}</i>");
		$config = CoreConfig::readFromActiveApplication("config.ini");
		$active_app = Core::getActiveApplication();
		if($this->show_trace==true){
			if(isset($config->application->debug)&&$config->application->debug==true){
				$requestTime = microtime(true);
				$debugMessages = Debug::getMessages();
				if(count($debugMessages)>0){
					print "<div class='debugInformation'>\n";
					print "<strong>Datos de Debug:</strong>";
					print "<table cellspacing='0' width='100%' align='center'>
						<thead>
							<th>#</th>
							<th>Valor</th>
							<th>M&eacute;todo/Funci&oacute;n</th>
							<th>L&iacute;nea</th>
							<th>Archivo</th>
							<th>Tiempo</th>
						</thead>\n";
					$i = 1;
					foreach($debugMessages as $message){
						$file = $message['file'];
						$file = basename($file);
						$time = round($message['time']-$_SERVER['REQUEST_TIME'], 4);
						if($message['completeBacktrace']==true){
							$bgcolor = "pink";
						} else {
							$bgcolor = "#ffffff";
						}
						print "
						<tr bgcolor='$bgcolor'>
							<td align='center'>$i</td>
							<td><pre>".wordwrap(htmlentities(print_r($message['value'], true), 100))."</pre></td>
							<td>{$message['class']}::{$message['function']}</td>
							<td align='center'>{$message['line']}</td>
							<td>$file</td>
							<td align='center'>$time</td>
						</tr>";
						if($message['completeBacktrace']==true){
							$i = 0;
							foreach($message['backtrace'] as $back){
								if($i>=1){
									if(isset($back['line'])){
										$functionCall = Debug::getFunctionCallAsString($back);
										print "<tr bgcolor='#f2f2f2'>
											<td></td>
											<td></td>
											<td>$functionCall</td>
											<td align='center'>".$back['line']."</td>
											<td>".basename($back['file'])."</td>
											<td></td>
										</tr>";
									}
								}
								$i++;
							}
						}
						$i++;
					}
					print "</table>";
					print "</div>";
				}
				$traceback = $this->getTrace();
				print "<div style='font-size:12px; margin: 5px; border:1px solid #333333; background: #676767; font-family: Lucida Console; margin:10px; color: white; padding: 2px' align='left'>
				<pre style='font-family: Lucida Console; margin:5px; color: white; font-size: 12px; text-align:left'>";
				if(count($this->extendedBacktrace)>0){
					$traceback = array_merge($this->extendedBacktrace, $traceback);
				}
				if(strpos($this->getFile(), "apps")){
					$firstLine = array(array(
						'file' => $this->getFile(),
						'line' => $this->getLine()
					));
					$traceback = array_merge($firstLine, $traceback);
				}
				foreach($traceback as $trace){
					if(isset($trace['file'])){
						if(isset($_SERVER['DOCUMENT_ROOT'])){
							$rfile = str_replace($_SERVER['DOCUMENT_ROOT'], "", $trace['file']);
						} else {
							$rfile = $trace['file'];
						}
						print $rfile." (".$trace['line'].")\n";
						if(strpos($trace['file'], "apps")){
							$file = $trace['file'];
							$line = $trace['line'];
							print "</pre><strong>La excepci&oacute;n se ha generado en el archivo '$rfile' en la l&iacute;nea '$line':</strong><br>";
							print "<div style='color: #000000; margin: 10px; border: 1px solid #333333; background: #FFFFFF; padding:0px'><table cellspacing='0' cellpadding='0' width='100%'>";
							$lines = file($file);
							$eline = $line;
							for($i =(($eline-4)<0 ? 0: $eline-4);$i<=($eline+2>count($lines)-1?count($lines)-1:$eline+2);$i++){
								$cline = str_replace("\t", "&nbsp;", htmlentities($lines[$i], ENT_NOQUOTES));
								if($i==$eline-1){
									print "<tr><td width='30' style='background:#eaeaea; font-size:12px'><strong>".($i+1).".</strong></td><td><div style='background: #FFDDDD;font-family: Lucida Console; font-size:13px; margin:0px; padding:0px'><strong>$cline</strong></div></td></tr>\n";
								} else {
									print "<tr><td style='background:#eaeaea; font-size:12px'><strong>".($i+1).".</strong></td><td style='font-family: Lucida Console; font-size:13px;'>&nbsp;$cline</td></tr>";
								}
							}
							print "</table></div><pre style='font-family: Lucida Console; margin:10px; color: white;'>";
						}
					}
				}
				print "</div>";


				$debugMemory = Debug::getMemory();
				if(count($debugMemory)>0){
					print "<div class='debugInformation'>\n";
					print "<strong>Datos de la Memoria:</strong>";
					print "<table cellspacing='0' width='100%' align='center'>
						<thead>
							<th>#</th>
							<th>Variable</th>
							<th>Valor</th>
						</thead>\n";
					$i = 1;
					foreach($debugMemory as $varname => $value){
						print "<tr>
							<td>$i</td>
							<td>$varname</td>
							<td>".htmlentities($value)."</td>
						</tr>";
						$i++;
					}
					print "</table></div>";
				}

				/**
				 * Imprime informacion extra de la excepcion si esta disponible
				 */
				if(method_exists($this, "getExceptionInformation")){
					print $this->getExceptionInformation();
				}

				/**
				 * Imprime los datos de entrada
				 */
				if(count($_POST+$_GET)>1){
					print "<div class='debugInformation'>\n";
					print "<strong>Datos de Entrada:</strong>";
					print "<table cellspacing='0' width='100%' align='center'>
						<thead>
							<th>Tipo</th>
							<th>Nombre</th>
							<th>Valor</th>
							<th>Tipo de Dato PHP</th>
					</thead>\n";
					unset($_GET['_url']);
					foreach($_GET as $key => $value){
						$type = gettype($value);
						if(is_array($value)){
							$value = print_r($value, true);
						}
						print "<tr bgcolor='#ffffff'>
							<td align='center'>GET</td>
							<td>$key</td>
							<td>$value</td>
							<td>$type</td>
						</tr>";
					}
					foreach($_POST as $key => $value){
						$type = gettype($value);
						if(is_array($value)){
							$value = print_r($value, true);
						}
						print "<tr bgcolor='#ffffff'>
							<td align='center'>POST</td>
							<td>$key</td>
							<td>$value</td>
							<td>$type</td>
						</tr>";
					}
					print "</table>";
					print "</div>";
				}

				print "<div style='font-size:12px; margin: 0px 15px 0px 15px; padding: 5px; border:1px solid #969696; background: #f2f2f2;' align='left'>";
				print "<i><strong>Informaci&oacute;n Adicional:</strong></i><br>";
				print "<div style='padding: 5px'>";
				print "<strong>Versi&oacute;n Framework:</strong> ".Core::FRAMEWORK_VERSION."<br>";
				print "<strong>Nombre de la Instancia:</strong> ".$instanceName."<br>";
				print "<strong>Fecha del Sistema:</strong> ".date("r")."<br>";
				print "<strong>Aplicaci&oacute;n actual:</strong> ".Core::getActiveApplication()."<br>";
				print "<strong>Entorno actual:</strong> ".$config->application->mode."<br>";
				$url = Router::getApplication()."/".Router::getController()."/".Router::getAction();
				print "<strong>Ubicaci&oacute;n actual:</strong> ".$url."<br>";
				print "<strong>Modelos Cargados:</strong> ".join(", ", array_keys(EntityManager::getEntities()))."<br>";
				if(isset($_SESSION['KMOD'][$instanceName][$active_app])){
					print "<strong>Modulos Cargados:</strong> ".join(", ", $_SESSION['KMOD'][$instanceName][$active_app])."<br>";
				}
				if(isset($_SESSION['KPC'][$instanceName][$active_app])){
					print "<strong>Plugins Cargados:</strong> ".join(", ", $_SESSION['KPC'][$instanceName][$active_app])."<br>";
				}
				if(isset($_SESSION['session_data'])){
					if(is_array($_SESSION['session_data'])){
						print "<strong>Datos de Session:</strong> ".join(", ", $_SESSION['session_data'])."<br>";
					} else {
						print "<strong>Datos de Session:</strong> ".print_r(unserialize($_SESSION['session_data']), 1)."<br>";
					}
				}
				print "<strong>Memoria Utilizada:</strong> ".(Helpers::toHuman(memory_get_peak_usage(true)))."<br>";
				print "<strong>Memoria Actual:</strong> ".(Helpers::toHuman(memory_get_usage()))."<br>";
				print "<strong>Tiempo empleado para atender la petici&oacute;n:</strong> ".(round($requestTime-$_SERVER['REQUEST_TIME'], 3))." segs <br>";
				print "</div></div>";
			} else {
				$traceback = $this->getTrace();
				if(count($this->extendedBacktrace)>0){
					$traceback = array_merge($this->extendedBacktrace, $traceback);
				}
				print "<pre style='font-family: Lucida Console; margin: 10px; border:1px solid #969696; background: #fafafa; font-size:12px'><span style='font-family: Lucida Console;font-size:11px'><b>Backtrace:</b></span>\n";
				$i = 0;
				foreach($traceback as $trace){
					if(isset($trace['file'])){
						$file = str_replace($_SERVER['DOCUMENT_ROOT'], "", $trace['file']);
					} else {
						$file = "internal-function ";
						$trace['line'] = 0;
					}
					if(!isset($trace['class'])){
						$trace['class'] = "";
						$trace['type'] = "";
					}
					if(!isset($trace['function'])){
						$trace['function'] = "";
					}
					print "#$i $file -&gt; {$trace['class']}{$trace['type']}{$trace['function']} ({$trace['line']})\n";
					$i++;
				}
				print "</pre>";
			}
		} else {
			if(isset($config->application->debug)&&$config->application->debug==true){
				/**
				 * Imprime informacion extra de la excepcion si esta disponible
				 */
				if(method_exists($this, "getExceptionInformation")){
					print $this->getExceptionInformation();
				}
			}
		}
		print "</div>";
	}

	/**
	 * Genera la salida de la excepcion en XML
	 *
	 */
	public function showMessageAsXML(){
		if(Session::isStarted()==false){
			Session::startSession();
			Core::setInstanceName();
		}
		$instanceName = Core::getInstanceName();
		$xml = new DOMDocument('1.0', 'UTF-8');
		$root = $xml->createElement('exception');
		$xml->appendChild($root);

		//Nombre de la Instancia
		$additionalInfo = $xml->createElement('additional-info');
		$node = $xml->createElement('instance-name', $instanceName);
		$additionalInfo->appendChild($node);

		//Aplicación
		$node = $xml->createElement('application', Router::getApplication());
		$additionalInfo->appendChild($node);

		//Timestamp
		$node = $xml->createElement('timestamp', date('r'));
		$additionalInfo->appendChild($node);

		//Version del Framework
		$node = $xml->createElement('framework-version', Core::FRAMEWORK_VERSION);
		$additionalInfo->appendChild($node);

		//BackTrace
		$backtrace = $xml->createElement('backtrace');
		foreach($this->getTrace() as $trace){
			$nodeTrace = $xml->createElement('trace');
			if(isset($trace['file'])){
				if(isset($_SERVER['DOCUMENT_ROOT'])){
					$fileTrace = $xml->createElement('file', str_replace($_SERVER['DOCUMENT_ROOT'], '', $trace['file']));
				} else {
					$fileTrace = $xml->createElement('file', $trace['file']);
				}
				$nodeTrace->appendChild($fileTrace);
			}
			if(isset($trace['line'])){
				$lineTrace = $xml->createElement('line', $trace['line']);
				$nodeTrace->appendChild($lineTrace);
			}
			if(isset($trace['class'])){
				$classTrace = $xml->createElement('class', $trace['class']);
				$nodeTrace->appendChild($classTrace);
			}
			if(isset($trace['function'])){
				$functionTrace = $xml->createElement('function', $trace['function']);
				$nodeTrace->appendChild($functionTrace);
			}
			if(isset($trace['type'])){
				$typeTrace = $xml->createElement('type', $trace['type']);
				$nodeTrace->appendChild($typeTrace);
			}
			if(isset($trace['args'])){
				$argsTrace = $xml->createElement('arguments');
				foreach($trace['args'] as $number => $arg){
					$argTrace = $xml->createElement('argument');
					$numArgTrace = $xml->createElement('number', $number);
					$dataArgTrace = $xml->createElement('data', serialize($arg));
					$argTrace->appendChild($numArgTrace);
					$argTrace->appendChild($dataArgTrace);
					$argsTrace->appendChild($argTrace);
				}
				$nodeTrace->appendChild($argsTrace);
			}

			//Trace
			$backtrace->appendChild($nodeTrace);
		}

		//headers
		$headersNode = $xml->createElement('http-headers');
		foreach($_SERVER as $key => $header){
			if(substr($key, 0, 5)=='HTTP_'){
				$key = str_replace('_', ' ', substr($key, 5));
				$key = str_replace(' ', '-', ucwords(strtolower($key)));
				$headerNode = $xml->createElement('header');
				$keyNode = $xml->createElement('key', $key);
				$valueNode = $xml->createElement('value', $header);
				$headerNode->appendChild($keyNode);
				$headerNode->appendChild($valueNode);
				$headersNode->appendChild($headerNode);
			}
		}

		//User Input
		$userInputNode = $xml->createElement('user-input');
		$postDataNode = $xml->createElement('post-data');
		$queryDataNode = $xml->createElement('query-data');
		foreach($_POST as $key => $value){
			$postNode = $xml->createElement('post-data');
			$keyNode = $xml->createElement('key', $key);
			$valueNode = $xml->createElement('value', serialize($value));
			$postNode->appendChild($keyNode);
			$postNode->appendChild($valueNode);
			$postDataNode->appendChild($postNode);
		}
		foreach($_GET as $key => $value){
			$queryNode = $xml->createElement('query-data');
			$keyNode = $xml->createElement('key', $key);
			$valueNode = $xml->createElement('value', serialize($value));
			$queryNode->appendChild($keyNode);
			$queryNode->appendChild($valueNode);
			$queryDataNode->appendChild($queryNode);
		}
		$userInputNode->appendChild($queryDataNode);
		$userInputNode->appendChild($postDataNode);

		//Tipo de Excepcion
		$node = $xml->createElement('type', get_class($this));
		$root->appendChild($node);

		//Codigo de la excepcion
		$node = $xml->createElement('code', $this->getCode());
		$root->appendChild($node);

		//Descripción de Excepcion
		$message = preg_replace('/[ \t]+/', ' ', html_entity_decode(str_replace("\n", '', $this->getMessage()), ENT_NOQUOTES, "UTF-8"));
		$node = $xml->createElement('message', $message);
		$root->appendChild($node);

		//Archivo
		if(isset($_SERVER['DOCUMENT_ROOT'])){
			$file = $xml->createElement('file', str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->getFile()));
		} else {
			$file = $xml->createElement('file', $this->getFile());
		}
		$root->appendChild($file);

		//Linea
		$node = $xml->createElement('line', $this->getLine());
		$root->appendChild($node);

		$root->appendChild($additionalInfo);
		$root->appendChild($backtrace);
		$root->appendChild($headersNode);
		$root->appendChild($userInputNode);
		return $xml->saveXML();
	}

	/**
	 * Devuelve el mensaje de la excepcion listo para salida a consola
	 *
	 * @return string
	 */
	public function getConsoleMessage(){
		return html_entity_decode($this->getMessage(), ENT_COMPAT, 'UTF-8');
	}

}
