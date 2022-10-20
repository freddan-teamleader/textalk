<?php

namespace Fredrik\Dtbook;

class FileUtils
{

    private $metadata;
    private static $instance = null;


    private function __construct($metadata)
    {
        $this->metadata = $metadata;
    }

    public static function newInstance($metadata): FileUtils
    {
        if (self::$instance == null) {
            self::$instance = new FileUtils($metadata);
        }

        return self::$instance;
    }

    public static function getInstance(): FileUtils
    {
        if (self::$instance == null) {
            throw new \Exception("Instance not initialized", 1);
        }
        return self::$instance;
    }

    function createFileName(string $id, string $name): string
    {
        return $this->metadata[XMLUtil::META_UUID] .
            '-' . sprintf('%03d', $id) . '-' . $name . '.xhtml';
    }
}
