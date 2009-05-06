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
class WebServiceClient extends SoapClient {

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
		#$options['trace'] = true;
		parent::__construct($options['wsdl'], $options);
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
