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
 * @package		Generator
 * @subpackage	GeneratorReport
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * Generador de Reportes
 *
 * @category	Kumbia
 * @package		Generator
 * @subpackage	GeneratorReport
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
abstract class GeneratorReport {

	/**
	 * Genera un reporte con las condiciones del formulario
	 *
	 * @access public
	 * @param array $form
	 * @static
	 */
	static public function generate($form){

		$config = CoreConfig::readEnviroment();

		$weightArray = array();
		$headerArray = array();
		$selectedFields = "";
		$tables = "";
		$whereCondition = "";
		$maxCondition = "";
		$n = 0;
		$db = db::rawConnect();
		if(isset($form['dataFilter'])&&$form['dataFilter']){
			if(strpos($form['dataFilter'], '@')){
				ereg("[\@][A-Za-z0-9_]+", $form['dataFilter'], $regs);
				foreach($regs as $reg){
					$form['dataFilter'] = str_replace($reg, $_REQUEST["fl_".str_replace("@", "", $reg)], $form['dataFilter']);
				}
			}
		}
		if($form['type']=='standard'){
			if(isset($form['joinTables'])&&$form['joinTables']) {
				$tables = $form['joinTables'];
			}
			if(isset($form['joinConditions'])&&$form['joinConditions']) {
				$whereCondition = " ".$form['joinConditions'];
			}
			foreach($form['components'] as $name => $com){
				if(!isset($com['attributes']['value'])){
					$com['attributes']['value'] = "";
				}
				if($_REQUEST['fl_'.$name]==$com['attributes']['value']){
					$_REQUEST['fl_'.$name] = "";
				}
				if(trim($_REQUEST["fl_".$name])&&$_REQUEST["fl_".$name]!='@'){
					if(!isset($form['components'][$name]['valueType'])){
						$form['components'][$name]['valueType'] = "";
					}
					if($form['components'][$name]['valueType']=='date'){
						$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
					} else {
						if($form['components'][$name]['valueType']=='numeric'){
							$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
						} else {
							if($form['components'][$name]['type']=='hidden'){
								$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
							} else {
								if($com['type']=='check'){
									if($_REQUEST["fl_".$name]==$form['components'][$name]['checkedValue'])
									$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
								} else {
									if($com['type']=='time'){
										if($_REQUEST["fl_".$name]!='00:00'){
											$whereCondition.=" and {$form['source']}.$name = '".$_REQUEST["fl_".$name]."'";
										}
									} else {
										if(!isset($com['primary'])){
											$com['primary'] = false;
										}
										if($com['primary']||$com['type']=='combo'){
											$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
										} else {
											$whereCondition.=" and ".$form['source'].".$name like '%".$_REQUEST["fl_".$name]."%'";
										}
									}
								}
							}
						}
					}
				}
			}
		}

		//Modificaciones para seleccion de la ordenacion del report, si esta acabado en _id, quiere decir foreignkey
		//Cojeremos el texto sin el id, tendremos la tabla
		ActiveRecordUtils::sqlItemSanizite($_REQUEST['reportTypeField']);
		if (substr($_REQUEST['reportTypeField'],strlen($_REQUEST['reportTypeField']) -3,strlen($_REQUEST['reportTypeField'])) == "_id"){
			$OrderFields = substr($_REQUEST['reportTypeField'],0,strlen($_REQUEST['reportTypeField'])-3);
		}else{
			$OrderFields =$_REQUEST['reportTypeField'];
		}
		$maxCondition = $whereCondition;
		$n = 0;
		foreach($form['components'] as $name => $com){
			if(!isset($com['notReport'])){
				$com['notReport'] = false;
			}
			if(!isset($com['class'])){
				$com['class'] = false;
			}
			if(!$com['notReport']){
				if(isset($com['caption'])&&$com['caption']){
					$headerArray[$n] = html_entity_decode($com['caption']);
					$headerArray[$n] = str_replace("<br/>", " ", $headerArray[$n]);
				} else {
					$com['caption'] = "";
				}
				if($com['type']=='combo'&&$com['class']=='dynamic'){
					if(isset($com['extraTables'])&&$com['extraTables']){
						$tables.="{$com['extraTables']},";
					}
					if(isset($com['whereConditionOnQuery'])&&$com['whereConditionOnQuery']){
						$whereCondition.=" and {$com['whereConditionOnQuery']}";
					}
					if(strpos(" ".$com['detailField'], "concat(")){
						$selectedFields.=$com['detailField'].",";
					} else {
						$selectedFields.=$com['foreignTable'].".".$com['detailField'].",";
						//Comparamos la Tabla foranea que tenemos, y cuando sea igual, suponiendo no hay
						//mas de una clave foranea por tabla, sabremos a que tabla pertenece
						if ($com['foreignTable'] == $OrderFields){
							$OrderFields = $com['foreignTable'].".".$com['detailField'];
						}
					}
					$tables.=$com['foreignTable'].",";
					if($com['column_relation']){
						$whereCondition.=" and ".$com['foreignTable'].".".$com['column_relation']." = ".$form['source'].".".$name;
					} else {
						$whereCondition.=" and ".$com['foreignTable'].".".$name." = ".$form['source'].".".$name;
					}
					$weightArray[$n] = strlen($headerArray[$n])+2;
					$n++;
				} else {
					if($com['type']!='hidden'){
						if($com['class']=='static'){
							$weightArray[$n] = strlen($headerArray[$n])+2;
							if($config->database->type=='postgresql'){
								$selectedFields.="case ";
							}
							if($config->database->type=='mysql'){
								for($i=0;$i<=count($com['items'])-2;$i++){
									$selectedFields.="if(".$form['source'].".".$name."='".$com['items'][$i][0]."', '".$com['items'][$i][1]."', ";
									if($weightArray[$n]<strlen($com['items'][$i][1])) {
										$weightArray[$n] = strlen($com['items'][$i][1])+1;
									}
								}
							}

							if($config->database->type=='postgresql'){
								for($i=0;$i<=count($com['items'])-1;$i++){
									$selectedFields.=" when ".$form['source'].".".$name."='".$com['items'][$i][0]."' THEN '".$com['items'][$i][1]."' ";
									if($weightArray[$n]<strlen($com['items'][$i][1])) {
										$weightArray[$n] = strlen($com['items'][$i][1])+1;
									}
								}
							}


							$n++;
							if($config->database->type=='mysql'){
								$selectedFields.="'".$com['items'][$i][1]."')";
								for($j=0;$j<=$i-2;$j++) {
									$selectedFields.=")";
								}
							}
							if($config->database->type=='postgresql'){
								$selectedFields.=" end ";
							}
							$selectedFields.=",";
						} else {
							$selectedFields.=$form['source'].".".$name.",";
							//Aqui seguro que no es foranea, entonces tenemos que poner la tabla principal 							//
							//antes para evitar repeticiones
							if ($name == $OrderFields){
								$OrderFields = $form['source'].".".$OrderFields;
							}
							$weightArray[$n] = strlen($headerArray[$n])+2;
							$n++;
						}
					}
				}
			}
		}
		$tables.=$form['source'];
		$selectedFields = substr($selectedFields, 0, strlen($selectedFields)-1);

		if(isset($form['dataRequisite'])&&$form['dataRequisite']){
			$whereCondition.=" and {$form['dataFilter']}";
		}

		//Modificacion del order
		if($OrderFields){
			$OrderCondition = "Order By ".$OrderFields;
		} else {
			$OrderCondition = "";
		}

		$query = "select $selectedFields from $tables where 1 = 1 ".$whereCondition. " " .$OrderCondition;

		$q = $db->query($query);
		if(!is_bool($q)){
			if(!$db->numRows($q)){
				Flash::notice("No hay información para listar");
				return;
			}
		} else {
			Flash::error($db->error());
			return;
		}

		$result = array();
		$n = 0;
		$db->setFetchMode(dbBase::DB_NUM);
		while($row = $db->fetchArray($q)){
			$result[$n++] = $row;
		}
		$db->setFetchMode(dbBase::DB_BOTH);

		foreach($result as $row){
			for($i=0;$i<=count($row)-1;$i++){
				if($weightArray[$i]<strlen(trim($row[$i]))){
					$weightArray[$i] = strlen(trim($row[$i]));
				}
			}
		}

		for($i=0;$i<=count($weightArray)-1;$i++){
			$weightArray[$i]*= 1.8;
		}

		$sumArray = array_sum($weightArray);

		if(!$_REQUEST['reportType']){
			$_REQUEST['reportType'] = 'pdf';
		}

		if($_REQUEST['reportType']!='html'){
			$title = html_entity_decode($form['caption']);
		} else {
			$title = $form['caption'];
		}

		switch($_REQUEST['reportType']){
			case 'pdf':
				require_once "Library/Kumbia/Generator/GeneratorReport/Format/Pdf.php";
				pdf($result, $sumArray, $title, $weightArray, $headerArray);
			break;
			case 'xls':
				#error_reporting(0);
				require_once "Library/Kumbia/Generator/GeneratorReport/Format/Xls.php";
				xls($result, $sumArray, $title, $weightArray, $headerArray);
			break;
			case 'html':
				require_once "Library/Kumbia/Generator/GeneratorReport/Format/Htm.php";
				htm($result, $sumArray, $title, $weightArray, $headerArray);
			break;
			case 'doc':
				require_once "Library/Kumbia/Generator/GeneratorReport/Format/Doc.php";
				doc($result, $sumArray, $title, $weightArray, $headerArray);
			break;
			default:
				require_once "Library/Kumbia/Generator/GeneratorReport/Format/Pdf.php";
				pdf($result, $sumArray, $title, $weightArray, $headerArray);
			break;
		}

	}
}