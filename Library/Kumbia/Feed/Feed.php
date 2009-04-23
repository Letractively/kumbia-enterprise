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
 * @package		Feed
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * Feed
 *
 * Permite la creación/lectura de feeds
 *
 * @category	Kumbia
 * @package		Feed
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
class Feed {

	/**
	 * Objeto DOMDocument
	 *
	 * @var DOMDocument
	 */
	private $_dom;

	/**
	 * Constructor de Feed
	 *
	 * @param string $version
	 * @param string $encoding
	 */
	public function __construct($version='1.0', $encoding='UTF-8'){
		$this->_dom = new DOMDocument($version, $encoding);
		$root = $this->_dom->createElement('feed');
		$namespaces = array(
			'xmlns:openSearch' => "http://a9.com/-/spec/opensearch/1.1/",
			'xmlns:georss' => 'http://www.georss.org/georss',
			'xmlns:gd' => 'http://schemas.google.com/g/2005',
			'xmlns:feedburner' => 'http://rssnamespace.org/feedburner/ext/1.0',
			'gd:etag' => 'W/&quot;CU8FRX44eCp7ImA9WxVbF08.&quot;"'
		);
		foreach($namespaces as $namespace => $value){
			$root->setAttribute($namespace, $value);
		}
		$this->_dom->appendChild($root);
		print $this->_dom->saveXML();
	}

}
