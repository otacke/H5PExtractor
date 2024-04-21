<?php

spl_autoload_register(function ($class) {
    static $classmap;

    if (!isset($classmap)) {
        $classmap = [
        'H5PExtractor\H5PFileHandler' => 'H5PFileHandler.php',
        'H5PExtractor\HtmlGeneratorInterface' => 'generators/HtmlGeneratorInterface.php',
        'H5PExtractor\PlainTextGeneratorInterface' => 'generators/PlainTextGeneratorInterface.php',
        'H5PExtractor\HtmlGeneratorMain' => 'generators/HtmlGeneratorMain.php',
        'H5PExtractor\PlainTextGeneratorMain' => 'generators/PlainTextGeneratorMain.php',
        'H5PExtractor\CSSUtils' => 'utils/CSSUtils.php',
        'H5PExtractor\FileUtils' => 'utils/FileUtils.php',
        'H5PExtractor\GeneralUtils' => 'utils/GeneralUtils.php',
        'H5PExtractor\H5PUtils' => 'utils/H5PUtils.php',
        'H5PExtractor\TextUtils' => 'utils/TextUtils.php',
        ];
    };

    if (isset($classmap[$class])) {
        require_once __DIR__ . '/' . $classmap[$class];
    }
});
