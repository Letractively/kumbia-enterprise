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
 * @copyright	Copyright (c) 2007-2007 Emilio Rafael Silveira Tovar (emilio.rst@gmail.com)
 * @license		New BSD License
 * @version 	$Id: Core.php 142 2009-04-23 18:33:59Z gutierrezandresfelipe $
 */

/**
 * Core
 *
 * Esta es la clase que integra todo el framework
 *
 * @category	Kumbia
 * @package		Core
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class Core {

	/**
	 * Version del Framework
	 *
	 */
	const FRAMEWORK_VERSION = '1.0.17GA';

	/**
	 * PATH donde esta instalada la instancia del framework
	 */
	private static $_instanceName = null;

	/**
	 * Almacena el ID del Cluster si aplica
	 *
	 * @var string
	 */
	private static $_clusterId = null;

	/**
	 * Nombre del controlador actual
	 *
	 * @var string
	 */
	static private $_controller;

	/**
	 * Nombre de la aplicacion activa
	 *
	 * @var string
	 */
	static private $_activeApp;

	/**
	 * Directorio de controladores activo
	 *
	 * @var string
	 */
	private static $_activeControllersDir;

	/**
	 * Directorio de modelos activo
	 *
	 * @var string
	 */
	private static $_activeModelsDir;

	/**
	 * Directorio de vistas activo
	 *
	 * @var string
	 */
	private static $_activeViewsDir;

	/**
	 * Facility Actual
	 *
	 * @var integer
	 */
	private static $_facility;

	/**
	 * Establece si el framework se encuentra en modo Test
	 *
	 * @var boolean
	 */
	private static $_testingMode = false;

	/**
	 * Indica si la aplicacion esta corriendo bajo IBM Websphere
	 *
	 * @var boolean
	 */
	private static $_isWebSphere = false;

	/**
	 * Inicializa el entorno de aplicacion
	 *
	 * @access public
	 * @static
	 */
	public static function initApplication(){

		/**
		 * @see Extensions
		 */
		self::requireFile('Extensions/Extensions');

		/**
		 * Carga las extensiones del boot.ini
		 */
		Extensions::loadBooteable();

		/**
		 * Carga los plug-in de la aplicacion actual
		 */
		PluginManager::loadApplicationPlugins();

		/**
		 * Establece el timezone del sistema
		 */
		self::setTimeZone();

	}

	/**
	 * Establecer el timezone para las fechas y horas
	 *
	 * @access public
	 * @param string $timezone
	 * @static
	 */
	static public function setTimeZone($timezone=''){
		if($timezone==''){
			$config = CoreConfig::getInstanceConfig();
			if(isset($config->core->timezone)){
				$timezone = $config->core->timezone;
			} else {
				$timezone = 'America/Bogota';
			}
		}
		if(date_default_timezone_set($timezone)==false){
			throw new CoreException('Timezone inv&aaacute;lido \''.$timezone.'\'');
		}
	}

	/**
	 * Obtiene el timezone actual
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	static public function getTimezone(){
		return date_default_timezone_get();
	}

	/**
	 * Inicializar el _INSTANCE_NAME
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	static public function setInstanceName(){

		if(self::$_instanceName!==null){
			return false;
		}

		/**
		 * Crear el _INSTANCE_NAME
		 */
		$deleteSessionCache = false;
		$path = substr(str_replace('/public/index.php', '', $_SERVER['PHP_SELF']), 1);
		if(isset($_SESSION['_INSTANCE_NAME'])){
			if($path!=$_SESSION['_INSTANCE_NAME']){
				$deleteSessionCache = true;
			}
		} else {
			/**
		 	 * Comprueba la version correcta del Framework, si es menor a 5.2 genera una excepción
			 */
			if(version_compare(PHP_VERSION, '5.2.0', '<')){
				$message = CoreLocale::getErrorMessage(-10, PHP_VERSION);
				throw new CoreException($message, -10);
			}
			/**
			 * Si el archivo public/temp no se puede escribir lanza una excepcion
			 */
			if(!is_writable('public/temp')){
				$message = CoreLocale::getErrorMessage(-11);
				throw new CoreException($message, -11);
			}
			$_SESSION['_INSTANCE_NAME'] = '';
		}

		/**
		 * Ejecutar onStartApplication y onChangeInstance
		 */
		$e = null;
		try {
			if($path!=$_SESSION['_INSTANCE_NAME']){
				self::runStartApplicationEvent();
				self::runChangeInstanceEvent();
			} else {
				if(!isset($_SESSION['_APPNAME'])||$_SESSION['_APPNAME']!=Router::getApplication()){
					self::runStartApplicationEvent();
					$_SESSION['_APPNAME'] = Router::getApplication();
				}
			}
		}
		catch(Exception $e){
			// Espera a que se defina _INSTANCE_NAME y lanza la excepcion
		}
		$_SESSION['_APPNAME'] = Router::getApplication();
		$_SESSION['_INSTANCE_NAME'] = $path;
		if($_SESSION['_INSTANCE_NAME']){
			self::$_instanceName = $_SESSION['_INSTANCE_NAME'];
		} else {
			self::$_instanceName = '';
		}
		if(is_object($e)){
			throw $e;
		}
		return true;
	}

	/**
	 * Devuelve el nombre de la instancia actual
	 *
	 * @return string
	 */
	public static function getInstanceName(){
		if(self::$_instanceName===null){
			return join(array_slice(explode('/' ,dirname($_SERVER['PHP_SELF'])),1,-1),"/");
		} else {
			return self::$_instanceName;
		}
	}

	/**
	 * Ejecuta el evento de inicializar la aplicacion
	 *
	 * @access public
	 * @static
	 */
	public static function runStartApplicationEvent(){
		PluginManager::notifyFromApplication('beforeStartApplication');
		if(class_exists('ControllerBase')){
			$controllerBase = new ControllerBase();
			if(method_exists($controllerBase, "onStartApplication")){
				$controllerBase->onStartApplication();
			}
		}
		PluginManager::notifyFromApplication('afterStartApplication');
	}

	/**
	 * Ejecuta el evento de cambiar de Instancia del Framework
	 *
	 * @access public
	 * @static
	 */
	public static function runChangeInstanceEvent(){
		PluginManager::notifyFromApplication('beforeChangeInstance');
		if(class_exists('ControllerBase')){
			$controllerBase = new ControllerBase();
			if(method_exists($controllerBase, 'onChangeInstance')){
				$controllerBase->onChangeApplication(self::getInstanceName());
			}
		}
		PluginManager::notifyFromApplication('afterChangeInstance');
	}

	/**
	 * Devuelve el PATH donde esta instalada la instancia despues del DOCUMENT ROOT
	 *
	 * @return string
	 */
	public static function getInstancePath(){
		$instance = self::getInstanceName();
		if($instance){
			return '/'.self::getInstanceName().'/';
		} else {
			return '/';
		}
	}

	/**
	 * Obtener el nombre de la aplicacion activa
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getActiveApplication(){
		return self::$_activeApp;
	}

	/**
	 * Función Principal donde se inicia el flujo de ejecucion
	 *
	 * @access public
	 * @return boolean
	 * @throws CoreException
	 * @static
	 */
	public static function main(){

		self::requireFile('CommonEvent/CommonEventManager');
		self::requireFile('Dispatcher/Dispatcher');
		self::requireFile('EntityManager/EntityManager');
		self::requireFile('Transactions/TransactionManager');
		self::requireFile('Db/Loader/DbLoader');
		self::requireFile('Db/DbBase');
		self::requireFile('ActiveRecord/Base/ActiveRecordBase');
		self::requireFile('Security/Security');
		self::requireFile('Facility/Facility');
		self::requireFile('View/View');
		self::requireFile('i18n/i18n');
		self::requireFile('Controller/ControllerResponse');
		self::requireFile('Utils/Utils');

		/**
		 * Rutas Base
		 */
		$config = CoreConfig::readFromActiveApplication('config.ini');

		//Aplicacion Activa
		self::$_activeApp = Router::getApplication();

		//Directorio de controladores Activo
		if(isset($config->application->controllersDir)){
			$controllersDir = 'apps/'.$config->application->controllersDir;
			self::$_activeControllersDir = "apps/".$config->application->controllersDir;
		} else {
			$controllersDir = 'apps/'.self::$_activeApp.'/controllers';
			self::$_activeControllersDir = 'apps/'.self::$_activeApp.'/controllers';
		}

		//Directorio de modelos activo
		if(isset($config->application->modelsDir)){
			$modelsDir = 'apps/'.$config->application->modelsDir;
			self::$_activeModelsDir = 'apps/'.$config->application->modelsDir;
		} else {
			$modelsDir = 'apps/'.self::$_activeApp.'/models';
			self::$_activeModelsDir = 'apps/'.self::$_activeApp.'/models';
		}

		//Directorio de Vistas Activo
		if(isset($config->application->viewsDir)){
			self::$_activeViewsDir = 'apps/'.$config->application->viewsDir;
		} else {
			self::$_activeViewsDir = 'apps/'.self::$_activeApp.'/views';
		}

		/**
		 * @see ControllerBase
		 */
		if(class_exists('ControllerBase', false)==false){
			require $controllersDir.'/application.php';
		}

		try {

			/**
			 * Iniciar el buffer de salida
			 */
			ob_start();

			/**
 	 	     * El driver de la BD es cargado segun lo que diga en config.ini
     	     */
			if(DbLoader::loadDriver()==false){
				return false;
			}

			/**
			 * Inicializa el modelo base
			 */
			EntityManager::initModelBase($modelsDir);
			if(isset($config->entities->autoInitialize)&&$config->entities->autoInitialize==false){
				EntityManager::setAutoInitialize(false);
				EntityManager::setModelsDirectory($modelsDir);
			} else {
				/**
				 * Los demas modelos estan en el directorio de modelos
				 */
				EntityManager::initModels($modelsDir);
			}

			/**
			 * Inicializa el administrador de transacciones
			 */
			TransactionManager::initializeManager();

			/**
		 	 * Inicializa el administrador de acceso
		 	 */
			Security::initAccessManager();

			/**
			 * Atiende la peticion
			 */
			$controller = self::handleRequest();

			/**
			 * Invoca el GarbageCollector
			 */
			if(isset($config->collector)){
				if(class_exists('GarbageCollector')==false){
					Core::requireFile('GarbageCollector/GarbageCollector');
				}
				if(isset($config->collector->probability)){
					GarbageCollector::setProbability($config->collector->probability);
				}
				if(isset($config->collector->collectTime)){
					GarbageCollector::setCollectTime($config->collector->collectTime);
				}
				if(isset($config->collector->compressTime)){
					GarbageCollector::setCompressTime($config->collector->compressTime);
				}
				GarbageCollector::startCollect();
			}

		}
		catch(CoreException $e){
			if(!isset($controller)){
				$controller = null;
			}
			return self::handleException($e, $controller);
		}
		catch(Exception $e){
			/**
			 * Las excepciones se convierten en CoreException sin perder la traza
			 */
			try {
				$fileTraced = false;
				foreach($e->getTrace() as $trace){
					if(isset($trace['file'])){
						if($trace['file']==$e->getFile()){
							$fileTraced = true;
						}
					}
				}
				if($fileTraced==false){
					$exceptionFile = array(array(
						'file' => $e->getFile(),
						'line' => $e->getLine()
					));
					$backtrace = array_merge($exceptionFile, $e->getTrace());
				} else {
					$backtrace = $e->getTrace();
				}
				throw new CoreException($e->getMessage(), $e->getCode(), true, $backtrace);
			}
			catch(CoreException $e){
				if(!isset($controller)){
					$controller = null;
				}
				return self::handleException($e, $controller);
			}
		}
		return true;
	}

	/**
	 * Realiza el proceso de atender una peticion
	 *
	 * @access public
	 * @static
	 */
	public static function handleRequest(){

		/**
		 * Inicializa los plug-ins
		 */
		PluginManager::initializePlugins();
		PluginManager::notifyFromApplication('beforeStartRequest');
		Facility::setFacility(Facility::USER_LEVEL);

		/**
		 * Inicializar componente Router
		 */
		Router::initialize();
		Router::setRouted(true);
		Router::ifRouted();
		$controller = null;
		$controllerName = Router::getController();

		/**
		 * Ejectutar Plugin::beforeDispatchLoop()
		 */
		PluginManager::notifyFromController('beforeDispatchLoop', $controller);

		/**
		 * Establecer directorio de controladores
		 */
		Dispatcher::setControllerDir(self::$_activeControllersDir);

		/**
		 * Ciclo del enrutador
		 */
		while(Router::getRouted()==true){
			Router::setRouted(false);

			/**
			 * Ejectutar Plugin::beforeDispatch()
			 */
			$controllerName = PluginManager::notifyFromController('beforeDispatch', $controller);

			/**
			 * Si no hay controlador ejecuta ControllerBase::init()
			 */
			if(empty($controllerName)){
				Dispatcher::initBase();
			} else {

				/**
				 * Valida que si se tenga acceso al recurso solicitado
				 */
				Security::checkResourceAccess($controller);

				/**
				  * Ejectutar Plugin::beforeExecuteRoute()
				  */
				PluginManager::notifyFromController('beforeExecuteRoute', $controller);

				$controller = Dispatcher::executeRoute(Router::getModule(), Router::getController(), Router::getAction(),
				Router::getParameters(), Router::getAllParameters());

				/**
				  * Ejectutar Plugin::afterExecuteRoute()
				  */
				PluginManager::notifyFromController('afterExecuteRoute', $controller);

			}

			Router::ifRouted();

			/**
			 * Ejectutar Plugin::afterDispatch()
			 */
			$controllerName = PluginManager::notifyFromController('afterDispatch', $controller);

		}

		/**
		 * Ejectutar Plugin::afterDispatchLoop() y CommonEventManager::notifyEvent()
		 */
		CommonEventManager::notifyEvent('afterDispatchLoop');
		$controllerName = PluginManager::notifyFromController('afterDispatchLoop', $controller);
		$controller = Dispatcher::getController();

		/**
		 * Cada tipo de Controlador puede tener un tipo diferente
		 * de administrador de presentacion
		 */
		if($controller!==null){
			$handler = $controller->getViewHandler();
			call_user_func_array($handler, array($controller));
		}
		CommonEventManager::notifyEvent('finishRequest');
		PluginManager::notifyFromApplication('beforeFinishRequest');

		return $controller;
	}

	/**
	 * Administra el comportamiento del framework al generarse una excepcion
	 *
	 * @param string $e
	 * @param Controller $controller
	 */
	private static function handleException($e, $controller){

		/**
		 * Notifica la excepcion a los Plugins
		 */
		PluginManager::notifyFromApplication('beforeUncaughtException', $e);

		$controller = Dispatcher::getController();
		Session::storeSessionData();
		if($controller){
			$exceptionHandler = $controller->getViewExceptionHandler();
			call_user_func_array($exceptionHandler, array($e, $controller));
		} else {
			if(self::$_testingMode==false){
				$e->showMessage();
				View::setContent(ob_get_contents());
				ob_end_clean();
				View::xhtmlTemplate('white');
			} else {
				throw $e;
			}
		}
		return;
	}

	/**
	 * Carga Librerias JavaScript Importantes en el Framework
	 *
	 * @access public
	 * @static
	 */
	public static function javascriptBase(){

		$application = Router::getActiveApplication();
		$controllerName = Router::getController();
		$actionName = Router::getAction();
		$module = Router::getModule();
		$id = Router::getId();
		$path = Core::getInstancePath();

		print "<script type='text/javascript' src='".$path."javascript/core/base.js'></script>\r\n";
		print "<script type='text/javascript' src='".$path."javascript/core/validations.js'></script>\r\n";
		print "<script type='text/javascript' src='".$path."javascript/core/main.php?app=$application&module=$module&path=".urlencode($path)."&controller=$controllerName&action=$actionName&id=$id'></script>\r\n";
	}

	/**
	 * Imprime los CSS cargados mediante Tag::stylesheetLink
	 *
	 * @access public
	 * @static
	 */
	public static function stylesheetLinkTags(){
		$styleSheets = MemoryRegistry::get('CORE_CSS_IMPORTS');
		if(is_array($styleSheets)){
			foreach($styleSheets as $css){
				print $css;
			}
		}
	}

	/**
	 * Enruta el controlador actual a otro controlador,
	 * o otra accion
	 * Ej:
	 * <code>
	 * Core::routeTo("controller: nombre", ["action: accion"], ["id: id"])
	 * </code>
	 *
	 * @access public
	 * @static
	 * @return null
	 */
	public static function routeTo(){
		$args = func_get_args();
		return call_user_func_array(array('Router', 'routeTo'), $args);
	}

	/**
	 * Metodo que muestra información del Framework y la licencia
	 *
	 * @access public
	 * @static
	 */
	public static function info(){
		ob_start();

		self::setInstanceName();

		echo self::javascriptBase();

		Tag::stylesheetLink('info');

		print "<div id='kumbia-info-content'><span id='kumbia-info-header'>Kumbia Enterprise Admin ".self::FRAMEWORK_VERSION."</span>
		<h2>Kumbia Enterprise Framework Instance (".self::getInstanceName()."/".Router::getApplication().") funciona!</h2><div>Para reemplazar esta p&aacute;gina
		edite el archivo <i>apps/default/controllers/application.php</i> en el DocumentRoot del servidor
		web <i>(".(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : getcwd())."/".self::getInstanceName().")</i>.<br><br>
		Está invitado a registrarse. El registro es opcional, al hacerlo usted obtiene:
		<ul>
			<li>Recibir información actualizada sobre proyectos y servicios</li>
			<li>Recibir información de ofertas y promociones</li>
			<li>Públicar temas y mensajes en los foros</li>
			<li>Descargar previews de proyectos y documentación</li>
		</ul></div>
		<hr color='#eaeaea'><div align='center' id='kumbia-info-footer'>
		<a href='http://www.loudertechnology.com/site/projects/license'>Licencia</a> |
		<a href='http://www.loudertechnology.com/'>Louder Technology</a> ".date("Y")."</div>";
		View::setContent(ob_get_contents());
		ob_end_clean();
		View::xhtmlTemplate();
	}

	/**
	 * Importa un paquete recursivamente
	 *
	 * @access public
	 * @static
	 * @param string $package
	 * @throws CoreException
	 */
	public static function import($package){
		$packageParts = explode('.', $package);
	}

	/**
	 * Importa un archivo desde la ubicacion actual
	 *
	 * @param string $dir
	 */
	public static function importFromActiveApp($dir){
		require_once 'apps/'.Router::getApplication()."/$dir";
	}

	/**
	 * Indica si un archivo existe en la aplicacion actual
	 *
	 * @param string $path
	 */
	public static function fileExistsOnActiveApp($path){
		return self::fileExists("apps/".Router::getApplication()."/$path");
	}

	/**
	 * Importa un archivo de una libreria en Library/
	 *
	 * @param string $libraryName
	 * @param string $dir
	 */
	public static function importFromLibrary($libraryName, $dir){
		require_once 'Library/'.$libraryName.'/'.$dir;
	}

	/**
	 * Realiza un require en forma condicional
	 *
	 * @param string $file
	 */
	public static function requireFile($file){
		require 'Library/Kumbia/'.$file.'.php';
	}

	/**
	 * Realiza un require en forma condicional
	 *
	 * @param string $file
	 */
	public static function requireLogicalFile($className){
		if(self::$_isWebSphere==true){
			foreach(func_get_args() as $className){
				if(class_exists($className)==false){
					require CoreClassPath::getClassPath($className);
				}
			}
		}
	}

	/**
	 * Devuelve el buffer de salida
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getContent(){
		return self::$_content;
	}

	/**
	 * Permite lanzar excepciones de PHP o externas a Kumbia como propias
	 *
	 * @access public
	 * @param Exception $exception
	 * @throws CoreException
	 * @static
	 */
	public static function manageExceptions($exception){
		throw new CoreException($exception->getMessage(), $exception->getCode());
	}

	/**
	 * Permite lanzar errores con excepciones
	 *
	 * @access public
	 * @param Exception $exception
	 * @throws CoreException
	 * @static
	 */
	public static function manageErrors($number, $message, $file, $num, $enviroment){
		$errortype = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notificación',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
		);
		$errorReporting = ini_get('error_reporting');
		if(isset($errortype[$number])&&$errorReporting>0){
			if(!class_exists('Debug')){
				self::requireFile('Debug/Debug');
			}
			if(!class_exists('CoreException')){
				self::requireFile('Core/CoreException');
			}
			foreach($enviroment as $var => $value){
				Debug::addVariable($var, $value);
			}
			$exists = false;
			foreach(debug_backtrace() as $trace){
				if(isset($trace['file'])){
					if($trace['file']==$file&&$trace['line']==$num){
						$exists = true;
						break;
					}
				}
			}
			$message = $errortype[$number]." - ".$message;
			if($exists==false){
				$backtrace = array(array(
					'file' => $file,
					'line' => $num
				));
				throw new CoreException($message, -$number, true, $backtrace);
			} else {
				throw new CoreException($message, -$number);
			}
		} else {
			return false;
		}
	}

	/**
	 * Indica si una aplicacion existe
	 *
	 * @access public
	 * @param string $application
	 * @return boolean
	 * @static
	 */
	public static function applicationExists($application){
		return self::fileExists('apps/'.$application);
	}

	/**
	 * Devuelve el directorio de vistas de la aplicacion activa
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getActiveViewsDir(){
		return self::$_activeViewsDir;
	}

	/**
	 * Devuelve el directorio de modelos de la aplicacion activa
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getActiveModelsDir(){
		return self::$_activeModelsDir;
	}

	/**
	 * Devuelve el directorio de controladores de la aplicacion activa
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getActiveControllersDir(){
		return self::$_activeControllersDir;
	}

	/**
	 * Recarga los valores de los directorios de controladores, modelos y vistas
	 *
	 * @access public
	 * @static
	 */
	public static function reloadMVCLocations(){

		//Aplicacion Activa
		self::$_activeApp = Router::getApplication();
		$config = CoreConfig::readFromActiveApplication('config.ini');

		//Directorio de controladores Activo
		if(isset($config->application->controllersDir)){
			self::$_activeControllersDir = 'apps/'.$config->application->controllersDir;
		} else {
			self::$_activeControllersDir = 'apps/'.self::$_activeApp.'/controllers';
		}

		//Directorio de modelos activo
		if(isset($config->application->modelsDir)){
			self::$_activeModelsDir = 'apps/'.$config->application->modelsDir;
		} else {
			self::$_activeModelsDir = 'apps/'.self::$_activeApp.'/models';
		}

		//Directorio de Vistas Activo
		if(isset($config->application->viewsDir)){
			self::$_activeViewsDir = 'apps/'.$config->application->viewsDir;
		} else {
			self::$_activeViewsDir = 'apps/'.self::$_activeApp.'/views';
		}
	}

	/**
	 * Establece si el framework esta en modo Test
	 *
	 * @param boolean $testingMode
	 */
	public static function setTestingMode($testingMode){
		self::$_testingMode = $testingMode;
	}

	/**
	 * Indica si el framework se encuentra en modo Test
	 *
	 * @return boolean
	 */
	public static function isTestingMode(){
		return self::$_testingMode;
	}

	/**
	 * Resetea la peticion
	 *
	 * @access public
	 * @static
	 */
	public static function resetRequest(){
		MemoryRegistry::reset('CORE_CSS_IMPORTS');
	}

	/**
	 * Obtener el valor de un Kumbia Naming and Directory Interface
	 *
	 * @access public
	 * @param string $kumbiaNDI
	 * @static
	 */
	public static function getKumbiaNDI($kumbiaNDI){
		$kumbiaNDI = str_replace('%localserver%', gethostbyname('localhost'), $kumbiaNDI);
		$kumbiaNDI = str_replace('%active-instance%', self::getInstanceName(), $kumbiaNDI);
		$kumbiaNDI = str_replace('%active-app%', Router::getApplication(), $kumbiaNDI);
		$kumbiaNDI = str_replace('%app-base%', 'apps/'.Router::getApplication(), $kumbiaNDI);
		return $kumbiaNDI;
	}

	/**
	 * Devuelve un timestamp aproximado de la peticion
	 *
	 * @return int
	 */
	public static function getProximityTime(){
		if(isset($_SERVER['REQUEST_TIME'])){
			return $_SERVER['REQUEST_TIME'];
		} else {
			return time();
		}
	}

	/**
	 * Indica si un archivo existe
	 *
	 * @param string $filePath
	 * @return boolean
	 */
	public static function fileExists($filePath){
		/*
		//Permite el debug usando Zend Platform
		if(isset($_GET['start_debug'])){
			return file_exists("/Applications/MAMP/htdocs/hfos/".$filePath);
		} else {
			return file_exists($filePath);
		}*/
		return file_exists($filePath);
	}

	public static function getFilePath($path){
		/*
		//Permite el debug usando Zend Platform
		if(isset($_GET['start_debug'])){
			return "/Applications/MAMP/htdocs/hfos/".$path;
		} else {

		}*/
		return $path;
	}

	/**
	 * Indica si un directorio existe en el sistema de archivos
	 *
	 * @param string $path
	 * @return string
	 */
	public static function isDir($path){
		/*
		//Permite el debug usando Zend Platform
		if(isset($_GET['start_debug'])){
			return is_dir("/Applications/MAMP/htdocs/hfos/".$path);
		} else {
			return is_dir($path);
		}*/
		return is_dir($path);
	}

	/**
	 * Establece que la aplicacion se esta ejecutando bajo IBM Websphere
	 *
	 * @param boolean $webSphere
	 */
	public static function setIsWebsphere($webSphere){
		self::$_isWebSphere = $webSphere;
	}

	/**
	 * Indica si se esta usando la aplicación en IBM® Websphere
	 *
	 * @return boolean
	 */
	public static function isWebsphere(){
		return self::$_isWebSphere;
	}

}
