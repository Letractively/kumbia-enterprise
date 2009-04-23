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
 * @copyright Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license New BSD License
 */

$messages = array(
	//Core
	-10 => 'Debe tener instalado PHP version 5.20 &oacute; superior para utilizar este framework (Est&aacute; usando %s)',
	-11 => 'El directorio public/temp no tiene permisos de escritura',
	//CoreConfig
	-12 => 'No ha establecido el entorno (mode) por defecto en el archivo config/config.ini',
	-13 => 'El entorno \'%s\' no existe en config/enviroment.ini',
	//ACL
	-14 => 'No existe el adaptador de ACL llamado \'%s\'',
	-15 => 'Nombre invalido "*" para nombre de Rol en Acl_Role::__constuct',
	-16 => 'El Rol \'%s\' no existe en la lista de control de acceso',
	-17 => 'Debe especificar un rol a heredar en Acl::addInherit',
	-18 => 'El recurso \'%s\' no existe en la lista de control de acceso',
	-19 => 'No existe el acceso \'%s\' en el resource \'%s\' de la lista',
	-20 => 'Debe indicar el nombre del modelo que administra la lista de Acceso',
	-21 => 'La clase \'%s\' no es un modelo valido',
	-22 => 'No se ha definido el modelo que administra los Roles',
	-23 => '\'%s\' al crear el Role en la persistencia',
	-24 => 'El role ya se ha creado en la lista de acceso',
	-25 => 'No se ha definido el modelo que administra los Resources',
	-26 => '\'%s\' al crear el Resource en la persistencia',
	-27 => 'El resource ya se ha creado en la lista de acceso',
	-28 => 'No se ha definido el modelo que relaciona los Resources con sus operaciones',
	-29 => 'Nombre de operación \'%s\' invalido',
	-30 => '\'%s\' al crear/modificar el acceso en la persistencia',
	-31 => 'Debe definir el archivo con la lista de políticas seguridad XML',
	-32 => 'El archivo con la lista AclXML no existe (%s)',
	-33 => 'El constraint de la lista de acceso No. %s no ha definido el rol a aplicar',
	-34 => 'El constraint de la lista de acceso No. %s no ha definido el recurso a aplicar',
	-35 => 'El constraint de la lista de acceso No. %s no ha definido la acci&oacute;n a aplicar',
	-36 => 'El constraint de la lista de acceso No. %s no ha definido el tipo de regla a aplicar',
	-37 => 'El tipo de regla del constraint de la lista de acceso No. %s es invalido'
	//ActiveRecord

);