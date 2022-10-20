<?php

namespace Fredrik\Dtbook;

class XMLUtil
{

    const META_IDENTIFIER = "dc:Identifier";
    const META_TITLE = "dc:Title";
    const META_UUID = "dtb:uid";

    const NAMESPACE = 'http://www.daisy.org/z3986/2005/dtbook/';
    const PREFIX = 'dtbns';

    private $outputDir;
    private static $instance = null;


    private function __construct($outputDir)
    {
        $this->outputDir = $outputDir;
    }

    public static function newInstance($outputDir)
    {
        if (self::$instance == null) {
            self::$instance = new XMLUtil($outputDir);
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

    static function getMetadata(\DOMElement $rootDom): array
    {
        $metadata = array();

        $xpath = new \DOMXpath($rootDom->ownerDocument);
        $xpath->registerNamespace(self::PREFIX, self::NAMESPACE);
        $metaNodeList = $xpath->query("//" .
            self::PREFIX . ":dtbook/" .
            self::PREFIX . ":head/" .
            self::PREFIX . ":meta");

        foreach ($metaNodeList as $meta) {
            switch ($meta->getAttribute('name')) {
                case self::META_IDENTIFIER:
                    $identifier = $meta->getAttribute('content');
                    $metadata[self::META_IDENTIFIER] = $identifier;
                    break;
                case self::META_TITLE:
                    $title = $meta->getAttribute('content');
                    $metadata[self::META_TITLE] = $title;
                    break;
                case self::META_UUID:
                    $uid = $meta->getAttribute('content');
                    $metadata[self::META_UUID] = $uid;
                    break;
            }
        }

        return $metadata;
    }

    static function newDocument(array $metaData)
    {
        $xmlDoc = new \DOMDocument('1.0', 'UTF-8');

        $implementation = new \DOMImplementation();
        $xmlDoc->appendChild($implementation->createDocumentType('html'));

        $root = $xmlDoc->createElementNS('http://www.w3.org/1999/xhtml', 'html');
        $root = $xmlDoc->appendChild($root);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:epub', 'http://www.idpf.org/2007/ops');
        $root->setAttributeNS('http://www.idpf.org/2007/ops', 'epub:prefix', 'z3998: http://www.daisy.org/z3998/2012/vocab/structure/#');
        $root->setAttribute('xml:lang', 'sv');
        $root->setAttribute('lang', 'sv');

        $head = $xmlDoc->createElement('head');
        $root->appendChild($head);

        $metaC = $xmlDoc->createElement('meta');
        $metaC->setAttribute('charset', 'UTF-8');
        $head->appendChild($metaC);

        $titleE = $xmlDoc->createElement('title');
        $titleE->appendChild($xmlDoc->createTextNode($metaData[self::META_TITLE]));
        $head->appendChild($titleE);

        $metaIdent = $xmlDoc->createElement('meta');
        $metaIdent->setAttribute('name', 'dc:identifier');
        $metaIdent->setAttribute('content', $metaData[self::META_IDENTIFIER]);
        $head->appendChild($metaIdent);

        $metaView = $xmlDoc->createElement('meta');
        $metaView->setAttribute('name', 'viewport');
        $metaView->setAttribute('content', 'width=device-width');
        $head->appendChild($metaView);

        $link = $xmlDoc->createElement('link');
        $link->setAttribute('href', 'css/epub.css');
        $link->setAttribute('rel', 'stylesheet');
        $link->setAttribute('type', 'text/css');
        $head->appendChild($link);

        return $xmlDoc;
    }

    static function createDOMElement(
        \DOMDocument $dom,
        string $elementName,
        array $attributes,
        string $text = null
    ): \DOMElement {
        $element = $dom->createElement($elementName);
        foreach ($attributes as $key => $value) {
            $element->setAttribute($key, $value);
        }

        if ($text != null) {
            $element->appendChild($dom->createTextNode($text));
        }

        return $element;
    }


    static function addImage(
        \DOMELement $parent,
        \DOMDocument $dom,
        string $src,
        string $id
    ) {
        $imgE = $dom->createElement('img');
        $imgE->setAttribute('src', $src);
        $imgE->setAttribute('alt', 'illustration');
        $imgE->setAttribute('id', $id);
        $parent->appendChild($imgE);
    }

    static function importAndAppend($dom, $element, $targetParent, array $deleteAttributes = null)
    {
        $imported = $dom->importNode($element, true);
        $targetParent->appendChild($imported);
        $targetParent->lastChild->removeAttributeNS(self::NAMESPACE, '');
        if ($deleteAttributes != null) {
            foreach ($deleteAttributes as $key) {
                $targetParent->lastChild->removeAttribute($key);
            }
        }

        return $imported;
    }

    function saveXML(\DOMDocument $xmlDoc, string $file)
    {
        $targetFile = $this->outputDir . DIRECTORY_SEPARATOR . $file;

        $xmlDoc->saveXML();
        $xmlDoc->formatOutput = true;
        $xmlDoc->save($targetFile);
    }
}
