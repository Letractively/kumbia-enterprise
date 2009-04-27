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
	}

	/**
	 * Lee un recurso RSS apartir de su ubicación
	 *
	 * @param string $url
	 * @return boolean
	 */
	public function readRss($url){
		$rssContent = file_get_contents($url);
		if($rssContent!==false){
			$this->readRssString($rssContent);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Lee un recurso RSS apartir de un string XML
	 *
	 * @param string $rssString
	 */
	public function readRssString($rssString){
		$this->_dom->loadXML($rssString);
	}

	/**
	 * Devuelve los items del RSS
	 *
	 * @return array
	 */
	public function getItems(){
		$items = $this->_dom->getElementsByTagName('item');
		$feedItems = array();
		foreach($items as $item){
			$feedItem = new FeedItem();
			foreach($item->childNodes as $child){
				switch($child->localName){
					case 'title':
						$feedItem->setTitle($child->nodeValue);
						break;
					case 'link':
						$feedItem->setLink($child->nodeValue);
						break;
					case 'pubDate':
						$feedItem->setPubDate($child->nodeValue);
						break;
				}
			}
			$feedItems[] = $feedItem;
		}
		return $feedItems;
	}

}
