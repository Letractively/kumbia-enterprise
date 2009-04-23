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
 * @category 	Kumbia
 * @package 	Soap
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */

/**
 * Soap
 *
 * Clase que administra el SoapServer
 *
 * @category 	Kumbia
 * @package 	Soap
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class Soap extends Object {

	/**
	 * Namespace de nodos Envelope
	 *
	 * @var string
	 * @staticvar
	 */
	private static $_envelopeNS = "http://schemas.xmlsoap.org/soap/envelope/";

	/**
	 * Namespace del XML Schema Instance (xsi)
	 *
	 * @var string
	 */
	private static $_xmlSchemaInstanceNS = "http://www.w3.org/2001/XMLSchema-instance";

	/**
	 * DOMDocument Base
	 *
	 * @var DOMDocument
	 * @staticvar
	 */
	private static $_domDocument;

	/**
	 * Nodo Raiz de la respuesta SOAP
	 *
	 * @var DOMElement
	 */
	private static $_rootElement;

	/**
	 * Nodo Body de la respuesta SOAP
	 *
	 * @var DOMElement
	 */
	private static $_bodyElement;

	/**
	 * Crea un Envelope SOAP apto para SoapFaults y Respuestas
	 *
	 * @access private
	 * @return DOMElement
	 * @static
	 */
	static private function _createSOAPEnvelope(){
		self::$_domDocument = new DOMDocument("1.0", "UTF-8");
		self::$_rootElement = self::$_domDocument->createElementNS(self::$_envelopeNS, "SOAP-ENV:Envelope");
		self::$_domDocument->appendChild(self::$_rootElement);
		self::$_bodyElement = new DOMElement("Body", "", self::$_envelopeNS);
		self::$_rootElement->appendChild(self::$_bodyElement);
		return self::$_bodyElement;
	}

	/**
	 * Administra el objeto SoapServer y genera la respuesta SOAP
	 *
	 * @access public
	 * @param mixed $controller
	 * @static
	 */
	static public function serverHandler($controller){

		/*$soapOptions = array(
			'uri' => 'http://app-services',
			'actor' => "http://{$_SERVER['SERVER_ADDR']}/".Core::getInstancePath()."/".$controller->getControllerName(),
			'soap_version' => SOAP_1_2,
			'encoding' => 'UTF-8'
		);
		$controllerName = $controller->getControllername();
		#$soapOptions = $controller->getSoapOptions();
		self::$_soapServer = new SoapServer(null, $soapOptions);
		self::$_soapServer->setClass($controllerName."Controller");
		self::$_soapServer->setPersistence(SOAP_PERSISTENCE_SESSION);
		$request = ControllerRequest::getInstance();
		$soapRawRequest = $request->getRawBody();
		if(preg_match('/ns1:([a-zA-Z0-9]+)/', $soapRawRequest, $nsAction)==true){
			$soapRequest = str_replace("ns1:{$nsAction[1]}", "ns1:{$nsAction[1]}Action", $soapRawRequest);
			self::$_soapServer->handle($soapRequest);
		} else {
			self::$_soapServer->handle();
		}*/

		$soapAction = explode("#", str_replace("\"", "", $_SERVER['HTTP_SOAPACTION'])); ;
		$serviceNamespace = "http://app-services";
		$bodyElement = self::_createSOAPEnvelope();
		self::$_domDocument->createAttributeNS($serviceNamespace, "ns1:dummy");
		self::$_domDocument->createAttributeNS("http://www.w3.org/2001/XMLSchema", "xsd:dummy");
		self::$_domDocument->createAttributeNS("http://schemas.xmlsoap.org/soap/encoding", "SOAP-ENC:dummy");
		self::$_domDocument->createAttributeNS(self::$_xmlSchemaInstanceNS, "xsi:dummy");
		self::$_rootElement->setAttributeNS(self::$_envelopeNS, "encondingStyle", "http://schemas.xmlsoap.org/soap/encoding/");

		$responseElement = self::$_domDocument->createElementNS($serviceNamespace, $soapAction[1]."Response");
		$dataEncoded = self::_getDataEncoded();
		if($dataEncoded!=null){
			$responseElement->appendChild($dataEncoded);
		}
		$bodyElement->appendChild($responseElement);
		print self::$_domDocument->saveXML();
	}

	/**
	 * Devuelve el tipo de dato XSD de acuerdo al tipo de dato Nativo en PHP
	 *
	 * @param string $nativeDataType
	 * @return string
	 */
	private static function _getDataXSD($nativeDataType){
		if($nativeDataType=='int'){
			return 'int';
		}
		return 'ur-type';
	}

	/**
	 * Formatea el valor devuelto por el metodo accion en el controlador
	 * usando el tipo de dato SOAP adecuado
	 *
	 * @access private
	 * @return DOMElement
	 * @static
	 */
	private static function _getDataEncoded($valueReturned=null, $nodeType="return"){
		if($valueReturned===null){
			$valueReturned = Dispatcher::getValueReturned();
		}
		if(!is_array($valueReturned)){
			$element = self::$_domDocument->createElement($nodeType, $valueReturned);
			if(is_integer($valueReturned)==true){
				$element->setAttribute("xsi:type", "int");
			}
			if(is_string($valueReturned)==true){
				$element->setAttribute("xsi:type", "string");
			}
			if(is_bool($valueReturned)==true){
				$element->setAttribute("xsi:type", "boolean");
				if($valueReturned===false){
					$stringValue = "false";
				} else {
					$stringValue = "boolean";
				}
				$element->nodeValue = $stringValue;
			}
			return $element;
		} else {
			$element = self::$_domDocument->createElement($nodeType);
			$dataType = "";
			$oldDataType = "";
			foreach($valueReturned as $key => $value){
				if($dataType!='mixed'){
					$dataType = gettype($value);
					if(!$oldDataType){
						$oldDataType = $dataType;
					} else {
						if($dataType!=$oldDataType){
							$dataType = 'mixed';
						}
						$oldDataType = $dataType;
					}
				}
			}
			$returnString = "";
			if($dataType=='mixed'){
				$element->setAttribute("SOAP-ENC:arrayType", "xsd:ur-type[".count($valueReturned)."]");
			} else {
				$element->setAttribute("SOAP-ENC:arrayType", "xsd:".self::_getDataXSD($dataType)."[".count($valueReturned)."]");
			}
			$element->setAttribute("xsi:type", "SOAP-ENC:Array");
			foreach($valueReturned as $key => $value){
				$element->appendChild(self::_getDataEncoded($value, "item"));
			}
			return $element;
		}
	}

	/**
	 * Genera las fault exceptions del Servidor SOAP
	 *
	 * @access public
	 * @param Exception $e
	 * @param mixed $controller
	 * @static
	 */
	static public function faultSoapHandler($e, $controller){
		$response = ControllerResponse::getInstance();
		if(isset($_SERVER['HTTP_SOAPACTION'])){
			$faultMessage = str_replace("\n", "", html_entity_decode($e->getMessage(), ENT_COMPAT, "UTF-8"));
			$response->setResponseType(ControllerResponse::RESPONSE_OTHER);
			$response->setResponseAdapter('xml');
			$bodyElement = self::_createSOAPEnvelope();
			$faultElement = new DOMElement("Fault", "", self::$_envelopeNS);
			$bodyElement->appendChild($faultElement);
			$faultElement->appendChild(new DOMElement("faultcode", $e->getCode()));
			$faultElement->appendChild(new DOMElement("faultstring", $faultMessage));
			print self::$_domDocument->saveXML();
		} else {
			ob_clean();
			$e->showMessage();
			View::setContent(ob_get_contents());
			ob_end_clean();
			View::xhtmlTemplate('white');
		}
	}

}
