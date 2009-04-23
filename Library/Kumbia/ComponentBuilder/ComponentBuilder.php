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
 * @package		ComponentBuilder
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ComponentBuilder.php 135 2009-04-16 11:06:19Z gutierrezandresfelipe $
 */

/**
 * ComponentBuilder
 *
 * Permite la creacion de componentes de aplicacion en forma dinamica
 *
 * @category	Kumbia
 * @package		ComponentBuilder
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 * @abstract
 */
abstract class ComponentBuilder {

	/**
	 * Crea los archivos .INI por defecto de una aplicacion
	 *
	 * @param string $name
	 */
	private static function createINIFiles($name){
		$str = "; Usa este archivo para definir el enrutamiento estatico entre
; controladores y sus acciones
;
; Un controlador se puede enrutar a otro controlador utlizando '*' como
; comodin asi:
; controlador1/accion1/valor_id1  =  controlador2/accion2/valor_id2
;
; Ej:
; Enrutar cualquier peticion a posts/adicionar a posts/insertar/*
; posts/adicionar/* =	posts/insertar/*
;
; Enrutar cualquier peticion a cualquier controlador en la accion
; adicionar a posts/adicionar/*
; */adicionar/* =	posts/insertar/*

[routes]
;prueba/ruta1/* = prueba/ruta2/*
;prueba/ruta2/* = prueba/ruta3/*
";
		file_put_contents("apps/$name/config/routes.ini", $str);
		$str = "; Kumbia Enterprise Framework\n; Configuracion de Aplicaciones

; mode: Es el entorno en el que se esta trabajando que esta definido en /app-dir/config/config
; name: Es el nombre de la aplicacion

; debug: indica si la aplicacion se encuentra en modo debug,
; las excepciones generan mas informacion

; controllers_dir: Indica en que directorio se encuentran los controladores
; modelsDir: Indica en que directorio se encuentran los modelos
; viewsDir: Indica en que directorio se encuentran las vistas
; pluginsDir: Indica en que directorio se encuentran las vistas

; sessionAdapter: Nombre del adaptador de sesion usado
; sessionSaveHandler: Parametro save handler usado por el Session Adapter

; dbdate: Formato de Fecha por defecto de la Applicacion

[application]
mode = development
name = \"Project Name\"
dbdate = YYYY-MM-DD
debug = On
";
		file_put_contents("apps/$name/config/config.ini", $str);
$str = "; Kumbia Enterprise Framework Configuration

; Parametros de base de datos
; Utiliza el nombre del controlador nativo en database.type (mysql, pgsql, oracle, informix)
; Colocar database.pdo = On si se usa PHP Data Objects

[development]
database.type = mysql
database.host = localhost
database.username = root
database.password =
database.name = development_db

[production]
database.type = mysql
database.host = localhost
database.username = root
database.password =
database.name = production_db

[test]
database.type = mysql
database.host = localhost
database.username = root
database.password =
database.name = test_db

";
		file_put_contents("apps/$name/config/environment.ini", $str);
		$str = "; Cargar los modulos de Kumbia en Library\n\n[modules]\nextensions =";
		file_put_contents("apps/$name/config/boot.ini", $str);
	}

	/**
	 * Crea el archivo ControllerBase por defecto
	 *
	 * @param string $name
	 */
	private static function createControllerBase($name){
		$str = "<?php

/**
 * Todas las controladores heredan de esta clase en un nivel superior
 * por lo tanto los metodos aqui definidos estan disponibles para
 * cualquier controlador.
 *
 * @category Kumbia
 * @package Controller
 * @access public
 **/
class ControllerBase {

	public function init(){
		Core::info();
	}

}

";
		file_put_contents("apps/$name/controllers/application.php", $str);
	}

	/**
	 * Crea el archivo modelbase por defecto
	 *
	 * @param string $name
	 */
	private static function createModelBase($name){
		$str = "<?php\n\n/**\n * ActiveRecord\n *\n * Esta clase es la clase padre de todos los modelos\n * de la aplicacion\n *\n * @category Kumbia\n * @package Db\n * @subpackage ActiveRecord\n */\nabstract class ActiveRecord extends ActiveRecordBase {\n\n}\n\n";
		file_put_contents("apps/$name/models/base/modelBase.php", $str);
	}

	/**
	 * Crea el archivo views/index.phtml por defecto
	 *
	 * @param string $name
	 */
	private static function createIndexView($name){
		$str = "<?php echo \"<?xml version=\\\"1.0\\\" encoding=\\\"UTF-8\\\"?>\\n\" ?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
 <head>
  <meta http-equiv='Content-type' content='text/html; charset=UTF-8' />
  <title>Application Title</title>
  <?php Tag::stylesheetLink('style', true) ?>
  <?php echo Core::stylesheetLinkTags() ?>
  <?php echo Core::javascriptBase() ?>
 </head>
 <body>
    <?php View::getContent(); ?>
 </body>
</html>
";
		file_put_contents("apps/$name/views/index.phtml", $str);
	}

	/**
	 * Crea una aplicacion
	 *
	 * @param string $name
	 */
	public static function createApplication($name){
		if(file_exists("apps/$name")){
			throw new ComponentBuilderException("La aplicaci&oacute;n '$name 'ya existe");
		}
		@mkdir("apps/$name");
		@mkdir("apps/$name/controllers");
		@mkdir("apps/$name/config");
		@mkdir("apps/$name/models");
		@mkdir("apps/$name/models/base");
		@mkdir("apps/$name/views");
		@mkdir("apps/$name/logs");
		@mkdir("apps/$name/views/layouts");
		self::createINIFiles($name);
		self::createModelBase($name);
		self::createIndexView($name);
		self::createControllerBase($name);
	}

}
