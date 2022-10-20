<?php

namespace Fredrik\Dtbook;

class Chapters extends Base
{

    static function process(\DOMElement $orgDoc, $metadata)
    {
        $bodymatterList = $orgDoc->getElementsByTagName('bodymatter');

        foreach ($bodymatterList as $bodymatter) {
            $bodymatterLevel1List = $bodymatter->getElementsByTagName('level1');
            foreach ($bodymatterLevel1List as $level1) {
                $resDoc = XMLUtil::newDocument($metadata);
                self::processLevel1($level1, $resDoc);
            }
        }
    }
    static function processLevel1(\DOMElement $level1, \DOMDocument $resDoc)
    {
        $id = substr($level1->getAttribute('id'), 7);
        parent::processBase($level1, $resDoc, $id, 'bodymatter', 'chapter', function () use ($level1) {
            return self::getTitle($level1);
        });
    }

    private static function getTitle(\DOMElement $level)
    {
        $class = $level->getAttribute('class');
        if ($class == 'footnotes') {
            return 'Kapitel';
        }
        $h1Element = $level->getElementsByTagName('h1');
        if (count($h1Element) == 1) {
            return $h1Element[0]->nodeValue;
        }
    }
}
