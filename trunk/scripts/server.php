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
 * @package		HurricaneServer
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * Excepción generada por HurricaneServer
 *
 */
class HurricaneServerException extends Exception {

}

/**
 * HurricaneServer
 *
 * HurricaneServer está escrito en PHP y proporciona servicios básicos de HTTP
 * para probar y desarrollar en Kumbia Enterprise Framework
 *
 */
class HurricaneServer {

	/**
	 * Dirección donde va a escuchar el servidor
	 *
	 * @var string
	 */
	private static $_address = '0.0.0.0';

	/**
	 * Puerto TCP donde va a escuchar el servidor
	 *
	 * @var string
	 */
	private static $_port = 8080;

	/**
	 * Socket TCP del servidor
	 *
	 * @var resource
	 */
	private static $_socket;


	/**
	 * Lista de sockets activos
	 *
	 * @var array
	 */
	private static $_connections = array();

	/**
	 * PHP5.3 Garbage Collector Enabled
	 *
	 * @var boolean
	 */
	private static $_gcEnabled = false;

	/**
	 * Ultima aplicacion en donde se ejecutó una petición
	 */
	private static $_lastApplication = '';

	/**
	 * Indica si ya se inicializó el framework
	 *
	 * @var boolean
	 */
	private static $_frameworkInitialized = false;

	/**
	 * HTTP Status General
	 *
	 * @var string
	 */
	private static $_responseStatus;

	/**
	 * Contenido y tamaño de la salida dinámica
	 *
	 * @var array
	 */
	private static $_dynamic = array();

	/**
	 * Encabezados HTTP de respuesta base
	 *
	 * @var array
	 */
	private static $_responseHeaders = array(
		'Server'  => 'HurricaneServer/0.1'
	);

	/**
	 * MIME types base
	 *
	 * @var array
	 */
	private static $_mimeTypes = array(
		'html' => 'text/html; charset=UTF-8',
		'htm' => 'text/html; charset=UTF-8',
		'css' => 'text/css',
		'js' => 'application/x-javascript',
		'gif' => 'image/gif',
		'png' => 'image/png',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'ico' => 'image/x-icon',
		'swf' => 'application/x-shockwave-flash',
		'xhtml' => 'application/xhtml+xml'
	);

	/**
	 * Asociación de encabezados de la petición con superglobal $_SERVER
	 *
	 * @var array
	 */
	private static $_serverRelationship = array(
		'User-Agent' => 'HTTP_USER_AGENT',
		'X-Requested-With' => 'HTTP_X_REQUESTED_WITH',
		'Accept' => 'HTTP_ACCEPT',
		'Accept-Encoding' => 'HTTP_ACCEPT_ENCODING',
		'Accept-Language' => 'HTTP_ACCEPT_LANGUAGE',
		'Referer' => 'HTTP_REFERER'
	);

	/**
	 * Inicializa el servidor web
	 *
	 * @return null
	 */
	public static function initialize(){
		error_reporting(E_ALL | E_NOTICE | E_STRICT);
		set_time_limit(0);
		ini_alter('track_errors', true);
		self::_bindAddress();
		self::_loadComponents();
		self::_setTimezone();
		if(version_compare(PHP_VERSION, '5.3', '>=')){
			gc_enable();
			self::$_gcEnabled = true;
		}
	}

	/**
	 * Establece la zona horaria de la aplicación
	 *
	 */
	private static function _setTimezone(){
		date_default_timezone_set('America/Bogota');
	}

