<?php

/**
 * Kumbia Enteprise Framework
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
 * @category 	Kumbia
 * @package 	Router
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */

/**
 * SoapRouter
 *
 * Adaptador que modifica el enrutamiento de acuerdo a la peticion SOAP
 *
 * @category 	Kumbia
 * @package 	Router
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class SoapRouter implements RouterInterface {

	/**
	 * Namespace para tipos de Datos SOAP
	 *
	 * @var string
	 */
	private $_xmlSchemaNamespace = 'http://www.w3.org/2001/XMLSchema-instance';

	/**
	 * Namespace para SOAP-ENC
	 *
	 * @var string
	 */
	private $_xmlSoapEnc = 'http://schemas.xmlsoap.org/soap/encoding/';

	/**
	 * Devuelve un SOAP:Array como un array numérico
	 *
	 * @access	private
	 * @param	DOMElement $actionParam
	 */
	private function _decodeSoapArray($actionParam){
		$soapArray = array();
		foreach($actionParam->childNodes as $item){
			if($item->nodeType==1){
				if($item->localName=='item'){
					$paramType = $item->getAttributeNS($this->_xmlSchemaNamespace, 'type');
					if($paramType=='ns2:Map'){
						$soapArray[] = $this->_getXSIMap($item);
					} else {
						if($paramType=='SOAP-ENC:Array'){
							$soapArray[] = $this->_decodeSoapArray($item);
						} else {
							$soapArray[] = $this->_decodeXSDType($paramType, $item->nodeValue);
						}
					}
				}
			}
		}
		return $soapArray;
	}

	/**
	 * Devuelve un mapa XSI como un array asociativo
	 *
	 * @access	private
	 * @param	DOMElement $actionParam
	 */
	private function _getXSIMap($actionParam){
		$arrayMap = array();
		foreach($actionParam->childNodes as $item){
			if($item->nodeType==1){
				if($item->localName=='item'){
					$index = null;
					$value = null;
					foreach($item->childNodes as $node){
						if($node->nodeType==1){
							if($node->localName=='key'){
								$index = (string) $node->nodeValue;
							} else {
								if($node->localName=='value'){
									$paramType = $node->getAttributeNS($this->_xmlSchemaNamespace, 'type');
									if($this->_isTypeLiteral($paramType)==true){
										$value = $this->_decodeXSDType($paramType, $node->nodeValue);
									} else {
										if($paramType=='ns2:Map'){
											$value = $this->_getXSIMap($node);
										} else {
											if($paramType=='SOAP-ENC:Array'){
												$value = $this->_getSoapArray($node);
											} else {
												$value = null;
											}
										}
									}
								}
							}
						}
					}
					if($index!==null){
						$arrayMap[$index] = $value;
					} else {
						$arrayMap[] = $value;
					}
				}
			}
		}
		return $arrayMap;
	}

	/**
	 * Devuelve true si el XSD corresponde a un literal
	 *
	 * @access private
	 * @param string $xsdDataType
	 */
	private function _isTypeLiteral($xsdDataType){
		return in_array($xsdDataType, array('xsd:string', 'xsd:boolean', 'xsd:int', 'xsd:float'));
	}

	/**
	 * Convierte el valor XSD a un valor nativo PHP
	 *
	 * @param string $xsdDataType
	 * @param mixed $returnValue
	 * @return mixed
	 */
	private function _decodeXSDType($xsdDataType, $returnValue){
		switch($xsdDataType){
			case 'xsd:string':
				return (string) $returnValue;
				break;
			case 'xsd:int':
				return (int) $returnValue;
				break;
			case 'xsd:float':
				return (float) $returnValue;
				break;
			case 'xsd:boolean':
				return $returnValue=='true' ? true : false;
				break;
		}
		return null;
	}

	/**
	 * Modifica los parametros de enrutamiento de acuerdo a la peticion SOAP
	 *
	 * @access public
	 */
	public function handleRouting(){
		$request = ControllerRequest::getInstance();
		$soapRawRequest = $request->getRawBody();
		$domDocument = new DOMDocument();
		$xmlValidation = @$domDocument->loadXML($soapRawRequest);
		if($xmlValidation==false){
			$soapException = new SoapException($php_errormsg);
			$soapException->setFaultCode('Sender');
			throw $soapException;
		}
		$soapAction = explode('#', str_replace("\"", "", $_SERVER['HTTP_SOAPACTION']));
		foreach($domDocument->getElementsByTagNameNS($soapAction[0], $soapAction[1]) as $domElement){
			$parameters = array();
			foreach($domElement->childNodes as $actionParam){
				if($actionParam->nodeType==1){
					$paramType = $actionParam->getAttributeNS($this->_xmlSchemaNamespace, 'type');
					if($paramType=='ns2:Map'){
						$parameters[] = $this->_getXSIMap($actionParam);
					} else {
						if($paramType=='SOAP-ENC:Array'){
							$parameters[] = $this->_decodeSoapArray($actionParam);
						} else {
							$parameters[] = $this->_decodeXSDType($paramType, $actionParam->nodeValue);
						}
					}
				}
			}
			Router::setAction($soapAction[1]);
			Router::setParameters($parameters);
		}
	}

	/**
	 * Obtiene el callback del administrador de excepciones
	 *
	 * @return callback
	 */
	public function getExceptionHandler(){
		return array('Soap', 'faultSoapHandler');
	}

	/**
	 * Devuelve el administrador de la respuesta de la petición por defecto
	 *
	 * @access public
	 * @return callback
	 */
	public function getResponseHandler(){
		return array('Soap', 'serverHandler');
	}

	/**
	 * Devuelve el administrador de petición por defecto
	 *
	 * @access public
	 * @return callback
	 */
	public function getExceptionResponseHandler(){
		return array('Soap', 'faultSoapHandler');
	}

}
