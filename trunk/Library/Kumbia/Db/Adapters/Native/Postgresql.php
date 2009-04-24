<?php

/**
 * Kumbia Entreprise Framework
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
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2007 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2007-2007 Emilio Silveira (emilio.rst@gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Postgresql.php 135 2009-04-16 11:06:19Z gutierrezandresfelipe $
 */

/**
 * PostgreSQL Database Support (alpha)
 *
 * La base de datos PostgreSQL es un producto Open Source y disponible sin costo.
 * Postgres, desarrollado originalmente en el Departamento de Ciencias de
 * Computación de UC Berkeley, fue pionero en muchos de los conceptos de
 * objetos y relacionales que ahora est&aacute;n apareciendo en algunas bases de
 * datos comerciales. Provee soporte para lenguajes SQL92/SQL99, transacciones,
 * integridad referencial, procedimientos almacenados y extensibilidad de tipos.
 * PostgreSQL es un descendiente de código abierto de su código original de Berkeley.
 *
 * Estas funciones le permiten acceder a servidores de bases de datos PostgreSQL.
 * Puede encontrar m&aacute;s información sobre PostgreSQL en http://www.postgresql.org.
 * La documentación de PostgreSQL puede encontrarse en http://www.postgresql.org/docs.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @copyright 	Copyright (c) 2005-2007 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2007-2007 Emilio Silveira (emilio.rst@gmail.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/ref.pgsql.php
 * @access		Public
 */
class DbPostgreSQL extends DbBase implements DbBaseInterface {

	public $Id_Connection;
	public $lastResultQuery;
	public $lastQuery;
	public $lastError;
	private $dbUser;
	private $dbHost;
	private $dbPass;
	private $dbPort = 5432;
	private $dbDSN;
	private $dbName;

	const DB_ASSOC = PGSQL_ASSOC;
	const DB_BOTH = PGSQL_BOTH;
	const DB_NUM = PGSQL_NUM;

	/**
	 * Hace una conexión a la base de datos de PostgreSQL
	 *
	 * @param string $dbhost
	 * @param string $dbuser
	 * @param string $dbpass
	 * @param string $dbname
	 * @param string $dbport
	 * @param string $dbdsn
	 * @return resourceConnection
	 */
	public function connect($dbhost='', $dbuser='', $dbpass='', $dbname='', $dbport='', $dbdsn=''){

		if(!extension_loaded('pgsql')){
			throw new DbException('Debe cargar la extensión de PHP llamada php_pgsql');
			return false;
		}

		if(!$dbhost){
			$dbhost = $this->dbHost;
		} else {
			$this->dbHost = $dbhost;
		}
		if(!$dbuser) $dbuser = $this->dbUser; else $this->dbUser = $dbuser;
		if(!$dbpass) $dbpass = $this->dbPass; else $this->dbPass = $dbpass;
		if(!$dbname) $dbpass = $this->dbName; else $this->dbName = $dbname;
		if(!$dbport) $dbport = $this->dbPort; else $this->dbPort = $dbport;
		if(!$dbdsn) $dbdsn = $this->dbDSN; else $this->dbDSN = $dbdsn;

		if($this->Id_Connection = @pg_connect("host={$this->dbHost} port=5432 user={$this->dbUser} password={$this->dbPass} dbname={$this->dbName} port={$this->dbPort}")){
			return true;
		} else {
			if($this->display_errors){
				throw new DbException("No se puede conectar a PostgreSQL, verifique q el servicio este arriba y los par&aacute;metros de conexión sean correctos", false);
			}
			$this->lastError = $this->error();
			$this->log($this->lastError, Logger::ERROR);
			return false;
		}

	}

	/**
	 * Efectua operaciones SQL sobre la base de datos
	 *
	 * @param string $sqlQuery
	 * @return resource or false
	 */
	public function query($sqlQuery){
		$this->debug($sqlQuery);
		$this->log($sqlQuery, Logger::DEBUG);
		if(!$this->Id_Connection){
			$this->connect();
			if(!$this->Id_Connection){
				return false;
			}
		}
		$this->lastQuery = $sqlQuery;
		if($resultQuery = @pg_query($this->Id_Connection, $sqlQuery)){
			$this->lastResultQuery = $resultQuery;
			return $resultQuery;
		} else {
			if($this->display_errors){
				throw new DbException($this->error()." al ejecutar <i>'$sqlQuery'</i>");
			}
			$this->log($this->error()." al ejecutar '$sqlQuery'", Logger::ERROR);
			$this->lastResultQuery = false;
			$this->lastError = $this->error();
			return false;
		}
	}

	/**
	 * Cierra la Conexion al Motor de Base de datos
	 */
	public function close(){
		if($this->Id_Connection) {
			pg_close($this->Id_Connection);
		}
	}

