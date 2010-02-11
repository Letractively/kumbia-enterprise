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
 * @package		Db
 * @subpackage	SQLDialects
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * MySQL SQL Dialect
 *
 * Funciones de traductor de SQL para MySQL
 * Puede encontrar más información sobre MySQL en http://www.mysql.com/.
 * La documentación de MySQL puede encontrarse en http://dev.mysql.com/doc/.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	SQLDialects
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/ref.mysql.php
 * @access		Public
 */
class MysqlSQLDialect {

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @return	string
	 * @static
	 */
	public static function tableExists($tableName, $schemaName=''){
		if($schemaName==''){
			return 'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = \''.$tableName.'\'';
		} else {
			$schemaName = addslashes("$schemaName");
			return 'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME =  \''.$tableName.'\' AND TABLE_SCHEMA = \''.$schemaName.'\'';
		}
	}

	/**
	 * Devuelve un FOR UPDATE valido para un SELECT del RBDM
	 *
	 * @access
	 * @param	string $sqlQuery
	 * @return	string
	 * @static
	 */
	public static function forUpdate($sqlQuery){
		return $sqlQuery.' FOR UPDATE';
	}

}