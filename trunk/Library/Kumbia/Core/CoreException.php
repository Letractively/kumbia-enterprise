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
 * @version 	$Id$
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
	 * Indica si la excepcion se generó remotamente
	 *
	 * @var boolean
	 */
	private $_isRemote = false;

	/**
	 * Establece el actor que generó la excepción remota
	 *
	 * @var string
	 */
	private $_remoteActor = '';

	/**
	 * Remote backtrace
	 *
	 * @var array
	 */
	private $_remoteBacktrace = array();

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
	 * Establece si la excepción se generó remotamente
	 *
	 * @param bool $remote
	 */
	public function setRemote($remote){
		$this->_isRemote = $remote;
	}

	/**
	 * Establece el actor que genero la excepción
	 *
	 * @param string $actor
	 */
	public function setRemoteActor($actor){
		$this->_remoteActor = $actor;
	}

	/**
	 * Establece el sistema de traza remoto
	 *
	 * @param array $trace
	 */
	public function setRemoteTrace($trace){
		$this->_remoteBacktrace = $trace;
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

		//Agrega el estilo
		Tag::stylesheetLink('exception');

		//Titulo de la pantalla
		Tag::setDocumentTitle(get_class($this).' - Kumbia Enterprise Framework');

		$file = $this->getSafeFile();
		print "\n<div class='exceptionContainer'>\n";
		$message = "<div class='exceptionDescription'>";
		if($this->_isRemote==true){
			$message.= "Remote &gt; ";
		}
		$message.= get_class($this).": $this->message ({$this->getCode()})<br>
		<span class='exceptionLocation'>En el archivo <i>{$file}</i> en la línea: <i>{$this->getLine()}</i></div>";
		print $message;
		$config = CoreConfig::readAppConfig();
		$activeApp = Router::getApplication();
		if($this->show_trace==true){
			if(isset($config->application->debug)&&$config->application->debug==true){
				$requestTime = microtime(true);
				$debugMessages = Debug::getMessages();
				if(count($debugMessages)>0){
					print "<div class='debugInformation'>\n";
					print "<strong>Datos de Debug:</strong>";
					print "<table cellspacing='0' width='100%' align='center'>
						<thead>
							<th class='debugThOdd'>#</th>
							<th class='debugThEven'>Valor</th>
							<th class='debugThOdd'>Método/Función</th>
							<th class='debugThEven'>Línea</th>
							<th class='debugThOdd'>Archivo</th>
							<th class='debugThEven'>Tiempo</th>
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
				print "<div class='exceptionBacktraceContainer' align='left'>
				<pre class='exceptionBacktracePre'>";

				//Imprimir Backtrace Remoto
				if($this->_isRemote==true){
					$color = '#151515';
					if(count($this->_remoteBacktrace)){
						print "<pre class='exceptionRemoteContainer'>";
						print "<div class='exceptionRemoteTitle'>Remote Backtrace <span class='exceptionActor'>(Actor: ".$this->_remoteActor.")</span></div>";
						foreach($this->_remoteBacktrace as $remoteTrace){
							if(isset($remoteTrace['file'])){
								print "<div class='exceptionRemoteTrace'>{$remoteTrace['file']} <span class='exceptionRemoteLine'>({$remoteTrace['line']})</span></div>";
							}
						}
						print "</pre>";
					}
				}

				if(count($this->extendedBacktrace)>0){
					$traceback = array_merge($this->extendedBacktrace, $traceback);
				}
				if(strpos($this->getFile(), 'apps')){
					$firstLine = array(array(
						'file' => $this->getFile(),
						'line' => $this->getLine()
					));
					$traceback = array_merge($firstLine, $traceback);
				}
				foreach($traceback as $trace){
					if(isset($trace['file'])){
						$rfile = self::getSafeFileName($trace['file']);
						print $rfile." <span class='exceptionLine'>(".$trace['line'].")</span>\n";
						if(strpos($trace['file'], "apps")){
							$file = $trace['file'];
							$line = $trace['line'];
							print "</pre><span class='exceptionLineNote'>La excepción se ha generado en el archivo '$rfile' en la línea '$line':</span><br/>";
							print "<div class='exceptionFileViewver'><table cellspacing='0' cellpadding='0' width='100%'>";
							$lines = file($file);
							$eline = $line;
							$className = 'exceptionLineNotActiveOdd';
							for($i =(($eline-4)<0 ? 0: $eline-4);$i<=($eline+2>count($lines)-1?count($lines)-1:$eline+2);$i++){
								$cline = str_replace("\t", "&nbsp;", htmlentities($lines[$i]));
								if($i==$eline-1){
									print "<tr><td width='30' class='exceptionLineTd'>".($i+1).".</td>
									<td><div  class='exceptionLineActive'>&nbsp;<strong>";
									print $cline;
									print "</strong></div></td></tr>\n";
								} else {
									print "<tr><td class='exceptionLineTd'>".($i+1).".</td>
									<td class='$className'>&nbsp;";
									print $cline;
									print "</td></tr>";
								}
								if($className=='exceptionLineNotActiveOdd'){
									$className = 'exceptionLineNotActiveEven';
								} else {
									$className = 'exceptionLineNotActiveOdd';
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
							<th class='debugThOdd'>#</th>
							<th class='debugThEven'>Variable</th>
							<th class='debugThOdd'>Valor</th>
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
				if(method_exists($this, 'getExceptionInformation')){
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
							<th class='debugThOdd'>Tipo</th>
							<th class='debugThEven'>Nombre</th>
							<th class='debugThOdd'>Valor</th>
							<th class='debugThEven'>Tipo de Dato PHP</th>
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

				print "<div class='exceptionAditionalInfo' align='left'>";
				print "<i><strong>Información Adicional:</strong></i><br>";
				print "<div style='padding: 5px'>";
				print "<table cellspacing='0' width='100%' cellpadding='3'>
				<tr class='rowInfoEven'>
					<td align='right' width='200'><strong>Versión Framework:</strong></td>
					<td> ".Core::FRAMEWORK_VERSION."</td>
				</tr>
				<tr class='rowInfoOdd'>
					<td align='right'><strong>Nombre de la Instancia:</strong></td>
					<td>".$instanceName."</td>
				</tr>
				<tr class='rowInfoEven'>
					<td align='right'><strong>Fecha del Sistema:</strong></td
					><td>".date("r")."</td>
				</tr>
				<tr class='rowInfoOdd'>
					<td align='right'><strong>Aplicación actual:</strong></td>
					<td>".Router::getApplication()."</td>
				</tr>
				<tr class='rowInfoEven'>
					<td align='right'><strong>Entorno actual:</strong></td>
					<td>".$config->application->mode."</td>
				</tr>";
				$url = Router::getApplication()."/".Router::getController()."/".Router::getAction();
				print "
				<tr class='rowInfoOdd'>
					<td align='right'><strong>Ubicación actual:</strong></td>
					<td>".$url."</td>
				</tr>
				<tr class='rowInfoEven'>
					<td align='right'><strong>Modelos Cargados:</strong></td>
					<td>".join(", ", array_keys(EntityManager::getEntities()))."</td>
				</tr>";
				if(isset($_SESSION['KMOD'][$instanceName][$activeApp])){
					print "<tr class='rowInfoOdd'>
						<td align='right'><strong>Modulos Cargados:</strong></td>
						<td>".join(", ", $_SESSION['KMOD'][$instanceName][$activeApp])."</td>
					</tr>";
				}
				if(isset($_SESSION['KPC'][$instanceName][$activeApp])){
					print "<tr class='rowInfoEven'>
						<td align='right'><strong>Plugins Cargados:</strong></td>
						<td>".join(", ", $_SESSION['KPC'][$instanceName][$activeApp])."</td>
					</tr>";
				}
				if(isset($_SESSION['session_data'])){
					if(is_array($_SESSION['session_data'])){
						print "<tr class='rowInfoOdd'>
							<td align='right'><strong>Datos de Session:</strong></td
							><td>".join(", ", $_SESSION['session_data'])."</td>
						</tr>";
					} else {
						print "<tr class='rowInfoOdd'>
							<td align='right'><strong>Datos de Session:</strong></td>
							<td>".print_r(unserialize($_SESSION['session_data']), 1)."</td>
						</tr>";
					}
				}
				print "<tr class='rowInfoEven'>
					<td align='right'><strong>Memoria Utilizada:</strong></td>
					<td>".(Helpers::toHuman(memory_get_peak_usage(true)))."</td>
				</tr>
				<tr class='rowInfoOdd'>
					<td align='right'><strong>Memoria Actual:</strong></td>
					<td>".(Helpers::toHuman(memory_get_usage()))."</td>
				</tr>
				<tr class='rowInfoEven'>
					<td align='right'><strong>Tiempo empleado para<br/>atender la petición:</strong></td>
					<td>".(round($requestTime-$_SERVER['REQUEST_TIME'], 3))." segs </td>
				</tr></table>";
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
	 * Genera una presentación sencilla para excepciones en la inicialización
	 *
	 * @param Exception $e
	 */
	public static function showSimpleMessage($e){
		//Agrega el estilo
		Tag::stylesheetLink('exception');

		//Titulo de la pantalla
		Tag::setDocumentTitle(get_class($e).' - Kumbia Enterprise Framework');

		ob_start();
		$file = CoreException::getSafeFileName($e->getFile());
		print "\n<div class='exceptionContainer'>\n";
		$message = "<div class='exceptionDescription'>".
		get_class($e).": {$e->getMessage()} ({$e->getCode()})<br>
		<span class='exceptionLocation'>En el archivo <i>{$file}</i> en la línea: <i>{$e->getLine()}</i></div>";
		print $message;

		print "<div class='exceptionBacktraceSimple'>";
		print '<b>Backtrace:</b><br/>'."\n";
		foreach($e->getTrace() as $debug){
			if(isset($debug['file'])){
				print CoreException::getSafeFileName($debug['file']).' ('.$debug['line'].") <br/>\n";
			}
		}
		print "</div>";
		print "</div>";
		View::setContent(ob_get_contents());
		ob_end_clean();
		View::xhtmlTemplate('white');

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

		//Mostrar datos recibidos por POST
		foreach($_POST as $key => $value){
			$postNode = $xml->createElement('post-data');
			$keyNode = $xml->createElement('key', $key);
			$valueNode = $xml->createElement('value', serialize($value));
			$postNode->appendChild($keyNode);
			$postNode->appendChild($valueNode);
			$postDataNode->appendChild($postNode);
		}

		//Mostrar datos recibidos por GET
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
	 * Obtiene el nombre del archivo que generó la excepción eliminando
	 * la ruta absoluta que muestre su ubicación real
	 *
	 * @return string
	 */
	public function getSafeFile(){
		return self::getSafeFileName($this->getFile());
	}

	/**
	 *
	 * Obtiene el nombre del archivo que generó la excepción eliminando
	 * la ruta absoluta que muestre su ubicación real
	 *
	 * @param string $filePath
	 * @return string
	 */
	public static function getSafeFileName($filePath){
		if(isset($_SERVER['DOCUMENT_ROOT'])){
			return str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
		} else {
			return str_replace(getcwd(), '', $filePath);
		}
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
