<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.

 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	ActiveRecord
 * @subpackage 	Migration
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * ActiveRecordMigrate
 *
 * Subcomponente que permite realizar migraciones de DML y DDL en bases de datos
 *
 * @package 	ActiveRecord
 * @subpackage 	Migration
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id$
 */
class ActiveRecordMigration extends Object {

	/**
	 * Genera el script de migración de una base de datos
	 *
	 * @param string $environment
	 */
	public function generateAll($environment=''){

		if($environment==''){
			$config = CoreConfig::readAppConfig();
			if(!isset($config->application->mode)){
				throw new ActiveRecordException('No se ha definido el entorno por defecto de la aplicación');
			}
		}

		$environmentConfig = CoreConfig::readEnviroment();
		if(!isset($environmentConfig->$environment)){
			throw new ActiveRecordException('No se ha definido el entorno por defecto en enviroment.ini');
		}

		$envConfig = $environmentConfig->$environment;

		$connection = DbLoader::factory($envConfig->{'database.type'}, array(
			'host' => $envConfig->{'database.host'},
			'username' => $envConfig->{'database.username'},
			'password' => $envConfig->{'database.password'},
			'name' => $envConfig->{'database.name'}
		));

		$dbAdapter = ucfirst($envConfig->{'database.name'});
		foreach($connection->listTables() as $table){
			$description = $connection->describeTable('menus_items');
			$tableDefinition = array();
			foreach($description as $field){
				$fieldDefinition = array();
				if(preg_match('/([a-z]+)\(([0-9]+)(,([0-9]+))*\){0,1}/', $field['Type'], $matches)){
					print_r($matches);
					switch($matches[1]){
						case 'int':
							$fieldDefinition['type'] = 'Db'.$dbAdapter.'::TYPE_INTEGER';
							break;
						case 'varchar':
							$fieldDefinition['type'] = 'Db'.$dbAdapter.'::TYPE_VARCHAR';
							break;
						case 'char':
							$fieldDefinition['type'] = 'Db'.$dbAdapter.'.::TYPE_CHAR';
							break;
						case 'date':
							$fieldDefinition['type'] = 'Db'.$dbAdapter.'::TYPE_DATE';
							break;
						case 'datetime':
							$fieldDefinition['type'] = 'Db'.$dbAdapter.'::TYPE_DATETIME';
							break;
						case 'decimal':
							$fieldDefinition['type'] = 'Db'.$dbAdapter.'::TYPE_DECIMAL';
							break;
						case 'text':
							$fieldDefinition['type'] = 'Db'.$dbAdapter.'::TYPE_TEXT';
							break;
					}
				}

				/*if(strpos($field['Type'], "char")){
					$fieldDefinition['type'] = 'DbMySQL::TYPE_VARCHAR';
				} else {
				}*/
				if($field['Null']){
					$fieldDefinition['notNull'] = 'true';
				}
				$tableDefinition[$field['Field']] = $fieldDefinition;
			}


			/*'id' => array(
				'type' => DbMySQL::TYPE_INTEGER,
				'notNull' => true,
				'primary' => true,
				'auto' => true
			),*/

			#print_r($tableDefinition);


			break;
		}

	}

}