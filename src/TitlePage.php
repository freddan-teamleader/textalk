<?php

namespace Fredrik\Dtbook;

class TitlePage extends Base
{
    static function process(\DOMElement $level1, \DOMDocument $resDoc)
    {
        parent::processBase($level1, $resDoc, 4, 'frontmatter', 'titlepage', function () {
            return 'Namnlös';
        });
    }
}
