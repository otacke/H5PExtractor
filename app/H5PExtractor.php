<?php
/**
 * Proof of concept code for extracting and displaying H5P content server-side.
 *
 * PHP version 8
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */

namespace H5PExtractor;

/**
 * Main class.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class H5PExtractor
{
    /**
     * Constructor.
     *
     * @param array $config The configuration.
     */
    public function __construct($config)
    {
        if (!isset($config)) {
            $config = [
                'uploadsPath' => __DIR__ . '/../uploads'
            ];
        }

        $this->config = $config;

        include_once __DIR__ .'/H5PFileHandler.php';
        include_once __DIR__ .'/generators/HtmlGeneratorMain.php';
        include_once __DIR__ .'/generators/PlainTextGeneratorMain.php';
    }

    /**
     * Done.
     *
     * @param string|null $result The result. Should be null if there is an error.
     * @param string|null $error  The error. Should be null if there is no error.
     *
     * @return array The result or error.
     */
    private function _done($result, $error = null)
    {
        if (isset($error)) {
            $result = null;
        } elseif (!isset($result)) {
            $error = 'Something went wrong, but I dunno what, sorry!';
        }

        return [
            'result' => $result,
            'error' => $error
        ];
    }

    /**
     * Extract the H5P content from the given file.
     *
     * @param array $params The parameters. file (tmp file), format (html, text).
     *
     * @return array The result or error.
     */
    public function extract($params)
    {
        if (!isset($params['file'])) {
            $this->_done(
                null,
                'It seems that no file was provided.'
            );
        }

        if (!isset($params['format'])) {
            $params['format'] = 'html';
        }

        $file = $params['file'];

        $fileSize = filesize($file);
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($fileInfo, $file);

        if ($fileSize === 0) {
            return $this->_done(null, 'The file is empty.');
        }

        $fileSizeLimit = 1024 * 1024 * 20; // 20 MB
        if ($fileSize > $fileSizeLimit) {
            return $this->_done(
                null,
                'The file is larger than the limit of '. $fileSizeLimit .' bytes.'
            );
        }

        if ($fileType !== 'application/zip') {
            return $this->_done(
                null,
                'The file is not a valid H5P file / ZIP archive.'
            );
        }

        try {
            $h5pFileHandler = new H5PFileHandler(
                $file,
                $this->config['uploadsPath']
            );
        } catch (\Exception $error) {
            return $this->_done(null, $error->getMessage());
        }

        if (!$h5pFileHandler->isFileOkay()) {
            return $this->_done(
                null,
                'The file does not seem to follow the H5P specification.'
            );
        }

        switch ($params['format']) {
        case 'html':
            $generator = new HtmlGeneratorMain($h5pFileHandler);
            break;

        case 'text':
            $generator = new PlainTextGeneratorMain($h5pFileHandler);
            break;

        default:
            $generator = null;
        }

        if ($generator === null || !method_exists($generator, 'create')) {
            return $this->_done(
                null,
                'No handler for specified format available.'
            );
        }

        try {
            $extract = $generator->create();
        } catch (\Exception $error) {
            return $this->_done(
                null,
                $error->getMessage()
            );
        }

        $h5pFileHandler = null;

        return $this->_done($extract);
    }
}
