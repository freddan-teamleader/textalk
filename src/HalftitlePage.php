<?php

namespace Fredrik\Dtbook;

class HalftitlePage extends Base
{
    static function process(\DOMElement $level1, \DOMDocument $resDoc)
    {
        parent::processBase($level1, $resDoc, 3, 'frontmatter', 'halftitlepage', function () use ($level1) {
            return self::getTitle($level1);
        });
    }

    private static function getTitle(\DOMElement $level)
    {
        $pElements = $level->getElementsByTagName('p');
        if (count($pElements) == 1) {
            return $pElements[0]->nodeValue;
        }
    }
}
