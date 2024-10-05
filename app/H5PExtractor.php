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
    private $config;

    /**
     * Constructor.
     *
     * @param array $config The configuration.
     *                     uploadsPath (string): The path to the uploads directory.
     *                     renderWidth (int): The width to render the content at in px.
     *                     target (string): [print|screen]
     *                     scope (string): [all|initial]
     *                     customCss (string): Custom CSS to be added to the output.
     */
    public function __construct($config = [])
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoloader.php';

        $defaultConfig = [
            'uploadsPath' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'uploads',
            'h5pContentUrl' => null,
            'h5pCoreUrl' => null,
            'h5pLibrariesUrl' => null,
            'baseFontSize' => 16,
            'fontFamily' => 'sans-serif',
            'renderWidth' => 1024,
            'renderWidths' => [],
            'target' => 'print',
            'scope' => 'all',
            'customCssPre' => '',
            'customCssPost' => ''
        ];

        foreach ($defaultConfig as $key => $value) {
            if (!isset($config[$key])) {
                $config[$key] = $value;
            }
        }

        $this->config = $config;
    }

    /**
     * Done.
     *
     * @param string|null $result The result. Should be null if there is an error.
     * @param string|null $error  The error. Should be null if there is no error.
     *
     * @return array The result or error.
     */
    private function done($result, $error = null)
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
    public function extract($params = [])
    {
        if (!isset($params['file'])) {
            return $this->done(
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
            return $this->done(null, 'The file is empty.');
        }

        $fileSizeLimit = 1024 * 1024 * 20; // 20 MB
        if ($fileSize > $fileSizeLimit) {
            return $this->done(
                null,
                'The file is larger than the limit of '. $fileSizeLimit .' bytes.'
            );
        }

        if ($fileType !== 'application/zip') {
            return $this->done(
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
            return $this->done(null, $error->getMessage());
        }

        if (!$h5pFileHandler->isFileOkay()) {
            return $this->done(
                null,
                'The file does not seem to follow the H5P specification.'
            );
        }

        switch ($params['format']) {
            case 'html':
                $generator = new HtmlGeneratorMain(
                    $h5pFileHandler,
                    floor($this->config['renderWidth']),
                    $this->config['renderWidths'],
                    $this->config['baseFontSize'],
                    $this->config['fontFamily'],
                    $this->config['target'],
                    $this->config['scope'],
                    $this->config['customCssPre'],
                    $this->config['customCssPost'],
                    $this->config['h5pContentUrl'],
                    $this->config['h5pCoreUrl'],
                    $this->config['h5pLibrariesUrl']
                );
                break;

            case 'text':
                $generator = new PlainTextGeneratorMain(
                    $h5pFileHandler,
                    $this->config['renderWidth'],
                    $this->config['target'],
                    $this->config['scope']
                );
                break;

            default:
                $generator = null;
        }

        if ($generator === null || !method_exists($generator, 'create')) {
            return $this->done(
                null,
                'No handler for specified format available.'
            );
        }

        try {
            $extract = $generator->create();
        } catch (\Exception $error) {
            return $this->done(
                null,
                $error->getMessage()
            );
        }

        $h5pFileHandler = null;

        return $this->done($extract);
    }
}
