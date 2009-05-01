<?php

/**
 * Louder Application Forms
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
 * @category 	Louder
 * @package 	Louder
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright  	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2008-2009 Oscar Garavito (game013@gmail.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * CreateController
 *
 * Controlador que administra la creación de formularios
 *
 * @category 	Kumbia
 * @package 	Kumbia
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright  	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2008-2009 Oscar Garavito (game013@gmail.com)
 * @license 	New BSD License
 */
class CreateController extends ApplicationController {

	/**
	 * Valores estado conversacional
	 *
	 * @var string
	 */
	public $extappname;
	public $newappname;
	public $environ;
	public $dbtype;
	public $descriptor;
	public $source;
	public $controlador;
	public $titulo;

	private function _checkRequiredPHPExtensions(){
		if(!extension_loaded("pdo")){
			throw new AsAdminException("Debe cargar la extensión de PHP llamada 'pdo' para usar esta aplicación");
		}
		if(!extension_loaded("pdo_sqlite")){
			throw new AsAdminException("Debe cargar la extensión de PHP llamada 'pdo' para usar esta aplicación");
		}
	}

	public function beforeFilter(){
		if($this->extappname==""){
			if($this->getActionName()!="index"){
				$this->routeTo("action: index");
				return false;
			}
		}
	}

	public function initialize(){
		$this->setPersistance(true);
	}

	public function indexAction(){
		$this->_checkRequiredPHPExtensions();
		if($this->extappname==""){
			$this->extappname = "default";
		}
		if($this->newappname!=""){
			$this->extappname = "@";
		}
		$applications = array();
		foreach(scandir('apps/') as $app){
			if(!in_array($app, array('.', '..', 'admin', 'appgen'))){
				if(is_dir('apps/'.$app)){
					$applications[$app] = $app;
				}
			}
		}
		$environments = array(
			'D' => 'DEVELOPMENT',
			'P' => 'PRODUCTION',
			'T' => 'TEST',
			'A' => 'USAR ENTORNO ACTIVO',
		);
		$this->setParamToView('environments', $environments);
		$this->setParamToView('applications', $applications);
	}

	public function componentAction(){
		$this->newappname = "";
		$controllerRequest = ControllerRequest::getInstance();
		if($controllerRequest->isSetPostParam('environ')){
			if($controllerRequest->getParamPost('environ')=='@'){
				Flash::error('Debe indicar el entorno a utilizar');
				return $this->routeTo('action: index');
			}
			$this->environ = $controllerRequest->getParamPost('environ', 'onechar');
		}
		if($controllerRequest->isSetPostParam('extappname')){
			if($controllerRequest->getParamPost('extappname')=='@'){
				$appName = $controllerRequest->getParamPost('newappname', 'identifier');
				if($appName==''){
					Flash::error('Debe indicar el nombre de la aplicación');
					$this->routeTo('action: index');
				} else {
					if(Core::fileExists("apps/$appName")==true){
						Flash::notice("La aplicación '$appName' ya existe");
						$this->extappname = $appName;
						$this->newappname = "";
					} else {
						$this->newappname = $appName;
						$controllerRequest->setParamPost("resumen", $appName);
						return $this->routeTo("action: nueva");
					}
				}
			} else {
				$appName = $controllerRequest->getParamPost("extappname", "identifier");
				$this->extappname = $appName;
				$this->routeTo("action: form");
			}
		}

	}

	public function formAction(){

	}

	public function nuevaAction(){

	}

