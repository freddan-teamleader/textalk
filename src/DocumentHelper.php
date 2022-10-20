<?php


namespace Fredrik\Dtbook;

class DocumentHelper
{

    private $counter = 0;
    private static $instance = null;
    private $indataDir;
    private $outputDir;


    private function __construct($indataDir, $outputDir)
    {
        $this->indataDir = $indataDir;
        $this->outputDir = $outputDir;
    }

    public static function newInstance($indataDir, $outputDir): DocumentHelper
    {
        if (self::$instance == null) {
            self::$instance = new DocumentHelper($indataDir, $outputDir);
        }

        return self::$instance;
    }

    public static function getInstance(): DocumentHelper
    {
        if (self::$instance == null) {
            throw new \Exception("Instance not initialized", 1);
        }
        return self::$instance;
    }

    function nextId(): string
    {
        $currentDate = new \DateTime();
        $d = (int)$currentDate->format('Hism');

        return dechex($d + (++$this->counter));
    }

    function saveImg(string $source): string
    {
        $src = $this->indataDir . $source;
        $nr = hash_file('md5', $src);
        $currentDate = date("ymd");
        if (!file_exists($this->outputDir . 'images/')) {
            mkdir($this->outputDir . 'images/', 0770, true);
        }
        $target = 'images/ar-' . $currentDate . '-tf-' . $nr . '-1-normal.jpg';
        copy($src, $this->outputDir . $target);
        return $target;
    }

    function append($element, $parent, $dom, $fileName)
    {
        foreach ($element->childNodes as $child) {
            $this->appendChild($child, $parent, $dom, $fileName);
        }
    }

    function appendChild(\DOMText | \DOMElement $element, \DOMElement $parent, \DOMDocument $dom, string $fileName)
    {
        switch ($element->nodeType) {
            case XML_ELEMENT_NODE:
                switch ($element->tagName) {
                    case 'pagenum':
                        $id = $this->nextId();
                        $parent->appendChild(XMLUtil::createDOMElement($dom, 'div', array(
                            'epub:type' => 'pagebreak',
                            'class' => 'page-front',
                            'id' => $id,
                            'title' => $element->nodeValue
                        )));
                        Nav::getInstance()->addPageTarget($fileName . '#' . $id, $element->nodeValue);
                        break;
                    case 'imggroup':
                        $parent = $parent->appendChild(XMLUtil::createDOMElement($dom, 'figure', array(
                            'class' => 'image',
                        )));
                        self::append($element,  $parent, $dom, $fileName);
                        break;
                    case 'img':
                        $src = self::saveImg($element->getAttribute('src'));
                        XMLUtil::addImage($parent, $dom, $src, $this->nextId());
                        break;
                    case 'prodnote':
                        $figCaption = $dom->createElement('figcaption');
                        $parent->appendChild($figCaption);
                        $this->append($element,  $figCaption, $dom, $fileName);
                        break;
                    case (preg_match('/h[\d]/', $element->tagName) ? true : false):
                        $hElement = $dom->createElement($element->tagName);
                        $parent->appendChild($hElement);
                        $this->append($element,  $hElement, $dom, $fileName);
                        break;
                    case 'p':
                        $importedParagraphNode = XMLUtil::importAndAppend($dom, $element, $parent);
                        if ($element->parentNode->parentNode->tagName != 'imggroup') {
                            $importedParagraphNode->setAttribute('id', $this->nextId());
                            $importedParagraphNode->removeAttribute('class');
                        }
                        break;
                    case 'list':
                        $ol = $dom->createElement('ol');
                        $ol->setAttribute('class', 'plain');
                        $parent->appendChild($ol);
                        $this->append($element,  $ol, $dom, $fileName);
                        break;
                    case 'lic':
                        $span = $dom->createElement('span');
                        $span->setAttribute('class', 'lic');
                        $parent = $parent->appendChild($span);
                        $this->append($element,  $span, $dom, $fileName);
                        break;
                    case 'note':
                        $section = $parent->appendChild(XMLUtil::createDOMElement(
                            $dom,
                            'section',
                            array(
                                'epub:type' => 'footnotes',
                                'class' => 'footnotes',
                                'id' => $this->nextId(),
                            )
                        ));
                        $ol = $dom->createElement('ol');
                        $section->appendChild($ol);
                        $li = $parent->appendChild(XMLUtil::createDOMElement(
                            $dom,
                            'li',
                            array(
                                'epub:type' => 'footnote',
                                'class' => 'notebody',
                                'id' => $this->nextId(), //TODO
                            )
                        ));
                        $ol->appendChild($li);
                        $this->append($element, $li, $dom, $fileName);
                        break;
                    case (preg_match('/level([\d])/', $element->tagName, $matches) ? true : false):
                        $section = $parent->appendChild($dom->createElement('section'));
                        $id = $this->nextId();
                        $section->setAttribute('id', $id);
                        $title = $this->getSectionTitle($element, $matches[1]);
                        Nav::getInstance()->addNavPoint($fileName . '#' . $id, $title);
                        $this->append($element, $section, $dom, $fileName);
                        break;
                    case 'table':
                        $tableE = $dom->createElement($element->tagName);
                        $tableE->setAttribute('id', $this->nextId());
                        $parent->appendChild($tableE);
                        $tBodyE = $dom->createElement('tbody');
                        $tBodyE->setAttribute('id', $this->nextId());
                        $tableE->appendChild($tBodyE);
                        $this->append($element, $tBodyE, $dom, $fileName);
                        break;
                    case 'tr':
                    case 'dl':
                    case 'li':
                        $tr = $dom->createElement($element->tagName);
                        $tr->setAttribute('id', $this->nextId());
                        $parent->appendChild($tr);
                        $this->append($element, $tr, $dom, $fileName);
                        break;
                    case 'td':
                    case 'th':
                    case 'dt':
                    case 'dd':
                        $tE = XMLUtil::importAndAppend($dom, $element, $parent, ['rowspan', 'colspan']);
                        $tE->setAttribute('id', $this->nextId());
                        break;
                }

                break;
            case XML_TEXT_NODE:
                $parent->appendChild($dom->createTextNode($element->nodeValue));
                break;
        }
    }

    private function getSectionTitle($element, $headerStyleNumber)
    {
        $title = 'Namnlös';
        $h = $element->getElementsByTagName('h' . $headerStyleNumber);
        if ($h->count() > 0) {
            $title = $h->item(0)->nodeValue;
        }
        return $title;
    }
}
