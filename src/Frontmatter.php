<?php

namespace Fredrik\Dtbook;

class Frontmatter extends Base
{
    static function process(\DOMElement $level1, \DOMDocument $resDoc)
    {
        parent::processBase($level1, $resDoc, 2, '', 'frontmatter', function () {
            return 'Namnlös';
        });
    }
}