	public function crearAplicacionAction(){
		$controllerRequest = ControllerRequest::getInstance();
		if($controllerRequest->getParamPost("resumen")==""){
			Flash::error("El campo resúmen no puede ser nulo");
			return $this->routeTo("action: nueva");
		}

		if(file_exists("apps/{$this->newappname}")==false){
			try {
				Flash::notice("Se creó el directorio apps/{$this->newappname}");
				ComponentBuilder::createApplication($this->newappname);
			}
			catch(ComponentBuilderException $e){
				Flash::error($e->getMessage());
			}
		}

		$application = $this->Applications->findFirst("name='{$this->newappname}'");
		if($application==false){
			$application = new Applications();
		}
		$application->setResume($this->getPostParam('resumen', 'striptags', 'extraspaces'));
		$application->setDescription($this->getPostParam('descripcion', 'striptags', 'extraspaces'));
		$application->setName($this->newappname);
		if($application->save()==false){
			foreach($application->getMessages() as $message){
				Flash::error($message->getMessage());
			}
			return $this->routeTo("action: nueva");
		}
		if($application->operationWasInsert()){
			Flash::success("Se creó la aplicación '{$this->newappname}' correctamente");
		}
		if($application->operationWasUpdate()){
			Flash::success("Se actualizó la aplicación '{$this->newappname}' correctamente");
		}
		$this->extappname = $this->newappname;
		$this->newappname = "";
		$this->routeTo("action: form");
	}

