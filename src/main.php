<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

use Fredrik\Dtbook\App;

(new SingleCommandApplication())
    ->setName('Convert DtBook to epub') // Optional
    ->setVersion('1.0.0') // Optional
    ->addOption('dtbookFile', array('i', 'indata_file'), InputOption::VALUE_REQUIRED, 'Location of dtbook file')
    ->addOption('outputDir', array('o', 'output_dir'), InputOption::VALUE_REQUIRED, 'Directory for saving epub file')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $dtbook = $input->getOption('dtbookFile');
        $outputFile = $input->getOption('outputDir');
        $app = new App();
        $app->convert($dtbook, $outputFile, $output);
    })
    ->run();
