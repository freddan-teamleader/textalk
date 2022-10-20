<?php

namespace Fredrik\Dtbook;

class Base
{
    static function processBase(
        \DOMElement $level1,
        \DOMDocument $resDoc,
        int $number,
        string $className,
        string $name,
        $getTitle
    ) {
        $fileName = FileUtils::getInstance()->createFileName($number, $name === '' ? $className : $name);

        $root = $resDoc->documentElement;

        $id = DocumentHelper::getInstance()->nextId();

        $title = $getTitle($level1);

        Nav::getInstance()->addNavPoint($fileName . '#' . $id, $title);

        $bodyE = $root->appendChild(XMLUtil::createDOMElement($resDoc, 'body', array(
            'id' => $id,
            'epub:type' => trim("$className $name"),
            'class' => 'nonstandardpagination'
        )));

        DocumentHelper::getInstance()->append($level1, $bodyE, $resDoc, $fileName);

        XMLUtil::getInstance()->saveXML($resDoc, DIRECTORY_SEPARATOR . $fileName);
    }
}
