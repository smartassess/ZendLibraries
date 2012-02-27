<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FeedSet.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Feed_Reader
 */
require_once 'Zend/Feed/Reader.php';

/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_FeedSet extends ArrayObject
{

    public $rss = null;

    public $rdf = null;

    public $atom = null;

    /**
     * Import a DOMNodeList from any document containing a set of links
     * for alternate versions of a document, which will normally refer to
     * RSS/RDF/Atom feeds for the current document.
     *
     * All such links are stored internally, however the first instance of
     * each RSS, RDF or Atom type has its URI stored as a public property
     * as a shortcut where the use case is simply to get a quick feed ref.
     *
     * Note that feeds are not loaded at this point, but will be lazy
     * loaded automatically when each links 'feed' array key is accessed.
     *
     * @param DOMNodeList $links
     * @param string $uri
     * @return void
     */
    public function addLinks(DOMNodeList $links, $uri)
    {
        foreach ($links as $link) {
            if (strtolower($link->getAttribute('rel')) !== 'alternate'
                || !$link->getAttribute('type') || !$link->getAttribute('href')) {
                continue;
            }
            if (!isset($this->rss) && $link->getAttribute('type') == 'application/rss+xml') {
                $this->rss = $this->_absolutiseUri(trim($link->getAttribute('href')), $uri);
            } elseif(!isset($this->atom) && $link->getAttribute('type') == 'application/atom+xml') {
                $this->atom = $this->_absolutiseUri(trim($link->getAttribute('href')), $uri);
            } elseif(!isset($this->rdf) && $link->getAttribute('type') == 'application/rdf+xml') {
                $this->rdf = $this->_absolutiseUri(trim($link->getAttribute('href')), $uri);
            }
            $this[] = new self(array(
                'rel' => 'alternate',
                'type' => $link->getAttribute('type'),
                'href' => $this->_absolutiseUri(trim($link->getAttribute('href')), $uri),
            ));
        }
    }

    /**
     *  Attempt 