<?php

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
			throw new AsAdminException("Debe cargar la extensi&oacute;n de PHP llamada 'pdo' para usar esta aplicaci&oacute;n");
		}
		if(!extension_loaded("pdo_sqlite")){
			throw new AsAdminException("Debe cargar la extensi&oacute;n de PHP llamada 'pdo' para usar esta aplicaci&oacute;n");
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
		foreach(scandir("apps/") as $app){
			if(!in_array($app, array(".", "..", "admin", "appgen"))){
				if(is_dir("apps/".$app)){
					$applications[$app] = $app;
				}
			}
		}
		$environments = array(
			"D" => "DEVELOPMENT",
			"P" => "PRODUCTION",
			"T" => "TEST",
			"A" => "USAR ENTORNO ACTIVO",
		);
		$this->setParamToView("environments", $environments);
		$this->setParamToView("applications", $applications);
	}

	public function componentAction(){
		$this->newappname = "";
		$controllerRequest = ControllerRequest::getInstance();
		if($controllerRequest->isSetPostParam("environ")){
			if($controllerRequest->getParamPost("environ")=="@"){
				Flash::error("Debe indicar el entorno a utilizar");
				$this->routeTo("action: index");
			}
			$this->environ = $controllerRequest->getParamPost("environ", "onechar");
		}
		if($controllerRequest->isSetPostParam("extappname")){
			if($controllerRequest->getParamPost("extappname")=="@"){
				$appName = $controllerRequest->getParamPost("newappname", "identifier");
				if($appName==""){
					Flash::error("Debe indicar el nombre de la aplicaci&oacute;n");
					$this->routeTo("action: index");
				} else {
					if(Core::fileExists("apps/$appName")==true){
						Flash::notice("La aplicaci&oacute;n '$appName' ya existe");
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
			Flash::error("El campo res&uacute;men no puede ser nulo");
			return $this->routeTo("action: nueva");
		}

		if(file_exists("apps/{$this->newappname}")==false){
			try {
				Flash::notice("Se cre&oacute; el directorio apps/{$this->newappname}");
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
		$application->setResume($this->getPostParam("resumen", "striptags", "extraspaces"));
		$application->setDescription($this->getPostParam("descripcion", "striptags", "extraspaces"));
		$application->setName($this->newappname);
		if($application->save()==false){
			foreach($application->getMessages() as $message){
				Flash::error($message->getMessage());
			}
			return $this->routeTo("action: nueva");
		}
		if($application->operationWasInsert()){
			Flash::success("Se cre&oacute; la aplicaci&oacute;n '{$this->newappname}' correctamente");
		}
		if($application->operationWasUpdate()){
			Flash::success("Se actualiz&oacute; la aplicaci&oacute;n '{$this->newappname}' correctamente");
		}
		$this->extappname = $this->newappname;
		$this->newappname = "";
		$this->routeTo("action: form");
	}

	public function sourceAction(){
		$environment = CoreConfig::getConfigurationFrom($this->extappname, "environment.ini");

		$descriptor = array();
		$environ = "";
		if($this->environ=='D'){
			$environ = "development";
		}
		if($this->environ=='P'){
			$environ = "production";
		}
		if($this->environ=='T'){
			$environ = "test";
		}

		if(!isset($environment->$environ)){
			Flash::error('No existe el entorno '.$environ.' en el environment.ini de la aplicaci&oacute;n '.$this->extappname);
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

		if(isset($activeEnv->{"database.host"})){
			$descriptor["host"] = $activeEnv->{"database.host"};
		}

		if(isset($activeEnv->{"database.username"})){
			$descriptor["username"] = $activeEnv->{"database.username"};
		}

		if(isset($activeEnv->{"database.password"})){
			$descriptor["password"] = $activeEnv->{"database.password"};
		}

		if(isset($activeEnv->{"database.name"})){
			$descriptor["name"] = $activeEnv->{"database.name"};
		}

		try {
			$connection = DbLoader::factory($dbtype, $descriptor);
		}
		catch(DbException $e){
			Flash::error("Error: No se pudo efectuar una conexi&oacute;n al gestor relacional con los par&aacute;metros de conexi&oacute;n
			en el entorno '$environ' de la aplicaci&oacute;n '{$this->extappname}'");
			return $this->routeTo("action: form");
		}

		$this->dbtype = $dbtype;
		$this->descriptor = $descriptor;

		$tables = $connection->listTables();
		$sources = array();
		foreach($tables as $table){
			$sources[$table] = $table;
		}
		$this->setParamToView("sources", $sources);

	}

	public function attributesAction(){
		$controllerRequest = ControllerRequest::getInstance();
		try {
			$connection = DbLoader::factory($this->dbtype, $this->descriptor);
		}
		catch(DbException $e){
			Flash::error("Error: No se pudo efectuar una conexi&oacute;n al gestor relacional con los par&aacute;metros de conexi&oacute;n
			de la aplicaci&oacute;n '{$this->extappname}'");
			return $this->routeTo("action: form");
		}
		if($controllerRequest->isSetPostParam("source")){
			$source = $controllerRequest->getParamPost("source", "identifier");
			$this->source = $source;
			$this->controlador = "";
			$this->titulo = "";
		}
		if($controllerRequest->isSetPostParam("etiqueta")){
			$etiqueta = $controllerRequest->getParamPost("etiqueta");
		} else {
			$etiqueta = array();
		}
		if($controllerRequest->isSetPostParam("size")){
			$sizes = $controllerRequest->getParamPost("size");
		} else {
			$sizes = array();
		}
		if($controllerRequest->isSetPostParam("component")){
			$components = $controllerRequest->getParamPost("component");
		} else {
			$components = array();
		}
		if($controllerRequest->isSetPostParam("search")){
			$search = $controllerRequest->getParamPost("search");
		} else {
			$search = array();
		}
		if($controllerRequest->isSetPostParam("browse")){
			$browse = $controllerRequest->getParamPost("browse");
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
				$label = ucwords(str_replace("_", " ", str_replace("_id", "", $field['Field'])));
				$attribute = EntityManager::getEntityInstance("Attributes");
				$attribute->setAppName($this->extappname);
				$attribute->setTableName($this->source);
				$attribute->setFieldName($field['Field']);
				$attribute->setType($field['Type']);
				$attribute->setAllowNull($field['Null'] == "YES" ? "Y" : "N");
				$attribute->setLabel($label);
				$attribute->setHidden("N");
				$attribute->setBrowse("Y");
				$attribute->setReport("Y");
				$attribute->setSearch("Y");
				$attribute->setReadOnly("N");
				if($field['Key']=="PRI"){
					$attribute->setPrimaryKey("Y");
				} else {
					$attribute->setPrimaryKey("N");
				}
				$component = "TE";
				if(strpos($field['Type'], "int")!==false||strpos($field['Type'], "decimal")!==false){
					$component = "TN";
				}
				if($field['Type']=='text'){
					$component = "TA";
				}
				if($field['Field']=='email'){
					$component = "EM";
				}
				if($field['Type']=='date'){
					$component = "DA";
				}
				if(preg_match('/([a-zA-Z0-9_]+)_id$/', $field['Field'])){
					$component = 'CR';
				}
				$size = 0;
				if($pos = strpos(" ".$field['Type'], "(")){
					$size = substr($field['Type'], $pos);
					$size = substr($size, 0, strpos($size, ")"));
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
						$messages[] = "Se actualiz&oacute; correctamente el campo '{$field['Field']}'";
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
			Flash::error("Error: No se pudo efectuar una conexi&oacute;n al gestor relacional con los par&aacute;metros de conexi&oacute;n
			de la aplicaci&oacute;n '{$this->extappname}'");
			return $this->routeTo("action: index");
		}
	}

	public function editRelationAction($id=0){
		$controllerRequest = ControllerRequest::getInstance();
		$id = $this->filter($id, "int");
		$relation = $this->Relations->findFirst($id);
		if($relation==false){
			Flash::error("La relaci&oacute;n no existe");
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
		$id = $this->filter($id, "int");
		$relation = $this->Relations->findFirst($id);
		if($relation==false){
			Flash::error("La relaci&oacute;n no existe");
			return;
		} else {
			$fieldDetail = $controllerRequest->getParamPost("fieldDetail", "identifier");
			$relation->setFieldDetail($fieldDetail);
			$fieldOrder = $controllerRequest->getParamPost("fieldOrder", "identifier");
			$relation->setFieldOrder($fieldOrder);
			if($relation->save()==false){
				foreach($relation->getMessages() as $message){
					Flash::error($message->getMessage());
				}
				$this->routeTo("action: editRelation");
			} else {
				Flash::success("Se guard&oacute; correctamente la relaci&oacute;n");
				$this->routeTo("action: relations");
			}
		}
	}

	public function formOptionsAction(){
		if($this->controlador==""){
			$this->controlador = $this->source;
			$this->titulo = ucwords($this->source);
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
		$controlador = $this->controlador;
		$extappname = $this->extappname;

		$conditions = "app_name = '{$this->extappname}' AND table_name='{$this->source}' AND component = 'CR'";
		if($this->Attributes->count($conditions)>0){
			$attributes = $this->Attributes->find($conditions);
			foreach($attributes as $attribute){
				$relation = $attribute->getRelations();
				$path = "apps/{$this->extappname}/models/{$relation->getTableRelation()}.php";
				$className = Utils::camelize($relation->getTableRelation());
				$modelCode = "<?php class $className extends ActiveRecord { }";
				file_put_contents($path, $modelCode);
			}
		}

		$path = "apps/{$this->extappname}/models/{$this->source}.php";
		$model = Utils::camelize($this->source);
		$modelCode = "<?php class $model extends ActiveRecord { }";
		file_put_contents($path, $modelCode);

		$controller = "<?php\n\n";
		$controller.= "/**\n";
 		$controller.= " * Controlador ".ucfirst($controlador)."\n";
		$controller.= " *\n";
		$controller.= " * @access public\n";
		$controller.= " * @version 1.0\n";
		$controller.= " */\n";
		$controller.= "class ".ucfirst($controlador)."Controller extends ApplicationController {\n\n";

		$conditions = "app_name = '{$this->extappname}' AND table_name='{$this->source}'";
		$attributes = $this->Attributes->find($conditions);
		foreach($attributes as $attribute){
			$fieldName = Utils::lcfirst(Utils::camelize($attribute->getFieldName()));
			$controller.= "\t/**\n";
			$controller.= "\t * ".$attribute->getFieldName()."\n";
			$controller.= "\t *\n";
			if(strpos($attribute->getType(), "int")!==false){
				$controller.= "\t * @var int\n";
			}
			if(strpos($attribute->getType(), "decimal")!==false){
				$controller.= "\t * @var double\n";
			}
			if(strpos($attribute->getType(), "char")!==false){
				$controller.= "\t * @var string\n";
			}
			$controller.= "\t */\n";
			$controller.= "\tpublic \$$fieldName;\n\n";
		}

		$controller.= "\t/**\n";
		$controller.= "\t * Condiciones de b&uacute;squeda temporales\n";
		$controller.= "\t *\n";
		$controller.= "\t * @var string\n";
		$controller.= "\t */\n";
		$controller.= "\tpublic \$condiciones;\n\n";

		$controller.= "\t/**\n";
		$controller.= "\t * Ordenamiento de la visualización\n";
		$controller.= "\t *\n";
		$controller.= "\t * @var string\n";
		$controller.= "\t */\n";
		$controller.= "\tpublic \$ordenamiento;\n\n";

		$controller.= "\t/**\n";
		$controller.= "\t * Página actual en la visualización\n";
		$controller.= "\t *\n";
		$controller.= "\t * @var int\n";
		$controller.= "\t */\n";
		$controller.= "\tpublic \$pagina;\n\n";

		$controller.= "\t/**\n";
		$controller.= "\t * Inicializador del controlador/\n";
		$controller.= "\t *\n";
		$controller.= "\t */\n";
		$controller.= "\tpublic function initialize(){\n";
		$controller.= "\t\t\$this->setPersistance(true);\n";
		$controller.= "\t}\n\n";

		$controller.= "\t/**\n";
		$controller.= "\t * Acción por defecto del controlador/\n";
		$controller.= "\t *\n";
		$controller.= "\t */\n";
		$controller.= "\tpublic function indexAction(){\n\n";
		$controller.= "\t}\n\n";

		$controller.= "\t/**\n";
		$controller.= "\t * Crear un $model/\n";
		$controller.= "\t *\n";
		$controller.= "\t */\n";
		$controller.= "\tpublic function nuevoAction(){\n\n";
		$controller.= "\t}\n\n";

		$gcontroller = "\t/**\n";
		$gcontroller.= "\t * Guardar el $model\n";
		$gcontroller.= "\t *\n";
		$gcontroller.= "\t */\n";
		$gcontroller.= "\tpublic function guardarAction(){\n\n";

		$controller.= "\t/**\n";
		$controller.= "\t * Realiza una búsqueda de registros en $model\n";
		$controller.= "\t *\n";
		$controller.= "\t */\n";
		$controller.= "\tpublic function buscarAction(){\n";
		$controller.="\n";

		$searchview = "<h1>Buscar : {$this->titulo}</h1>\n\n";
		$searchview.= "<div class='userStatus'>Estado: Visualizar un {$this->titulo}</div>\n\n";
		$searchview.= "<table cellspacing='0' class='tableResults'>\n";
		$searchview.= "\t<tr>\n";
		$searchview.= "\t\t<thead>\n";
		$localcodeuf = array();
		foreach($attributes as $attribute){
			$fieldName = Utils::lcfirst(Utils::camelize($attribute->getFieldName()));
			$filters = array();
			if(strpos($attribute->getType(), "int")!==false){
				$filters[] = "\"int\"";
			}
			if(strpos($attribute->getType(), "decimal")!==false){
				$filters[] = "\"double\"";
			}
			if(strpos($attribute->getType(), "char")!==false&&$attribute->getSize()>1){
				$filters[] = "\"striptags\"";
				$filters[] = "\"extraspaces\"";
			}
			if(strpos($attribute->getType(), "char")!==false&&$attribute->getSize()==1){
				$filters[] = "\"onechar\"";
			}
			if(count($filters)>0){
				$localcode = "\t\t\$$fieldName = \$this->getPostParam(\"$fieldName\");\n";
				$localcodeuf[$fieldName] = "\t\t\$$fieldName = \$this->getPostParam(\"$fieldName\", ".join(", ", $filters).");\n";
			} else {
				$localcode = "\t\t\$$fieldName = \$this->getPostParam(\"$fieldName\");\n";
				$localcodeuf[$fieldName] = "\t\t\$$fieldName = \$this->getPostParam(\"$fieldName\");\n";
			}
			if($attribute->getSearch()=='Y'){
				$controller.=$localcode;
			}
			$gcontroller.=$localcodeuf[$fieldName];
			if($attribute->getBrowse()=='Y'){
				$searchview.= "\t\t\t<th><?php echo Tag::linkTo(\"$controlador/visualizar?ordenar={$fieldName}\", \"{$attribute->getLabel()}\") ?></th>\n";
			}
		}
		$searchview.= "\t\t</thead>\n";
		$searchview.= "\t</tr>\n";
		$searchview.= "\t<?php\n";
		$searchview.= "\tif(count(\$resultados)>0){ \n";
		$searchview.= "\t\t\$resultadosPagina = Tag::paginate(\$resultados, \$pagina, 15);\n";
		$searchview.= "\t\tforeach(\$resultadosPagina->items as \$resultado){\n";
		$searchview.= "\t\t\techo Tag::trClassName(array('trResults1', 'trResults2'));\n";
		$controller.="\n";
		$controller.="\t\t\$condiciones = array();\n";
		$browseNumber = 0;
		$primaryKey = array();
		foreach($attributes as $attribute){
			if($attribute->getPrimaryKey()=='Y'){
				$primaryKey[] = $attribute->getFieldName();
			}
			if($attribute->getBrowse()=='Y'){
				$browseNumber++;
				if(strpos($attribute->getType(), "char")!==false){
					$searchview.="\t\t\tprint \"<td>\".\$resultado->{$attribute->getFieldName()}.\"</td>\";\n";
				} else {
					$searchview.="\t\t\tprint \"<td align='right'>\".\$resultado->{$attribute->getFieldName()}.\"</td>\";\n";
				}
			}
			if($attribute->getSearch()=='Y'){
				$fieldName = Utils::lcfirst(Utils::camelize($attribute->getFieldName()));
				if($attribute->getComponent()=='CR'){
					$controller.="\t\tif(\$$fieldName!=\"@\"){\n";
				} else {
					$controller.="\t\tif(\$$fieldName!==\"\"){\n";
				}
				if(in_array($attribute->getComponent(), array('TE', 'TA'))){
					$controller.="\t".$localcodeuf[$fieldName];
					$controller.="\t\t\t\$$fieldName = preg_replace(\"/[ ]+/\", \" \", \$$fieldName);\n";
					$controller.="\t\t\t\$$fieldName = str_replace(\" \", \"%\", \$$fieldName);\n";
					$controller.="\t\t\t\$condiciones[] = \"".$attribute->getFieldName()." LIKE '%\$$fieldName%'\";\n";
				} else {
					$controller.=$localcodeuf[$fieldName];
					$controller.="\t\t\t\$condiciones[] = \"".$attribute->getFieldName()." = '\$$fieldName'\";\n";
				}
				$controller.="\t\t}\n";
			}
		}

		if(count($primaryKey)){
			$urlItems = array();
			$editUrlItems = array();
			if(count($primaryKey)>1){
				$fieldName = Utils::lcfirst(Utils::camelize($key));
				foreach($primaryKey as $key){
					$urlItems[] = "{\$resultado->$key}";
					$editUrlItems[] = "\$$fieldName=null";
				}
				$url = join("/", $urlItems);
				$eurl = join("/", $editUrlItems);
			} else {
				$fieldName = Utils::lcfirst(Utils::camelize($primaryKey[0]));
				$url = "{\$resultado->{$primaryKey[0]}}";
				$eurl = "\$$fieldName=null";
			}
			$searchview.="\t\t\tprint \"<td align='center'>\".Tag::buttonToAction(\"Editar\", \"$controlador/editar/$url\", \"class: editButton\").\"</td>\";\n";
			$searchview.="\t\t\tprint \"<td align='center'>\".Tag::buttonToAction(\"Eliminar\", \"$controlador/eliminar/$url\", \"class: deleteButton\").\"</td>\";\n";
		}

		$econtroller = "\t/**\n";
		$econtroller.= "\t * Editar el $model\n";
		$econtroller.= "\t *\n";
		$econtroller.= "\t */\n";
		$econtroller.= "\tpublic function editarAction($eurl){\n\n";

		$econtroller.= "\t}\n\n";
		$gcontroller.= "\t}\n\n";

		$controller.="\t\tif(count(\$condiciones)>0){\n";
		$controller.="\t\t\t\$this->condiciones = join(\" OR \", \$condiciones);\n";
		$controller.="\t\t} else {\n";
		$controller.="\t\t\t\$this->condiciones = \"\";\n";
		$controller.="\t\t}\n";
		$controller.="\t\t\$this->ordenamiento = \"1\";\n";
		$controller.="\t\t\$this->routeTo(\"action: visualizar\");\n";
		$controller.= "\t}\n\n";

		$nuevoview = "<h1>{$this->titulo}</h1>\n\n";
		$nuevoview.= "<div class='userStatus'>Estado: Creando un {$this->titulo}</div>\n\n";
		$nuevoview.= "<?php echo Tag::form(\"$controlador/crear\"); ?>\n";
		$nuevoview.= "<table class='tableFormNuevo' cellspacing='0'>\n";

		$editarview = "<h1>{$this->titulo}</h1>\n\n";
		$editarview.= "<div class='userStatus'>Estado: Editando un {$this->titulo}</div>\n\n";
		$editarview.= "<?php echo Tag::form(\"$controlador/guardar\"); ?>\n";
		$editarview.= "<table class='tableFormEditar' cellspacing='0'>\n";

		$indexview = "<h1>{$this->titulo}</h1>\n\n";
		$indexview.= "<?php echo Tag::form(\"$controlador/buscar\"); ?>\n";
		$indexview.= "<div class='userStatus'>Estado: Buscar un {$this->titulo}</div>\n\n";
		$indexview.= "<div align='right' class='nuevoButtonDiv'>\n";
		$indexview.= "\t<?php echo Tag::buttonToAction(\"Nuevo\", \"$controlador/nuevo\") ?>\n";
		$indexview.= "</div>\n";
		$indexview.= "<table class='tableFormSearch' cellspacing='0'>\n";
		foreach($attributes as $attribute){
			$labelCode = "\t<tr>\n\t\t<td align='right'><label for='{$attribute->getFieldName()}'><b>".$attribute->getLabel()."</b>:</label></td>\n";
			$size = $attribute->getSize();
			if($size!=""){
				$size = ", \"size: $size\"";
			}
			$maxlength = $attribute->getMaxlength();
			if($maxlength!=""){
				$maxlength = ", \"maxlength: $maxlength\"";
			}
			if($attribute->getComponent()=='TE'){
				$componentCode = "\t\t<td><?php echo Tag::textField(\"{$attribute->getFieldName()}\"{$size}{$maxlength}) ?></td>\n";
			}
			if($attribute->getComponent()=='TN'){
				$componentCode = "\t\t<td><?php echo Tag::numericField(\"{$attribute->getFieldName()}\"{$size}{$maxlength}) ?></td>\n";
			}
			if($attribute->getComponent()=='DA'){
				$componentCode = "\t\t<td><?php echo Tag::dateField(\"{$attribute->getFieldName()}\") ?></td>\n";
			}
			if($attribute->getComponent()=='CR'){
				$relation = $attribute->getRelations();
				$relationsList = $relation->getRelationsList();
				$className = Utils::camelize($relation->getTableRelation());
				foreach($relationsList as $relationList){
					$componentCode = "\t\t<td><?php echo Tag::select(\"{$attribute->getFieldName()}\", \${$className}->find(\"order: {$relation->getFieldOrder()}\"), \"using: {$relationList->getFieldName()},{$relation->getFieldDetail()}\", \"use_dummy: yes\") ?></td>\n";
					break;
				}
			}
			if($attribute->getSearch()=='Y'){
				$indexview.= $labelCode;
				$indexview.=$componentCode;
				$indexview.= "\t</tr>\n";
			}
			$nuevoview.= $labelCode;
			$nuevoview.=$componentCode;
			$nuevoview.= "\t</tr>\n";
			$editarview.= $labelCode;
			$editarview.=$componentCode;
			$editarview.= "\t</tr>\n";
		}
		$indexview.= "\t<tr>\n\t\t<td></td>\n\t\t<td><?php echo Tag::submitButton(\"Buscar\") ?></td>\n\t</tr>\n";
		$indexview.= "</table>\n";
		$indexview.= "<?php echo Tag::endForm(); ?>\n";

		$nuevoview.= "\t<tr>\n\t\t<td></td>\n\t\t<td><?php echo Tag::submitButton(\"Crear\") ?> <?php echo Tag::linkTo(\"$controlador/index\", \"Cancelar\") ?></td>\n\t</tr>\n";
		$nuevoview.= "</table>\n";
		$nuevoview.= "<?php echo Tag::endForm(); ?>\n";

		$editarview.= "\t<tr>\n\t\t<td></td>\n\t\t<td><?php echo Tag::submitButton(\"Guardar\") ?> <?php echo Tag::linkTo(\"$controlador/index\", \"Cancelar\") ?></td>\n\t</tr>\n";
		$editarview.= "</table>\n";
		$editarview.= "<?php echo Tag::endForm(); ?>\n";

		$searchview.= "\t\t\tprint \"</tr>\";\n";
		$searchview.= "\t\t} \n";
		$searchview.= "\t?>\n";
		$searchview.= "\t<tr class='resultsNavigator'>\n";
		$searchview.= "\t\t<td colspan='$browseNumber' align='right'>\n";
		$searchview.= "\t\t\t<?php echo Tag::form(\"$controlador/visualizar\") ?>\n";
		$searchview.= "\t\t\t<table class='resultsNavigatorControls' cellspacing='0'>\n";
		$searchview.= "\t\t\t\t<tr>\n";
		$searchview.= "\t\t\t\t\t<td><?php echo Tag::linkTo(\"$controlador/visualizar?pagina=1\", \"<div class='goToFirstButton'></div>\") ?></td>\n";
		$searchview.= "\t\t\t\t\t<td><?php echo Tag::linkTo(\"$controlador/visualizar?pagina=\".\$resultadosPagina->before, \"<div class='goPrevButton'></div>\") ?></td>\n";
		$searchview.= "\t\t\t\t\t<td><?php echo Tag::numericField(\"pagina\", \"size: 3\", \"value: {\$resultadosPagina->current}\") ?> de <?php echo \$resultadosPagina->total_pages ?></td>\n";
		$searchview.= "\t\t\t\t\t<td><?php echo Tag::linkTo(\"$controlador/visualizar?pagina=\".\$resultadosPagina->next, \"<div class='goNextButton'></div>\") ?></td>\n";
		$searchview.= "\t\t\t\t\t<td><?php echo Tag::linkTo(\"$controlador/visualizar?pagina=\".\$resultadosPagina->total_pages, \"<div class='goToLastButton'></div>\") ?></td>\n";
		$searchview.= "\t\t\t\t</tr>\n";
		$searchview.= "\t\t\t</table>\n";
		$searchview.= "\t\t\t<?php echo Tag::endForm() ?>\n";
		$searchview.= "\t\t</td>\n";
		$searchview.= "\t</tr>\n";
		$searchview.= "\t<?php\n";
		$searchview.= "\t} else {\n";
		$searchview.= "\t\tprint \"<tr><td colspan='$browseNumber' align='center'>NO HAY RESULTADOS EN LA BÚSQUEDA</td>\";\n";
		$searchview.= "\t}\n";
		$searchview.= "\t?>\n";
		$searchview.= "</table>\n";
		$searchview.= "<div align='right' class='backButtonDiv'>\n";
		$searchview.= "\t<?php echo Tag::buttonToAction(\"Volver\", \"$controlador/index\") ?>\n";
		$searchview.= "</div>\n";
		$searchview.= "</div>\n";

		@mkdir("apps/{$extappname}/views/{$controlador}");

		file_put_contents("apps/{$extappname}/views/{$controlador}/index.phtml", $indexview);
		file_put_contents("apps/{$extappname}/views/{$controlador}/visualizar.phtml", $searchview);
		file_put_contents("apps/{$extappname}/views/{$controlador}/nuevo.phtml", $nuevoview);
		file_put_contents("apps/{$extappname}/views/{$controlador}/editar.phtml", $editarview);

		$controller.= "\t/**\n";
		$controller.= "\t * Visualiza los registros encontrados en la búsqueda\n";
		$controller.= "\t *\n";
		$controller.= "\t */\n";
		$controller.= "\tpublic function visualizarAction(){\n";
		$controller.="\n";
		$controller.="\t\t\$controllerRequest = ControllerRequest::getInstance();\n";
		$controller.="\t\tif(\$controllerRequest->isSetQueryParam(\"ordenar\")){\n";
		$controller.="\t\t\t\$posibleOrdenar = array(\n";
		$posibleOrdenar = array();
		foreach($attributes as $attribute){
			if($attribute->getBrowse()=='Y'){
				$fieldName = Utils::lcfirst(Utils::camelize($attribute->getFieldName()));
				$posibleOrdenar[] = "\t\t\t\t\"$fieldName\" => \"{$attribute->getFieldName()}\"";
			}
		}
		$controller.=join(",\n", $posibleOrdenar);
		$controller.="\n\t\t\t);\n";
		$controller.="\t\t\t\$ordenar = \$controllerRequest->getParamQuery(\"ordenar\", \"alpha\");\n";
		$controller.="\t\t\tif(isset(\$posibleOrdenar[\$ordenar])==true){\n";
		$controller.="\t\t\t\t\$this->ordenamiento = \$posibleOrdenar[\$ordenar];\n";
		$controller.="\t\t\t} else {\n";
		$controller.="\t\t\t\t\$this->ordenamiento = \"1\";\n";
		$controller.="\t\t\t}\n";
		$controller.="\t\t}\n";
		$controller.="\t\tif(\$controllerRequest->isSetRequestParam(\"pagina\")){\n";
		$controller.="\t\t\t\$this->pagina = \$controllerRequest->getParamRequest(\"pagina\", \"int\");\n";
		$controller.="\t\t} else {\n";
		$controller.="\t\t\t\$this->pagina = 1;\n";
		$controller.="\t\t}\n";
		$controller.="\t\tif(\$this->condiciones!=\"\"){\n";
		$controller.="\t\t\t\$resultados = \$this->{$model}->find(\$this->condiciones, \"order: {\$this->ordenamiento}\");\n";
		$controller.="\t\t} else {\n";
		$controller.="\t\t\t\$resultados = \$this->{$model}->find(\"order: {\$this->ordenamiento}\");\n";
		$controller.="\t\t}\n\n";
		$controller.="\t\t\$this->setParamToView(\"resultados\", \$resultados);\n";
		$controller.= "\t}\n\n";

		$controller.=$econtroller;
		$controller.=$gcontroller;

		$controller.= "}\n\n";
		file_put_contents("apps/{$extappname}/controllers/{$controlador}_controller.php", $controller);
	}

	public function getFieldsAction(){
		$this->setResponse('json');
		$table = $this->getPostParam("table", "identifier");
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
		$field = $this->getPostParam("field", "identifier");
		$tableRelation = $this->getPostParam("tableRelation", "identifier");
		$fieldRelation = $this->getPostParam("fieldRelation", "identifier");
		$fieldOrder = $this->getPostParam("fieldOrder", "identifier");
		$fieldDetail = $this->getPostParam("fieldDetail", "identifier");
		$conditions = "app_name = '{$this->extappname}' AND table_name='{$this->source}' AND field_name = '$field'";
		$attribute = $this->Attributes->findFirst($conditions);
		if($attribute==false){
			Flash::error("No existe el campo '$field' en la tabla '{$this->source}'");
		} else {
			$relation = ActiveRecord::getInstance("Relations", array(
				"attributes_id" => $attribute->getId(),
				"table_relation" => $tableRelation
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