	/**
	 * Carga componentes del framework para que esten disponibles en cualquier momento
	 *
	 */
	private static function _loadComponents(){
		require 'Library/Kumbia/Autoload.php';
		require 'Library/Kumbia/Object.php';
		require 'Library/Kumbia/Core/Core.php';
		require 'Library/Kumbia/Session/Session.php';
		require 'Library/Kumbia/Config/Config.php';
		require 'Library/Kumbia/Core/Config/CoreConfig.php';
		require 'Library/Kumbia/Core/Type/CoreType.php';
		require 'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
		require 'Library/Kumbia/Router/Router.php';
		require 'Library/Kumbia/Plugin/Plugin.php';
		require 'Library/Kumbia/Registry/Memory/MemoryRegistry.php';
		require 'Library/Kumbia/Extensions/Extensions.php';
		require 'Library/Kumbia/CommonEvent/CommonEvent.php';
		require 'Library/Kumbia/Dispatcher/Dispatcher.php';
		require 'Library/Kumbia/EntityManager/EntityManager.php';
		require 'Library/Kumbia/Transactions/TransactionManager.php';
		require 'Library/Kumbia/Db/Loader/DbLoader.php';
		require 'Library/Kumbia/Db/DbBase.php';
		require 'Library/Kumbia/ActiveRecord/Base/ActiveRecordBase.php';
		require 'Library/Kumbia/Security/Security.php';
		require 'Library/Kumbia/Facility/Facility.php';
		require 'Library/Kumbia/View/View.php';
		require 'Library/Kumbia/i18n/i18n.php';
		require 'Library/Kumbia/Controller/ControllerResponse.php';
		require 'Library/Kumbia/Utils/Utils.php';
	}

	/**
	 * Registra funciones de terminación y señales de cerrar
	 *
	 */
	private static function _registerShutdownSignals(){

		//Apagar servidor correctamente
		register_shutdown_function(array('HurricaneServer', 'shutdownServer'));

	}

	/**
	 * Abre el puerto y empieza a escuchar peticiones en él
	 *
	 * @access public
	 * @static
	 */
	private static function _bindAddress(){

		if(!extension_loaded('sockets')){
			throw new HurricaneServerException('Debe cargar la extensión de PHP llamada php_sockets');
		}

		self::$_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(self::$_socket===false){
			throw new HurricaneServerException('socket_create() failed: reason: '.socket_strerror(socket_last_error()));
		}

		if(!socket_set_option(self::$_socket, SOL_SOCKET, SO_REUSEADDR, 1)){
			throw new HurricaneServerException(socket_strerror(socket_last_error(self::$_socket)));
		}

		if(socket_bind(self::$_socket, self::$_address, self::$_port)===false){
			throw new HurricaneServerException('socket_bind() failed: reason: '.socket_strerror(socket_last_error(self::$_socket)));
		}

		if(socket_listen(self::$_socket, 128)===false){
			throw new HurricaneServerException('socket_listen() failed: reason: '.socket_strerror(socket_last_error(self::$_socket)));
		}

		//Mensaje de bienvenida
		echo 'HurricaneServer escuchando en '.self::$_address.' '.self::$_port, "\n";

		//Registra SIGNAL handlers para la terminación correcta de procesos
		self::_registerShutdownSignals();

	}

	/**
	 * Apaga el servidor cerrando sockets abiertos de forma segura
	 *
	 * @access 	public
	 * @param 	int $signal
	 * @static
	 */
	public static function shutdownServer($signal=null){
		@socket_close(self::$_socket);
	}

	public static function eventAccept(){

	}

