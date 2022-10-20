<?php

namespace Fredrik\Dtbook;

class Nav
{
    private $playOrder = 0;
    private $pageTargets = array();

    private static $instance = null;

    private function __construct($metadata)
    {        
        $this->metadata = $metadata;
    }

    public static function newInstance($metadata)
    {
        if (self::$instance == null) {
            self::$instance = new Nav($metadata);
            self::$instance->initialize();
        }

        return self::$instance;
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            throw new \Exception("Instance not initialized", 1);
        }
        return self::$instance;
    }

    private function initialize()
    {
        $this->ncxdom = new \DOMDocument('1.0', 'UTF-8');

        $implementation = new \DOMImplementation();
        $this->ncxdom->appendChild($implementation->createDocumentType('ncx'));

        $root = $this->ncxdom->createElementNS('http://www.daisy.org/z3986/2005/ncx/', 'ncx');
        $this->ncxdom->appendChild($root);
        $root->setAttribute('xml:lang', 'sv');
        $root->setAttribute('version', '2005-1');

        $head = $this->ncxdom->createElement('head');
        $root->appendChild($head);

        $head->appendChild(XMLUtil::createDOMElement($this->ncxdom, 'meta', array(
            'name' => 'dc:uid',
            'content' => $this->metadata[XMLUtil::META_IDENTIFIER]
        )));

        $head->appendChild(XMLUtil::createDOMElement($this->ncxdom, 'meta', array(
            'name' => 'dc:depth',
            'content' => '4'
        )));

        $head->appendChild(XMLUtil::createDOMElement($this->ncxdom, 'meta', array(
            'name' => 'dc:generator',
            'content' => 'Webarch'
        )));

        $head->appendChild(XMLUtil::createDOMElement($this->ncxdom, 'meta', array(
            'name' => 'dc:totalPageCount',
            'content' => '161' //TODO
        )));

        $docTitle = $this->ncxdom->createElement('docTitle');
        $root->appendChild($docTitle);
        $text = $this->ncxdom->createElement('text');
        $text->appendChild($this->ncxdom->createTextNode(
            $this->metadata[XMLUtil::META_TITLE]
        ));
        $docTitle->appendChild($text);

        $this->navMap = $this->ncxdom->createElement('navMap');
        $root->appendChild(
            $this->navMap
        )->appendChild(
            $this->ncxdom->createElement('navLabel')
        )->appendChild(
            $this->ncxdom->createElement('text')
        )->appendChild(
            $this->ncxdom->createTextNode("Innehållsförteckning")
        );
    }

    function setTotalPageNumber(string $pagesTotalCount)
    {
        $head = $this->ncxdom->documentElement->appendChild();

        XMLUtil::createDOMElement($this->ncxdom, 'meta', array(
            'name' => 'dc:totalPageCount',
            'content', $pagesTotalCount
        ));
    }

    function setMaxPageNumber(string $pagesMaxCount)
    {
        $head = $this->ncxdom->documentElement->appendChild();

        XMLUtil::createDOMElement($this->ncxdom, 'meta', array(
            'name' => 'dc:maxPageNumber',
            'content', $pagesMaxCount
        ));
    }

    function addPoint()
    {
    }

    function addNavPoint(string $source, string $title, $parent = null)
    {
        if ($parent != null) {
            $this->navPoints[$parent][$source] = array('title' => $title);
        } else {
            $this->navPoints[$source] = array('title' => $title);
        }
    }

    function appendNavPoints(array $navPoints, $parent = null)
    {
        foreach ($navPoints as $source => $values) {
            $navPoint = $this->ncxdom->createElement('navPoint');
            $navPoint->setAttribute('id', 'navPoint-' . ++$this->playOrder);
            $navPoint->setAttribute('playOrder',  $this->playOrder);
            $parent->appendChild($navPoint);
            $this->addNavLabel($navPoint, $source, $values['title']);
            unset($values['title']);
            foreach ($values as $key => $value) {
                $this->appendNavPoints(array($key => $value), $navPoint);
            }
        }
    }


    function addNavLabel(\DOMElement $navPoint, string $source, string $textContent)
    {
        $navLabel = $this->ncxdom->createElement('navLabel');
        $navPoint->appendChild($navLabel);

        $text = $this->ncxdom->createElement('text');
        $text->appendChild($this->ncxdom->createTextNode($textContent));
        $navLabel->appendChild($text);

        if ($source != '') {
            $content = $this->ncxdom->createElement('content');
            $content->setAttribute('src', $source);
            $navPoint->appendChild($content);
        }
    }

    function addPageTarget(string $fileName, string $text)
    {
        $this->pageTargets[$text] = $fileName;
    }

    function appendPageTargets()
    {
        $pageList = $this->ncxdom->documentElement->appendChild($this->ncxdom->createElement('pageList'));
        $this->addNavLabel($pageList, '', 'Sidlista');

        foreach ($this->pageTargets as $text => $fileName) {
            $pageTarget = $pageList->appendChild(
                XMLUTIL::createDOMElement($this->ncxdom, 'pageTarget', array(
                    'id' => 'pageTarget-' . ++$this->playOrder,
                    'playOrder' =>  $this->playOrder,
                    'type' => ($text == 'i' ? 'special' : 'normal')
                ))
            );

            $this->addNavLabel($pageTarget, $fileName, $text);
        }
    }

    // function appendPageTargetsHTML()
    // { //<nav epub:type="page-list"><h1>Sidlista</h1><ol
    //     $nav = $this->ncxdom->documentElement->appendChild($this->ncxdom->createElement('nav'));
    //     $nav->setAttribute('epub:type', 'page-list');
    //     $this->addNavLabel($nav, '', 'Sidlista');
    //     $nav = $this->ncxdom->documentElement->appendChild($this->ncxdom->createElement('nav'));

    //     foreach ($this->pageTargets as $text => $fileName) {
    //         $pageTarget = $pageList->appendChild(
    //             $this->base->createElement($this->ncxdom, 'pageTarget', array(
    //                 'id' => 'pageTarget-' . ++$this->playOrder,
    //                 'playOrder' =>  $this->playOrder,
    //                 'type' => ($text == 'i' ? 'special' : 'normal')
    //             ))
    //         );

    //         $this->addNavLabel($pageTarget, $fileName, $text);
    //     }
    // }

    function save()
    {
        $this->appendNavPoints($this->navPoints, $this->navMap);
        $this->appendPageTargets();
        //$this->appendPageTargetsHTML();

        XMLUtil::getInstance()->saveXML($this->ncxdom, 'nav.ncx');
    }
}
