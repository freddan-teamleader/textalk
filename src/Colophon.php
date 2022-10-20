<?php

namespace Fredrik\Dtbook;

class Colophon extends Base
{
    static function process(\DOMElement $level1, \DOMDocument $resDoc)
    {
        parent::processBase($level1, $resDoc, 5, 'frontmatter', 'colophon', function () {
            return 'Namnlös';
        });
    }
}