	/**
	 * Acepta peticiones en la cola y genera las respuestas
	 *
	 */
	public static function serverAccept(){
		$initialTime = time();
		do {
			$clientSocket = socket_accept(self::$_socket);
			if($clientSocket===false){
				throw new HurricaneServerException("socket_accept() failed: reason: ".socket_strerror(socket_last_error($sock)));
				break;
			}
			$time = microtime(true);
			$i = 0;
			$line = '';
			$rawResponse = '';
			$requestHeaders = array();
			while(true){
				unset($buffer);
				$buffer = socket_read($clientSocket, 1, PHP_BINARY_READ);
				if($buffer=="\n"){
					if($line=="\r"){
						break;
					}
					if($i==0){
						$requestHttpUri = explode(' ', $line);
						unset($fline);
					} else {
						$fline = explode(': ', $line, 2);
						if(count($fline)==2){
							$requestHeaders[$fline[0]] = substr($fline[1], 0, strlen($fline[1])-1);
						}
						unset($fline);
					}
					$rawResponse.=$line;
					unset($line);
					$line = '';
					$i++;
				} else {
					$line.=$buffer;
				}
			}

			foreach($_GET as $name => $value){
				unset($_GET[$name]);
				unset($name);
				unset($value);
			}

			foreach($_POST as $name => $value){
				unset($_POST[$name]);
				unset($name);
				unset($value);
			}

			foreach($_REQUEST as $name => $value){
				unset($_REQUEST[$name]);
				unset($name);
				unset($value);
			}

			$_GET = array();
			$_POST = array();
			$_REQUEST = array();

			//Query-String
			$ppos = strpos($requestHttpUri[1], '?');
			if($ppos!==false){
				$uri = substr($requestHttpUri[1], 0, $ppos);
				$queryString = substr($requestHttpUri[1], $ppos+1);
				foreach(explode('&', $queryString) as $variable){
					$pvariable = explode('=', $variable);
					if(isset($pvariable[1])){
						$_GET[$pvariable[0]] = urldecode($pvariable[1]);
					} else {
						$_GET[$pvariable[0]] = null;
					}
					unset($pvariable);
					unset($variable);
				}
				unset($queryString);
			} else {
				$uri = $requestHttpUri[1];
			}
			unset($ppos);

			//Leer cuerpo POST del mensaje
			if($requestHttpUri[0]=='POST'){
				$requestLength = $requestHeaders['Content-Length'];
				$postBody = '';
				for($i=0;$i<$requestLength;++$i){
					$postBody.=socket_read($clientSocket, 1, PHP_BINARY_READ);
				}
				foreach(explode('&', $postBody) as $variable){
					$pvariable = explode('=', $variable);
					if(isset($pvariable[1])){
						$_POST[$pvariable[0]] = urldecode($pvariable[1]);
					} else {
						$_POST[$pvariable[0]] = null;
					}
					unset($pvariable);
					unset($variable);
				}
				unset($postBody);
				unset($requestLength);
			}

			//Crear $_REQUEST
			foreach($_POST as $name => $value){
				$_REQUEST[$name] = $value;
				unset($name);
				unset($value);
			}
			foreach($_GET as $name => $value){
				$_REQUEST[$name] = $value;
				unset($name);
				unset($value);
			}

			//Alimentar $_SERVER
			foreach(self::$_serverRelationship as $httpHeader => $serverIndex){
				if(isset($requestHeaders[$httpHeader])){
					$_SERVER[$serverIndex] = $requestHeaders[$httpHeader];
				} else {
					unset($_SERVER[$serverIndex]);
				}
			}

			$requestTime = time();
			unset($_SERVER['REQUEST_METHOD']);
			unset($_SERVER['REQUEST_TIME']);
			$_SERVER['REQUEST_METHOD'] = $requestHttpUri[0];
			$_SERVER['REQUEST_TIME'] = $requestTime;

			if(socket_getpeername($clientSocket, $ipaddress, $port)!==false){
				$_SERVER['REMOTE_ADDR'] = $ipaddress;
				$_SERVER['REMOTE_PORT'] = $port;
				unset($ipaddress);
				unset($port);
			}

			unset(self::$_responseHeaders['Content-Length']);
			unset(self::$_responseHeaders['Content-Type']);
			unset(self::$_responseHeaders['Cache-Control']);
			unset(self::$_responseHeaders['Expires']);
			unset(self::$_responseHeaders['Date']);
			unset(self::$_responseHeaders['Last-Modified']);

			//Encabezados de salida
			self::$_responseHeaders['Date'] = date('r');

			$staticContent = false;
			$existsFile = file_exists('public'.$uri);
			if($existsFile){
				if(preg_match('/\.([a-z0-9]+)$/', $uri, $matches)){
					if($matches[1]!='php'){
						self::$_responseHeaders['Content-Length'] = filesize('public'.$uri);
						if(isset(self::$_mimeTypes[$matches[1]])){
							self::$_responseHeaders['Content-Type'] = self::$_mimeTypes[$matches[1]];
						} else {
							self::$_responseHeaders['Content-Type'] = 'text/plain';
						}
						self::$_responseHeaders['Last-Modified'] = date('r', filemtime('public'.$uri));
						self::$_responseHeaders['Expires'] = date('r', $initialTime+1728000);
						$staticContent = true;
					} else {
						self::_serveDynamicContent($uri);
					}
					unset($matches);
				}
			} else {
				unset($_GET['_url']);
				$_GET['_url'] = substr($uri, 1);
				self::_serveAppRequest();
			}

			//Escribir estado HTTP
			if(isset($requestHeaders['If-Modified-Since'])){
				$responseStatus = "HTTP/1.1 304 Not Modified\r\n";
			} else {
				$responseStatus = "HTTP/1.1 200 OK\r\n";
			}
			socket_write($clientSocket, $responseStatus, strlen($responseStatus));

			//Escribir encabezados de la respuesta
			foreach(self::$_responseHeaders as $headerName => $headerValue){
				$header = $headerName.': '.$headerValue."\r\n";
				socket_write($clientSocket, $header, strlen($header));
				unset($header);
				unset($headerName);
				unset($headerValue);
			}
			socket_write($clientSocket, "\r\n", 2);

			echo $requestHttpUri[0].' '.$requestHttpUri[1], "\n";

			if($staticContent==false){
				socket_write($clientSocket, self::$_dynamic['content'], self::$_dynamic['length']);
				unset(self::$_dynamic['content']);
				unset(self::$_dynamic['length']);
				$forkedChild = false;
			} else {
				if(!isset($requestHeaders['If-Modified-Since'])){
					self::_serveStaticContent($clientSocket, $uri, false);
				}
			}
			socket_close($clientSocket);

			echo $requestHttpUri[0].' '.$requestHttpUri[1], ' Mu=', memory_get_peak_usage(), ' Ti=', (microtime(true)-$time), "\n";

			//Collect cycles
			if(self::$_gcEnabled==true){
				if(($requestTime%10)==0){
					gc_collect_cycles();
				}
			} else {
				unset($rawResponse);
				unset($requestHeaders);
				unset($uri);
				unset($existsFile);
				unset($clientSocket);
				unset($responseStatus);
				unset($requestHttpUri);
				unset($staticContent);
				unset($time);
				unset($requestTime);
			}

		} while(true);

		socket_close($clientSocket);
		socket_close(self::$_socket);
	}

