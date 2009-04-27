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
 * @version 	$Id: Feed.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * FeedItem
 *
 * Permite encapsular la información de un item en un recurso RSS
 *
 * @category	Kumbia
 * @package		Feed
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
class FeedItem {

	/**
	 * Titulo del item
	 *
	 * @var string
	 */
	private $_title;

	/**
	 * Enlace del item
	 *
	 * @var string
	 */
	private $_link;

	/**
	 * TTL del item
	 *
	 * @var string
	 */
	private $_ttl;

	/**
	 * Idioma del item
	 *
	 * @var string
	 */
	private $_language;

	/**
	 * Fecha de públicación del item
	 *
	 * @var string
	 */
	private $_pubDate;

	/**
	 * Constructor de FeedItem
	 *
	 */
	public function __construct(){

	}

	/**
	 * Establece el título del item
	 *
	 * @param string $title
	 */
	public function setTitle($title){
		$this->_title = $title;
	}

	/**
	 * Obtiene el titulo del item
	 *
	 * @return string
	 */
	public function getTitle(){
		return $this->_title;
	}

	/**
	 * Establece el link del item
	 *
	 * @param string $link
	 */
	public function setLink($link){
		$this->_link = $link;
	}

	/**
	 * Obtiene el link del item
	 *
	 * @return string
	 */
	public function getLink(){
		return $this->_link;
	}

	/**
	 * Establece la fecha de públicación del del item
	 *
	 * @param string $pubDate
	 */
	public function setPubDate($pubDate){
		$this->_pubDate = $pubDate;
	}

	/**
	 * Obtiene la fecha de públicación del item
	 *
	 * @return string
	 */
	public function getPubDate(){
		return $this->_pubDate;
	}

}
