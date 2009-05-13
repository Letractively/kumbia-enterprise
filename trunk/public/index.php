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
 * to kumbia@kumbia.org so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	Bootstrap
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2007-2007 Emilio Rafael Silveira Tovar (emilio.rst@gmail.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * Establece tipo de notificacion de errores
 */
error_reporting(E_ALL | E_NOTICE | E_STRICT);

/**
 * Activa el track_errors
 */
ini_alter('track_errors', true);

/**
 * Cambiar el directorio para ocultar el framework y los archivos de aplicacion
 */
chdir('..');

/**
 * Cargar componentes principales
 */
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

try {

	//Inicializar el ExceptionHandler
	set_exception_handler(array('Core', 'manageExceptions'));
	set_error_handler(array('Core', 'manageErrors'));

	//Detecta la forma en que se debe tratar los parametros del
	Router::handleRouterParameters();

	//Inicializa el entorno de ejecución de la petición
	Core::initApplication();

	//Atender la petición
	Core::main();


}
catch(CoreException $e){
	try {
		Session::startSession();
		$exceptionHandler = Core::determineExceptionHandler();
		call_user_func_array($exceptionHandler, array($e, null));
	}
	catch(Exception $e){
		//Pueden ocurrir mas excepciones en los componentes de inicialización
		ob_start();
		Flash::error(get_class($e).': '.$e->getMessage()." ".$e->getFile()."(".$e->getLine().")");
		print '<b>Backtrace:</b><br/>'."\n";
		foreach($e->getTrace() as $debug){
			print $debug['file'].' ('.$debug['line'].") <br/>\n";
		}
		View::setContent(ob_get_contents());
		ob_end_clean();
		View::xhtmlTemplate('white');
	}
}
catch(Exception $e){
	print 'Exception: '.$e->getMessage();
	foreach(debug_backtrace() as $debug){
		print $debug['file'].' ('.$debug['line'].") <br>\n";
	}
}