	/**
	 * Sirve un archivo estático que se encuentra en el public de manera segura
	 *
	 * @param socket $socketClient
	 * @param string $filePath
	 * @param int $processId
	 */
	private static function _serveStaticContent($socketClient, $filePath, $processId=false){
		$fp = fopen('public'.$filePath, 'r');
		while(!feof($fp)){
			socket_write($socketClient, fgetc($fp), 1);
		}
		fclose($fp);
		unset($fp);
		unset($filePath);
		if($processId!==false){
			posix_kill(posix_getpid(), SIGKILL);
		}
	}

	/**
	 * Sirve un contenido dinámico en una caja de arena
	 *
	 * @param string $uri
	 */
	private static function _serveDynamicContent($uri){
		self::$_responseHeaders['Content-Type'] = 'text/html; charset=UTF-8';
		self::$_responseHeaders['Cache-Control'] = 'no-cache';
		ob_start();
		include 'public'.$uri;
		self::$_dynamic['content'] = ob_get_contents();
		self::$_dynamic['length'] = i18n::strlen(self::$_dynamic['content']);
		self::$_responseHeaders['Content-Length'] = self::$_dynamic['length'];
		ob_end_clean();
		unset($uri);
	}

	/**
	 * Sirve una petición a una aplicación
	 *
	 * @return
	 */
	private static function _serveAppRequest(){
		self::$_responseHeaders['Content-Type'] = 'text/html; charset=UTF-8';
		self::$_responseHeaders['Cache-Control'] = 'no-cache';
		ob_start();
		try {
			Core::resetRequest();
			Router::handleRouterParameters();
			$activeApplication = Router::getApplication();
			if(self::$_lastApplication!=$activeApplication){
				Extensions::cleanExtensions();
				Extensions::loadBooteable();
				PluginManager::loadApplicationPlugins();
				Core::setTimeZone();
			}
			if(self::$_frameworkInitialized==false){
				Core::setIsHurricane(true);
				Core::setInitialPath(getcwd());
				Core::setInstanceName('');
				TransactionManager::initializeManager();
				Session::disableAutoStart(true);
				self::$_frameworkInitialized = true;
			}
			if(self::$_lastApplication!=$activeApplication){
				$config = CoreConfig::readAppConfig();
				Core::reloadMVCLocations();
				if(DbLoader::loadDriver()==false){
					return false;
				}
				$modelsDir = Core::getActiveModelsDir();
				EntityManager::initModelBase($modelsDir);
				if(isset($config->entities->autoInitialize)&&$config->entities->autoInitialize==false){
					EntityManager::setAutoInitialize(false);
					EntityManager::setModelsDirectory($modelsDir);
				} else {
					EntityManager::initModels($modelsDir);
				}
				Security::initAccessManager();
				self::$_lastApplication = $activeApplication;
				unset($modelsDir);
			}

			ob_start();
			$controller = Core::handleRequest();
			$controllerResponse = ControllerResponse::getInstance();
			if($controllerResponse->getResponseType()==ControllerResponse::RESPONSE_NORMAL){
				ob_end_clean();
				View::getContent();
			} else {
				ob_end_flush();
			}

			//Cierra transacciones pendientes
			TransactionManager::rollback(true);
			TransactionManager::collectTransactions();

			//Resetea a las conexión por defecto
			EntityManager::resetEntites();

		}
		catch(CoreException $e){
			try {
				Session::startSession();
				$exceptionHandler = Core::determineExceptionHandler();
				call_user_func_array($exceptionHandler, array($e, null));
			}
			catch(Exception $e){
				ob_start();
				echo get_class($e).': '.$e->getMessage()." ".$e->getFile()."(".$e->getLine().")";
				echo 'Backtrace', "\n";
				foreach($e->getTrace() as $debug){
					echo $debug['file'].' ('.$debug['line'].") <br/>\n";
				}
				View::setContent(ob_get_contents());
				ob_end_clean();
				View::xhtmlTemplate('white');
			}
		}
		catch(Exception $e){
			echo 'Exception: '.$e->getMessage();
			foreach(debug_backtrace() as $debug){
				echo $debug['file'].' ('.$debug['line'].") <br>\n";
			}
		}

		//Eliminar salidas que no se hayan terminado
		$obStatus = ob_get_status(true);
		$numObStatus = count($obStatus);
		if($numObStatus>1){
			for($i=0;$i<($numObStatus-1);$i++){
				ob_end_flush();
			}
		}

		//Cargar salida para su envio al socket
		self::$_dynamic['content'] = ob_get_contents();
		self::$_dynamic['length'] = i18n::strlen(self::$_dynamic['content']);
		self::$_responseHeaders['Content-Length'] = self::$_dynamic['length'];
		ob_end_clean();

		unset($obStatus);
		unset($numObStatus);
		unset($i);
	}

	/**
	 * Cambia un encabezado de la salida externamente
	 *
	 * @param string $name
	 * @param string $value
	 */
	public static function setHeader($name, $value){
		self::$_responseHeaders[$name] = $value;
	}

	/**
	 * Devuelve un encabezado previamente definido
	 *
	 * @param	string $name
	 * @return	null
	 */
	public static function getHeader($name){
		if(isset(self::$_responseHeaders[$name])){
			return self::$_responseHeaders[$name];
		} else {
			return null;
		}
	}

}

try {
	HurricaneServer::initialize();
	HurricaneServer::serverAccept();
}
catch(HurricaneServerException $e){
	echo $e;
}
