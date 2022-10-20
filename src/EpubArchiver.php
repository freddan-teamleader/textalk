<?php

namespace Fredrik\Dtbook;

class EpubArchiver
{
    static function createArtchive(string $sourcPath, string $targetPath, string $identifier, $output)
    {
        $zip = new \ZipArchive;
        $targetFile = $targetPath . '/' . $identifier . '.epub';

        if ($zip->open($targetFile, \ZipArchive::CREATE) === TRUE) {
            self::addFile($zip, $sourcPath, '');
        }

        $output->writeln("saved epub at $targetFile");

        $zip->close();
    }

    private static function addFile(\ZipArchive $zip, string $pathdir, string $zipDir)
    {
        $dir = opendir($pathdir);
        while ($file = readdir($dir)) {
            $d = is_dir($pathdir . $file);
            if (is_file($pathdir . $file) && !str_starts_with($file, '.')) {
                $zip->addFile($pathdir . $file, $zipDir . $file);
            } elseif (is_dir($pathdir . $file) && !str_starts_with($file, '.')) {
                self::addFile($zip, $pathdir . $file . '/', $zipDir . $file . '/');
            }
        }
    }
}
