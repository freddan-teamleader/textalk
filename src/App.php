<?php

namespace Fredrik\Dtbook;

use Fredrik\Dtbook\Nav;
use Fredrik\Dtbook\EpubArchiver;

class App
{
    function initialize(string $dtbook, array $metadata, string $outDir)
    {
        Nav::newInstance($metadata);

        FileUtils::newInstance($metadata);

        XMLUtil::newInstance($outDir);

        $indataDir = pathinfo($dtbook, PATHINFO_DIRNAME) . '/';

        DocumentHelper::newInstance($indataDir, $outDir);
    }

    function convert(string $dtbook, string $outputDir, $output)
    {
        $xmlDoc = $this->loadDTBook($dtbook);

        $metadata = XMLUtil::getMetadata($xmlDoc);

        $tempOutDir = 'result/'/* sys_get_temp_dir() */ . $metadata[XMLUtil::META_IDENTIFIER] . '/';

        $epubDir = $tempOutDir . 'EPUB/';
        if (!file_exists($epubDir)) {
            mkdir($epubDir, 0770, true);
        }

        $this->initialize($dtbook, $metadata, $epubDir);

        Front::process($xmlDoc, $metadata);
        Chapters::process($xmlDoc, $metadata);
        Back::process($xmlDoc, $metadata);

        $this->finalize($tempOutDir, $outputDir, $metadata, $output);
    }

    function finalize(string $tempOutDir, string $outputDir, array $metadata, $output)
    {
        Nav::getInstance()->save();

        EpubArchiver::createArtchive($tempOutDir, $outputDir, $metadata[XMLUtil::META_IDENTIFIER], $output);
    }

    function loadDTBook(string $dtbook): \DOMElement
    {
        // TODO: Check if file exists
        $xmlStr = file_get_contents($dtbook);
        $xmlDoc = new \SimpleXMLElement($xmlStr);
        return dom_import_simplexml($xmlDoc);
    }
}