	/**
	 * Devuelve fila por fila el contenido de un select
	 *
	 * @param resource $resultQuery
	 * @param integer $opt
	 * @return array
	 */
	public function fetchArray($resultQuery='', $opt=''){
		if($opt==='') $opt = db::DB_BOTH;
		if(!$this->Id_Connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		return pg_fetch_array($resultQuery, NULL, $opt);
	}

	/**
	 * Constructor de la Clase
	 */
	public function __construct($dbhost=null, $dbuser=null, $dbpass=null, $dbname='', $dbport='', $dbdns=''){
		$this->connect($dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbdsn);
	}

	/**
	 * Devuelve el numero de filas de un select
	 *
	 * @param resource $resultQuery
	 * @return boolean|integer
	 */
	public function numRows($resultQuery=''){
		if(!$this->Id_Connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = pg_num_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			$this->log($this->error(), Logger::ERROR);
			$this->lastError = $this->error();
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el nombre de un campo en el resultado de un select
	 *
	 * @param integer $number
	 * @param resource $resultQuery
	 * @return string
	 */
	public function fieldName($number, $resultQuery=''){
		if(!$this->Id_Connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($fieldName = pg_field_name($resultQuery, $number))!==false){
			return $fieldName;
		} else {
			$this->lastError = pg_last_error($this->Id_Connection);
			$this->log($this->error(), Logger::ERROR);
			return false;
		}
		return false;
	}


	/**
	 * Se Mueve al resultado indicado por $number en un select
	 *
	 * @param integer $number
	 * @param resource $resultQuery
	 * @return boolean
	 */
	public function dataSeek($number, $resultQuery=''){
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($success = pg_result_seek($resultQuery, $number))!==false){
			return $success;
		} else {
			if($this->display_errors){
				throw new DbException($this->error());
			}
			$this->lastError = $this->error();
			$this->log($this->error(), Logger::ERROR);
			return false;
		}
		return false;
	}

	/**
	 * Numero de Filas afectadas en un insert, update o delete
	 *
	 * @param resource $resultQuery
	 * @return integer
	 */
	public function affectedRows($resultQuery=''){
		if(!$this->Id_Connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = pg_affected_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			$this->lastError = $this->error();
			$this->log($this->error(), Logger::ERROR);
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el error de PostgreSQL
	 *
	 * @param string $err
	 * @return string
	 */
	public function error($err=''){
		if(!$this->Id_Connection){
			return @pg_last_error() ? @pg_last_error() : "[Error Desconocido en PostgreSQL $err]";
		}
		return pg_last_error($this->Id_Connection);
	}

	/**
	 * Devuelve el no error de PostgreSQL
	 *
	 * @return integer
	 */
	public function noError(){
		if(!$this->Id_Connection){
			return false;
		}
		return "0"; //Codigo de Error?
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @param string $schema
	 * @param string $table
	 * @return boolean
	 */
	public function tableExists($table, $schema=''){
		$table = strtolower($table);
		$num = $this->fetch_one("select count(*) from information_schema.tables where table_schema = 'public' and table_name='$table'");
		return $num[0];
	}

	/**
	 * Devuelve un FOR UPDATE valido para un SELECT del RBDM
	 *
	 * @param string $sqlQuery
	 * @return string
	 */
	public function forUpdate($sqlQuery){
		return "$sqlQuery FOR UPDATE";
	}

	/**
	 * Devuelve un SHARED LOCK valido para un SELECT del RBDM
	 *
	 * @param string $sqlQuery
	 * @return string
	 */
	public function sharedLock($sqlQuery){
		return "$sqlQuery LOCK IN SHARE MODE";
	}

	/**
	 * Devuelve un LIMIT valido para un SELECT del RBDM
	 *
	 * @param string $sql
	 * @param integer $number
	 * @return string
	 */
	public function limit($sql, $number){
		if(is_numeric($number)){
			$number = (int) $number;
			return "$sql LIMIT $number";
		} else {
			return $sql;
		}
	}

	/**
	 * Borra una tabla de la base de datos
	 *
	 * @param string $table
	 * @param boolean $ifExists
	 * @return boolean
	 */
	public function dropTable($table, $ifExists=true){
		if($ifExists){
			if($this->tableExists($table)){
				return $this->query("DROP TABLE $table");
			} else {
				return true;
			}
		} else {
			return $this->query("DROP TABLE $table");
		}
	}

	/**
	 * Devuelve el ultimo ROW'id en la ultima insercion
	 *
	 * @param string $table
	 * @param array $primaryKey
	 * @return integer
	 */
	public function lastInsertId($table='', $primaryKey=array()){
		return pg_last_oid($this->lastResultQuery);
	}

	/**
	 * Crea una tabla utilizando SQL nativo del RDBM
	 *
	 * TODO:
	 * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
	 * - Soporte para campos autonumericos
	 * - Soporte para llaves foraneas
	 *
	 * @param string $table
	 * @param array $definition
	 * @param array $index
	 * @return boolean
	 */
	public function createTable($table, $definition, $index=array()){
		$create_sql = "CREATE TABLE $table (";
		if(!is_array($definition)){
			new DbException("Definición invalida para crear la tabla '$table'");
			return false;
		}
		$create_lines = array();
		$index = array();
		$unique_index = array();
		$primary = array();
		$not_null = "";
		$size = "";
		foreach($definition as $field => $field_def){
			if(isset($field_def['not_null'])){
				$not_null = $field_def['not_null'] ? 'NOT NULL' : '';
			} else {
				$not_null = "";
			}
			if(isset($field_def['size'])){
				$size = $field_def['size'] ? '('.$field_def['size'].')' : '';
			} else {
				$size = "";
			}
			if(isset($field_def['index'])){
				if($field_def['index']){
					$index[] = "INDEX($field)";
				}
			}
			if(isset($field_def['unique_index'])){
				if($field_def['unique_index']){
					$index[] = "UNIQUE($field)";
				}
			}
			if(isset($field_def['primary'])){
				if($field_def['primary']){
					$primary[] = "$field";
				}
			}
			if(isset($field_def['auto'])){
				if($field_def['auto']){
					$field_def['type'] = "SERIAL";
				}
			}
			if(isset($field_def['extra'])){
				$extra = $field_def['extra'];
			} else {
				$extra = "";
			}
			$create_lines[] = "$field ".$field_def['type'].$size.' '.$not_null.' '.$extra;
		}
		$create_sql.= join(',', $create_lines);
		$last_lines = array();
		if(count($primary)){
			$last_lines[] = 'PRIMARY KEY('.join(",", $primary).')';
		}
		if(count($index)){
			$last_lines[] = join(',', $index);
		}
		if(count($unique_index)){
			$last_lines[] = join(',', $unique_index);
		}
		if(count($last_lines)){
			$create_sql.= ','.join(',', $last_lines).')';
		}
		return $this->query($create_sql);

	}

	/**
	 * Listar las tablas en la base de datos
	 *
	 * @return array
	 */
	public function listTables(){
		return $this->fetchAll("SELECT c.relname AS table_name FROM pg_Class c, pg_user u "
             ."WHERE c.relowner = u.usesysid AND c.relkind = 'r' "
             ."AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
             ."AND c.relname !~ '^(pg_|sql_)' UNION "
             ."SELECT c.relname AS table_name FROM pg_Class c "
             ."WHERE c.relkind = 'r' "
             ."AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
             ."AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner) "
             ."AND c.relname !~ '^pg_'");
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param string $table
	 * @param string $schema
	 * @return array
	 */
	public function describeTable($table, $schema=''){
		$describe = $this->fetchAll("SELECT a.attname AS Field, t.typname AS Type,
			 	CASE WHEN attnotnull=false THEN 'YES' ELSE 'NO' END AS Null,
			 	CASE WHEN (select cc.contype FROM pg_catalog.pg_constraint cc WHERE
			 	cc.conrelid = c.oid AND cc.conkey[1] = a.attnum)='p' THEN 'PRI' ELSE ''
			 	END AS Key FROM pg_catalog.pg_Class c, pg_catalog.pg_attribute a,
			 	pg_catalog.pg_type t WHERE c.relname = '$table' AND c.oid = a.attrelid
			 	AND a.attnum > 0 AND t.oid = a.atttypid order by a.attnum");
		$finalDescribe = array();
		foreach($describe as $key => $value){
			$finalDescribe[] = array(
				"Field" => $value["field"],
				"Type" => $value["type"],
				"Null" => $value["null"],
				"Key" => $value["key"]
			);
		}
		return $finalDescribe;
	}

	/**
	 * Devuelve el id de Conexion generado por el driver
	 *
	 * @return resource
	 */
	public function getConnectionId(){
		return $this->_idConnection;
	}

	/**
	 * Obtiene el nombre de la base de datos actual en el adaptador
	 *
	 * @return string
	 */
	public function getDatabaseName(){
		return $this->_dbName;
	}

	/**
	 * Devuelve el ultimo cursor generado por el driver
	 *
	 * @return resource
	 */
	public function getLastResultQuery(){
		return $this->_lastResultQuery;
	}

	/**
	 * Devuelve una fecha formateada de acuerdo al RBDM
	 *
	 * @param string $date
	 * @param string $format
	 * @return string
	 */
	public function getDateUsingFormat($date, $format='YYYY-MM-DD'){
		return "'$date'";
	}

	/**
	 * Devuelve la fecha actual del motor
	 *
	 *@return string
	 */
	public function getCurrentDate(){
		return new DbRawValue("now()");
	}

	/**
	 * Permite establecer el nivel de isolacion de la conexion
	 *
	 * @param int $isolationLevel
	 */
	public function setIsolationLevel($isolationLevel){
		switch($isolationLevel){
			case 1:
				$isolationCommand = "SET SESSION TRANSACTION READ UNCOMMITED";
				break;
			case 2:
				$isolationCommand = "SET SESSION TRANSACTION READ COMMITED";
				break;
			case 3:
				$isolationCommand = "SET SESSION TRANSACTION REPETEABLE READ";
				break;
			case 4:
				$isolationCommand = "SET SESSION TRANSACTION SERIALIZABLE";
				break;
		}
		$this->query($isolationCommand);
		return true;
	}

}
