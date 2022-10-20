<?php

namespace Fredrik\Dtbook;

class Preface extends Base
{
    static function process(\DOMElement $level1, \DOMDocument $resDoc)
    {
        parent::processBase($level1, $resDoc, 7 , 'frontmatter', 'preface', function () use ($level1) {
            return self::getTitle($level1);
        });
    }

    private static function getTitle(\DOMElement $level)
    {
        $h1Element = $level->getElementsByTagName('h1');
        if (count($h1Element) == 1) {
            return $h1Element[0]->nodeValue;
        }
    }
}
