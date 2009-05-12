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
 * @subpackage 	Client
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * WebServiceClient
 *
 * Cliente para invocar servicios Web
 *
 * @category	Kumbia
 * @package 	Soap
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
#class WebServiceClient extends SoapClient {

class WebServiceClient {

	/**
	 * Namespace de nodos Envelope
	 *
	 * @var string
	 * @staticvar
	 */
	private static $_envelopeNS = 'http://schemas.xmlsoap.org/soap/envelope/';

	/**
	 * Namespace del XML Schema Instance (xsi)
	 *
	 * @var string
	 */
	private static $_xmlSchemaInstanceNS = 'http://www.w3.org/2001/XMLSchema-instance';

	/**
	 * DOMDocument Base
	 *
	 * @var DOMDocument
	 */
	private $_domDocument;

	/**
	 * Nodo Raiz de la respuesta SOAP
	 *
	 * @var DOMElement
	 */
	private $_rootElement;

	/**
	 * Nodo Body de la respuesta SOAP
	 *
	 * @var DOMElement
	 */
	private $_bodyElement;

	/**
	 * Transporte usado para generar las peticiones
	 *
	 * @var Http
	 */
	private $_transport;

	/**
	 * Opciones del servicio
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Constructor del cliente del Servicio
	 *
	 * @param string $wsdl
	 * @param array $options
	 */
	public function __construct($options){
		if(!is_array($options)){
			$options = array('wsdl' => null, 'location' => $options);
		}
		if(!isset($options['wsdl'])){
			$options['wsdl'] = null;
		}
		if(!isset($options['uri'])){
			$options['uri'] = 'http://app-services';
		}
		if(!isset($options['encoding'])){
			$options['encoding'] = 'UTF-8';
		}
		if(!isset($options['compression'])){
			$options['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
		}
		$this->_transport = new HttpRequest($options['location'], HttpRequest::METH_POST);
		#$options['trace'] = true;
		$this->_options = $options;
		#parent::__construct($options['wsdl'], $options);
	}

	/**
	 * Crea el SOAP Envelope de una petición
	 *
	 * @return DOMElement
	 */
	private function _createSOAPEnvelope(){
		$this->_domDocument = new DOMDocument('1.0', 'UTF-8');
		$this->_rootElement = $this->_domDocument->createElementNS(self::$_envelopeNS, 'SOAP-ENV:Envelope');
		$this->_rootElement->setAttribute('xmlns:ns1', $this->_options['uri']);
		$this->_rootElement->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
		$this->_rootElement->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$this->_rootElement->setAttribute('xmlns:SOAP-ENC', 'http://schemas.xmlsoap.org/soap/encoding/');
		$this->_domDocument->appendChild($this->_rootElement);
		$this->_bodyElement = new DOMElement('Body', '', self::$_envelopeNS);
		$this->_rootElement->appendChild($this->_bodyElement);
		return $this->_bodyElement;
	}

	/**
	 * Agrega un parámetro a la petición SOAP
	 *
	 * @param int $n
	 * @param string $param
	 */
	private function _addArgument($n, $param){
		if(is_integer($param)){
			return '<param'.$n.' xsi:type="xsd:int">'.$param.'</param'.$n.'>';
		} else {
		}
	}

	/**
	 * Realiza el llamado a una función del servicio
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	public function x__call($method, $arguments){
		try {

			$this->_transport->setHeaders(array(
				'Soap-Action' => $this->_options['uri'].'#'.$method
			));
			#$bodyElement = $this->_createSOAPEnvelope();
			#$nsAction = $this->_domDocument->createElementNS($this->_options['uri'], 'ns1:'.$method);
			#$nsAction = $this->_domDocument->createElementNS($this->_options['uri'], $method);
			$request = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://app-services" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body>';
			$n = 0;
			foreach($arguments as $argument){
				$request.=$this->_addArgument($n, $argument);
				$n++;
			}
			$request.='</SOAP-ENV:Body></SOAP-ENV:Envelope>';
			$httpMessage = new HttpMessage($request);
			$this->_transport->setRawPostData($request);
			$this->_transport->send();
		}
		catch(HttpInvalidParamException $e){

		}
		catch(HttpMalformedHeadersException $e){

		}
	}

	/**
	 * Realiza una peticion SOAP
	 *
	 * @param 	string $request
	 * @param 	string $location
	 * @param 	string $action
	 * @param 	int $version
	 **/
	public function __doRequest($request, $location, $action, $version){
		return @parent::__doRequest($request, $location, $action, $version);
	}

}
