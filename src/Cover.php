<?php

namespace Fredrik\Dtbook;

class Cover
{
    static function process(\DOMElement $level1, \DOMDocument $resDoc)
    {
        $fileName = FileUtils::getInstance()->createFileName(1, 'cover');

        $doctitle = $level1->parentNode->getElementsByTagName('doctitle')[0]->nodeValue;

        $root = $resDoc->documentElement;

        $id = 'c1';

        $source = $fileName . '#' . $id;
        Nav::getInstance()->addNavPoint($source, 'Omslagssida');

        $bodyE = $root->appendChild(XMLUtil::createDOMElement($resDoc, 'body', array(
            'id' => $id,
            'epub:type' => 'cover'
        )));

        //DocumentHelper::getInstance() append($element, $bodyE, $fileName, $dom);
        self::append($level1, $bodyE, $doctitle, $resDoc, $fileName, $source);

        XMLUtil::getInstance()->saveXML($resDoc, $fileName);
    }

    static function append(
        \DomElement $element,
        \DOMElement $body,
        string $doctitle,
        \DOMDocument $dom,
        string $fileName,
        string $source
    ) {
        foreach ($element->childNodes as $child) {
            switch ($child->nodeType) {
                case XML_ELEMENT_NODE:
                    if ($child->tagName == 'prodnote') {
                        $sectionE = $dom->createElement('section');
                        $body->appendChild($sectionE);
                        $sectionE->setAttribute('class', $child->getAttribute('class'));
                        $aside = XMLUtil::createDOMElement($dom, 'aside', array(
                            'epub:type' => 'z3998:production',
                            'id' => DocumentHelper::getInstance()->nextId(),
                            'class' => $child->tagName
                        ));

                        if ($child->getAttribute('class') == 'frontcover') {
                            $id = 'c2';
                            $sectionE->setAttribute('id', $id);
                            $sectionE->appendChild(XMLUtil::createDOMElement($dom, 'h2', array(
                                'id' => 'frontcover-heading'
                            ), $doctitle));
                            $parent = $sectionE->appendChild($aside);
                            Nav::getInstance()->addNavPoint($fileName . '#' . $id, $doctitle, $source);
                            DocumentHelper::getInstance()->append($child, $parent, $dom, $fileName);
                        } else {
                            $id = 'c3';
                            Nav::getInstance()->addNavPoint($fileName . '#' . $id, 'Baksida', $source);
                            $sectionE->setAttribute('id', $id);
                            $parent = $sectionE->appendChild($aside);
                            self::appendRearCover(
                                $child,
                                $parent,
                                $dom
                            );
                        }
                    }
                    self::append(
                        $child,
                        $body,
                        $doctitle,
                        $dom,
                        $fileName,
                        $source
                    );
                    break;
            }
        }
    }


    static function appendRearCover(
        \DOMElement $element,
        \DOMElement $parent,
        \DOMDocument $dom
    ) {
        $paragraphNodes = $element->getElementsByTagName('p');
        foreach ($paragraphNodes as $paragraphNode) {
            $importedParagraphNode = XMLUtil::importAndAppend($dom, $paragraphNode, $parent);
            $importedParagraphNode->setAttribute('id', DocumentHelper::getInstance()->nextId());
        }
    }
}
