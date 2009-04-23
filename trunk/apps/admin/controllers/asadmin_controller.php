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
 * @category Kumbia
 * @copyright Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license New BSD License
 */

/**
 * Login Operations into Admin Console
 *
 * @category Kumbia
 * @copyright Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license New BSD License
 * @access public
 */
class AsAdminController extends ApplicationController {

	public function beforeFilter(){

	}

	private function _checkRequiredPHPExtensions(){
		if(!extension_loaded("pdo")){
			throw new AsAdminException("Debe cargar la extensi&oacute;n de PHP llamada 'pdo' para usar esta aplicaci&oacute;n");
		}
		if(!extension_loaded("pdo_sqlite")){
			throw new AsAdminException("Debe cargar la extensi&oacute;n de PHP llamada 'pdo' para usar esta aplicaci&oacute;n");
		}
	}

	public function indexAction(){
		$this->_checkRequiredPHPExtensions();
	}

	public function startSessionAction(){
		$this->setResponse('json');
		$authInfo = SessionNamespace::add("authInfo");
		$login = $this->getPostParam("login", "alpha");
		$password = $this->getPostParam("password", "md5");
		if($this->Users->findFirst("login='$login' AND password='$password'")!=false){
			$authInfo->setAuthenticated(true);
			$authInfo->setLastLogin($this->Users->getLastlogin());
			$authInfo->setUserName($this->Users->getLogin());
			$this->Users->setLastlogin(time());
			return true;
		} else {
			$authInfo->setAuthenticated(false);
			return false;
		}
	}

	public function getApplicationsAction(){
		$this->setResponse("view");
		$applications = array();
		foreach(scandir("apps")  as $app){
			if(!in_array($app, array('.', '..', 'admin', '.DS_Store', '.svn', 'Thumbs.db'))){
				$models = array();
				if(file_exists("apps/$app/models")){
					foreach(scandir("apps/$app/models") as $model){
						if(!is_dir("apps/$app/models/$model")){
							if(!in_array($model, array('.', '..', '.DS_Store', '.svn', 'Thumbs.db'))){
								$model = Utils::camelize(str_replace(".php", "", $model));
								$models[] = array(
									"iconCls" => "node-models",
									"text" => $model,
									"id" => "model-$model-$app",
									"leaf" => true
								);
							}
						}
					}
				}
				$logs = array();
				if(file_exists("apps/$app/logs")){
					foreach(scandir("apps/$app/logs") as $log){
						if(!is_dir("apps/$app/logs/$log")){
							if(!in_array($log, array('.', '..', '.DS_Store', '.svn', 'Thumbs.db'))){
								$logs[] = array(
									"iconCls" => "node-logs",
									"text" => $log,
									"id" => "log-$log-$app",
									"leaf" => true
								);
							}
						}
					}
				}
				$plugins = array();
				if(file_exists("apps/$app/plugins")){
					foreach(scandir("apps/$app/plugins") as $plugin){
						if(!is_dir("apps/$app/plugins/$plugin")){
							if(!in_array($plugin, array('.', '..', '.DS_Store', '.svn', 'Thumbs.db'))){
								$plugins[] = array(
									"iconCls" => "node-plugin",
									"text" => $plugin,
									"id" => "plugin-$plugin-$app",
									"leaf" => true
								);
							}
						}
					}
				}
				$config = array();
				if(file_exists("apps/$app/config")){
					foreach(scandir("apps/$app/config") as $conf){
						if(!is_dir("apps/$app/config/$conf")){
							if(!in_array($conf, array('.', '..', '.DS_Store', '.svn', 'Thumbs.db'))){
								$config[] = array(
									"iconCls" => "node-conf",
									"text" => $conf,
									"id" => "conf-$conf-$app",
									"leaf" => true
								);
							}
						}
					}
				}
				$applications[] =  array(
					"text" => $app,
					"iconCls" => "node-app",
					"id" => $app."Application",
					"children" => array(
						array(
							"iconCls" => "node-conf",
							"text" => "Configuraci&oacute;n",
							"id" => "$app-conf",
							"children" => $config
						),
						array(
							"iconCls" => "node-logs",
							"text" => "Logs",
							"id" => "$app-logs",
							"children" => $logs
						),
						array(
							"iconCls" => "node-models",
							"text" => "Modelos",
							"children" => $models
						),
						array(
							"iconCls" => "node-plugin",
							"text" => "Plugins",
							"children" => $plugins
						)
					)
				);
			}
		}
		$jsonResponse = array(
			array(
				'text' => "Aplicaciones Web",
				"iconCls" => "node-apps",
				'children' => $applications
			),
			array(
				'text' => "Configuraci&oacute;n",
				"iconCls" => "node-config",
				'children' => array()
			),
			array(
				"iconCls" => "node-monitor",
				"text" => "Monitor",
				"id" => "node-monitor",
				"leaf" => true
			)
		);
		print json_encode($jsonResponse);
	}