	public function sourceAction(){
		$environment = CoreConfig::getConfigurationFrom($this->extappname, 'environment.ini');

		$descriptor = array();
		$environ = '';
		if($this->environ=='D'){
			$environ = 'development';
		}
		if($this->environ=='P'){
			$environ = 'production';
		}
		if($this->environ=='T'){
			$environ = 'test';
		}

		if(!isset($environment->$environ)){
			Flash::error('No existe el entorno '.$environ.' en el environment.ini de la aplicación '.$this->extappname);
			return $this->routeTo("action: form");
		} else {
			$activeEnv = $environment->{$environ};
		}
		if(isset($activeEnv->{"database.type"})){
			$dbtype = $activeEnv->{"database.type"};
		} else {
			Flash::error("El entorno '$this->environ' no ha definido el tipo de gestor relacional a usar");
			return $this->routeTo("action: form");
		}

		$parameters = array('host', 'username', 'password', 'name', 'instance', 'layer', 'dsn');
		foreach($parameters as $parameter){
			if(isset($activeEnv->{"database.$parameter"})){
				$descriptor["$parameter"] = $activeEnv->{"database.$parameter"};
			}
		}

		try {
			$connection = DbLoader::factory($dbtype, $descriptor);
		}
		catch(DbException $e){
			Flash::error($e->getMessage());
			Flash::error("Error: No se pudo efectuar una conexión al gestor relacional con los par&aacute;metros de conexión
			en el entorno '$environ' de la aplicación '{$this->extappname}'");
			return $this->routeTo("action: form");
		}

		$this->dbtype = $dbtype;
		$this->descriptor = $descriptor;

		$tables = $connection->listTables();
		$sources = array();
		foreach($tables as $table){
			$sources[$table] = strtolower($table);
		}
		$this->setParamToView('sources', $sources);

	}

	public function attributesAction(){
		$controllerRequest = ControllerRequest::getInstance();
		try {
			$connection = DbLoader::factory($this->dbtype, $this->descriptor);
		}
		catch(DbException $e){
			Flash::error("Error: No se pudo efectuar una conexión al gestor relacional con los par&aacute;metros de conexión
			de la aplicación '{$this->extappname}'");
			return $this->routeTo("action: form");
		}
		if($controllerRequest->isSetPostParam('source')){
			$source = $controllerRequest->getParamPost('source', 'identifier');
			$this->source = strtolower($source);
			$this->controlador = '';
			$this->titulo = '';
		}
		if($controllerRequest->isSetPostParam('etiqueta')){
			$etiqueta = $controllerRequest->getParamPost('etiqueta');
		} else {
			$etiqueta = array();
		}
		if($controllerRequest->isSetPostParam('size')){
			$sizes = $controllerRequest->getParamPost('size');
		} else {
			$sizes = array();
		}
		if($controllerRequest->isSetPostParam('component')){
			$components = $controllerRequest->getParamPost('component');
		} else {
			$components = array();
		}
		if($controllerRequest->isSetPostParam('search')){
			$search = $controllerRequest->getParamPost('search');
		} else {
			$search = array();
		}
		if($controllerRequest->isSetPostParam('browse')){
			$browse = $controllerRequest->getParamPost('browse');
		} else {
			$browse = array();
		}
		$i = 0;
		#unset($_SESSION['KMD']);
		$fields = $connection->describeTable($this->source);
		$messages = array();
		foreach($fields as $field){
			$changes = 0;
			$conditions = "app_name = '{$this->extappname}' AND table_name='{$this->source}' AND field_name = '{$field['Field']}'";
			$attribute = $this->Attributes->findFirst($conditions);
			if($attribute==false){
				$label = ucwords(str_replace('_', ' ', str_replace('_id', '', $field['Field'])));
				$attribute = EntityManager::getEntityInstance('Attributes');
				$attribute->setAppName($this->extappname);
				$attribute->setTableName($this->source);
				$attribute->setFieldName($field['Field']);
				if(strpos($field['Type'], 'enum')!==false){
					$field['Type'] = "char";
				}
				$attribute->setType($field['Type']);
				$attribute->setAllowNull($field['Null'] == 'YES' ? 'Y' : 'N');
				$attribute->setLabel($label);
				$attribute->setHidden('N');
				$attribute->setBrowse('Y');
				$attribute->setReport('Y');
				$attribute->setSearch('Y');
				$attribute->setReadOnly('N');
				if($field['Key']=='PRI'){
					$attribute->setPrimaryKey('Y');
				} else {
					$attribute->setPrimaryKey('N');
				}
				$component = 'TE';
				if(strpos($field['Type'], 'int')!==false||strpos($field['Type'], 'decimal')!==false){
					$component = 'TN';
				}
				if($field['Type']=='text'){
					$component = 'TA';
				}
				if($field['Field']=='email'){
					$component = 'EM';
				}
				if($field['Type']=='date'){
					$component = 'DA';
				}
				if(preg_match('/([a-zA-Z0-9_]+)_id$/', $field['Field'])){
					$component = 'CR';
				}
				$size = 0;
				if($pos = strpos(' '.$field['Type'], '(')){
					$size = substr($field['Type'], $pos);
					$size = substr($size, 0, strpos($size, ')'));
					$size = (int) $size;
				}
				$attribute->setSize($size);
				$attribute->setMaxlength($size);
				$attribute->setComponent($component);
				if($attribute->save()==false){
					foreach($attribute->getMessages() as $message){
						Flash::error($message->getMessage());
					}
				}
			} else {
				if(isset($etiqueta[$i])){
					if($etiqueta[$i]!=$attribute->getLabel()){
						$attribute->setLabel($etiqueta[$i]);
						$changes++;
					}
				}
				if(isset($sizes[$i])){
					if($sizes[$i]!=$attribute->getSize()){
						$attribute->setSize($sizes[$i]);
						$changes++;
					}
				}
				if(isset($components[$i])){
					if($components[$i]!=$attribute->getComponent()){
						$attribute->setComponent($components[$i]);
						$changes++;
					}
				}
				if($controllerRequest->isSetPostParam("search")){
					if(in_array($attribute->getId(), $search)){
						if($attribute->getSearch()!='Y'){
							$attribute->setSearch("Y");
							$changes++;
						}
					} else {
						if($attribute->getSearch()!='N'){
							$attribute->setSearch("N");
							$changes++;
						}
					}
				}
				if($controllerRequest->isSetPostParam("browse")){
					if(in_array($attribute->getId(), $browse)){
						if($attribute->getBrowse()!='Y'){
							$attribute->setBrowse("Y");
							$changes++;
						}
					} else {
						if($attribute->getBrowse()!='N'){
							$attribute->setBrowse("N");
							$changes++;
						}
					}
				}
				if($changes>0){
					if($attribute->save()==false){
						foreach($attribute->getMessages() as $message){
							Flash::error($message->getMessage());
						}
					} else {
						$messages[] = "Se actualizó correctamente el campo '{$field['Field']}'";
					}
				}
			}
			$i++;
		}
		if(count($messages)>0){
			Flash::success($messages);
		}
		$conditions = "app_name = '{$this->extappname}' AND table_name='{$this->source}'";
		$attributes = $this->Attributes->find($conditions);
		$this->setParamToView("attributes", $attributes);

	}

	public function relationsAction(){
		$connection = $this->_getConnection();
		$conditions = "app_name = '{$this->extappname}' AND table_name='{$this->source}'";
		if($this->Attributes->count($conditions)>0){
			$attributes = $this->Attributes->find($conditions);
			$attributesRelation = array();
			$fields = array();
			foreach($attributes as $attribute){
				$fields[$attribute->getFieldName()] = $attribute->getFieldName();
				if($attribute->getComponent()=='CR'){
					if(preg_match('/([a-zA-Z0-9_]+)_id$/', $attribute->getFieldName(), $matches)){
						$relation = $this->Relations->findFirst("attributes_id='{$attribute->getId()}' AND table_relation='{$matches[1]}'");
						if($relation==false){
							$relation = new Relations();
							$relation->setAttributesId($attribute->getId());
							$relation->setTableRelation($matches[1]);
							$relation->setFieldDetail("id");
							$relation->setFieldOrder("id");
							if($relation->save()==false){
								foreach($relation->getMessages() as $message){
									Flash::error($message->getMessage());
								}
							}
							$relationList = new RelationsList();
							$relationList->setRelationsId($relation->getId());
							$relationList->setFieldName("id");
							$relationList->setNumber(1);
							if($relationList->save()==false){
								foreach($relationList->getMessages() as $message){
									Flash::error($message->getMessage());
								}
							}
						}
					}
					$attributesRelation[] = $attribute;
				}
			}
			$this->setParamToView("fields", $fields);
			$this->setParamToView("attributes", $attributesRelation);
		} else {
			$this->setParamToView("attributes", array());
		}

		$tables = $connection->listTables();
		$sources = array();
		foreach($tables as $table){
			$sources[$table] = $table;
		}
		$this->setParamToView("sources", $sources);

	}

	private function _getConnection(){
		try {
			$connection = DbLoader::factory($this->dbtype, $this->descriptor);
			return $connection;
		}
		catch(DbException $e){
			Flash::error("Error: No se pudo efectuar una conexión al gestor relacional con los par&aacute;metros de conexión
			de la aplicación '{$this->extappname}'");
			return $this->routeTo("action: index");
		}
	}

	public function editRelationAction($id=0){
		$controllerRequest = ControllerRequest::getInstance();
		$id = $this->filter($id, "int");
		$relation = $this->Relations->findFirst($id);
		if($relation==false){
			Flash::error("La relación no existe");
			return;
		} else {
			$connection = $this->_getConnection();
			$this->setParamToView("tableRelation", $relation->getTableRelation());
			$describe = $connection->describeTable($relation->getTableRelation());
			$fields = array();
			foreach($describe as $field){
				$fields[$field['Field']] = $field['Field'];
			}
			if($controllerRequest->isSetPostParam("fieldDetail")==false){
				$controllerRequest->setParamPost("fieldDetail", $relation->getFieldDetail());
			}
			if($controllerRequest->isSetPostParam("fieldOrder")==false){
				$controllerRequest->setParamPost("fieldOrder", $relation->getFieldOrder());
			}
			$this->setParamToView("fields", $fields);
		}
	}

	public function saveRelationAction($id){
		$controllerRequest = ControllerRequest::getInstance();
		$id = $this->filter($id, 'int');
		$relation = $this->Relations->findFirst($id);
		if($relation==false){
			Flash::error('La relación no existe');
			return;
		} else {
			$fieldDetail = $controllerRequest->getParamPost('fieldDetail', 'identifier');
			$relation->setFieldDetail($fieldDetail);
			$fieldOrder = $controllerRequest->getParamPost('fieldOrder', 'identifier');
			$relation->setFieldOrder($fieldOrder);
			if($relation->save()==false){
				foreach($relation->getMessages() as $message){
					Flash::error($message->getMessage());
				}
				$this->routeTo('action: editRelation');
			} else {
				Flash::success('Se guardó correctamente la relación');
				$this->routeTo('action: relations');
			}
		}
	}

	public function formOptionsAction(){
		if($this->controlador==""){
			$this->controlador = strtolower($this->source);
			$this->titulo = ucwords(strtolower(str_replace('_', ' ', $this->source)));
		}
	}

	public function confirmarAction(){
		$files = array();
		$files[] = "apps/{$this->extappname}/controllers/{$this->controlador}_controller.php";
		$conditions = "app_name = '{$this->extappname}' AND table_name='{$this->source}' AND component = 'CR'";
		if($this->Attributes->count($conditions)>0){
			$attributes = $this->Attributes->find($conditions);
			foreach($attributes as $attribute){
				$relation = $attribute->getRelations();
				$path = "apps/{$this->extappname}/models/{$relation->getTableRelation()}.php";
				if(Core::fileExists($path)==false){
					$files[] = $path;
				}
			}
		}
		$path = "apps/{$this->extappname}/models/{$this->controlador}.php";
		if(Core::fileExists($path)==false){
			$files[] = $path;
		}
		$files[] = "apps/{$this->extappname}/views/{$this->controlador}/index.phtml";
		$files[] = "apps/{$this->extappname}/views/{$this->controlador}/crear.phtml";
		$files[] = "apps/{$this->extappname}/views/{$this->controlador}/buscar.phtml";
		$files[] = "apps/{$this->extappname}/views/{$this->controlador}/editar.phtml";
		$this->setParamToView("files", $files);
	}

	public function crearFormAction(){
		$connection = $this->_getConnection();
		CreateForm::newForm($connection, $this->controlador, $this->extappname, $this->source, $this->titulo);
		#$this->redirect("create/index");
	}

	public function getFieldsAction(){
		$this->setResponse('json');
		$table = $this->getPostParam('table', 'identifier');
		$connection = $this->_getConnection();
		$fields = $connection->describeTable($table);
		$rfields = array();
		foreach($fields as $field){
			$rfields[] = $field['Field'];
		}
		return $rfields;
	}

	public function addRelationAction(){
		$this->setResponse('ajax');
		$field = $this->getPostParam('field', 'identifier');
		$tableRelation = $this->getPostParam('tableRelation', 'identifier');
		$fieldRelation = $this->getPostParam('fieldRelation', 'identifier');
		$fieldOrder = $this->getPostParam('fieldOrder', 'identifier');
		$fieldDetail = $this->getPostParam('fieldDetail', 'identifier');
		$conditions = "app_name = '{$this->extappname}' AND table_name='{$this->source}' AND field_name = '$field'";
		$attribute = $this->Attributes->findFirst($conditions);
		if($attribute==false){
			Flash::error("No existe el campo '$field' en la tabla '{$this->source}'");
		} else {
			$relation = ActiveRecord::getInstance('Relations', array(
				'attributes_id' => $attribute->getId(),
				'table_relation' => $tableRelation
			));
			$relation->setFieldDetail($fieldDetail);
			$relation->setFieldOrder($fieldOrder);
			if($attribute->getComponent()!='CR'){
				$attribute->setComponent('CR');
				if($attribute->save()==false){
					foreach($attribute->getMessages() as $message){
						Flash::error($message->getMessage());
					}
				} else {
					Flash::notice("El tipo de componente del campo '{$attribute->getFieldName()}' fué cambiado a 'Combo Entidad-Relacional'");
				}
			}
			if($relation->save()==false){
				foreach($relation->getMessages() as $message){
					Flash::error($message->getMessage());
				}
			}
			$relationList = ActiveRecord::getInstance("RelationsList", array(
				"relations_id" => $relation->getId(),
				"field_name" => $fieldRelation
			));
			if($relationList->getNumber()<1){
				$number = $relationList->maximum("relations_id='{$relation->getId()}'");
				$relationList->setNumber($number+1);
			}
			if($relationList->save()==false){
				foreach($relationList->getMessages() as $message){
					Flash::error($message->getMessage());
				}
			}
			print $relation->getTableRelation();
		}
	}

}