	public function regeneratePasswordAction(){
		$this->setResponse("view");
		$adminUser = $this->Users->findFirst();
		$adminUser->setPassword(md5("admin"));
		$adminUser->setLastlogin(time());
		if($adminUser->save()==false){
			foreach($adminUser->getMessages() as $message){
				Flash::error($message->getMessage());
			}
		}
	}

	public function getApplicationLogAction($identifier){
		$this->setResponse('view');
		$items = explode("-", $identifier);
		$logFile = "apps/{$items[2]}/logs/{$items[1]}";
		$this->setParamToView("logName", $items[1]);
		$this->setParamToView("logSize", filesize($logFile));
		$this->setParamToView("logLastModify", date("r", filemtime($logFile)));
		$this->setParamToView("logContent", file($logFile));
	}

	public function getConfigurationAction($identifier){
		$this->setResponse('view');
		$items = explode("-", $identifier);
		$configFile = "apps/{$items[2]}/config/{$items[1]}";
		$this->setParamToView("appName", $items[2]);
		$this->setParamToView("configName", $items[1]);

		$allSettings = array(
			"environment.ini" => array(
				"database.host" => false,
				"database.type" => false,
				"database.pdo" => false,
				"database.dsn" => false,
				"database.username" => false,
				"database.password" => false,
				"database.port" => false
			)
		);

		$config = parse_ini_file($configFile, true);
		$jsonConfig = array();
		foreach($config as $section => $configSection){
			foreach($configSection as $index => $value){
				if(isset($allSettings[$items[1]][$index])){
					$allSettings[$items[1]][$index] = true;
				}
				if($value==1){
					$configSection[$index] = true;
				}
			}
			if(isset($allSettings[$items[1]])&&is_array($allSettings[$items[1]])){
				foreach($allSettings[$items[1]] as $index => $value){
					if($value==false){
						$configSection[$index] = "";
					}
				}
			}
			$jsonConfig[] = array(
				"name" => $section,
				"settings" => $configSection
			);
		}
		$this->setParamToView("config", $config);
		$this->setParamToView("jsonConfig", $jsonConfig);
	}

	public function getMonitorStatusAction(){
		$this->setResponse('ajax');
		$monitorConfig = CoreConfig::readFromActiveApplication("monitor.ini");
		$db = DbLoader::factory($monitorConfig->settings->{"database.type"}, array(
			"host" => $monitorConfig->settings->{"database.host"},
			"username" => $monitorConfig->settings->{"database.username"},
			"password" => $monitorConfig->settings->{"database.password"},
			"name" => $monitorConfig->settings->{"database.name"}
		));
		$results = $db->fetchAll("SELECT * FROM appmonitor ORDER BY status DESC, application, lasttime DESC", DbMysql::DB_ASSOC); //Fix!
		$db->delete("appmonitor", "lasttime <= ".(time()-21600)." OR lasttime IS NULL");
		$this->setParamToView("results", $results);
	}

}
