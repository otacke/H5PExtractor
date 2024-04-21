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
 * Class for generating HTML for H5P content.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class H5PFileHandler
{
    /**
     * Constructor.
     *
     * @param string $file        The H5P file to handle.
     * @param string $uploadsPath The path to the uploads directory.
     *                            Will default to "uploads" in current directory.
     */
    public function __construct($file, $uploadsPath)
    {
        $this->baseDirectory = $uploadsPath;

        try {
            $this->filesDirectory = $this->extractContent($file);
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }

        try {
            // TODO: separate class for H5P information (?)
            $this->h5pInfo = $this->extractH5PInformation();
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }

        for ($i = 0; $i < count($this->h5pInfo['preloadedDependencies']); $i++) {
            if (isset($this->h5pInfo['majorVersion'])) {
                break;
            }

            $dependency = $this->h5pInfo['preloadedDependencies'][$i];
            if ($dependency['machineName'] === $this->h5pInfo['mainLibrary']) {
                $this->h5pInfo['majorVersion'] = $dependency['majorVersion'];
                $this->h5pInfo['minorVersion'] = $dependency['minorVersion'];
            }
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if (!isset($this->filesDirectory)) {
            return;
        }

        $this->collectGarbage();
        $this->deleteDirectory($this->filesDirectory);
    }

    /**
     * Check if the H5P file is okay.
     *
     * @return bool True if the file is okay, false otherwise.
     */
    public function isFileOkay()
    {
        return isset($this->filesDirectory) && $this->filesDirectory !== false;
    }

    /**
     * Get the base directory for the H5P files.
     *
     * @return string The file directory for the H5P files.
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * Get the file directory for the H5P files.
     *
     * @return string The file directory for the H5P files.
     */
    public function getFilesDirectory()
    {
        return $this->filesDirectory;
    }

    /**
     * Get the H5P content informaton from h5p.json.
     *
     * @param string $property The property to get or null to get full information.
     *
     * @return string|array|null  H5P content type CSS, null if not available.
     */
    public function getH5PInformation($property = null)
    {
        if (!isset($this->h5pInfo)) {
            return null;
        }

        return isset($property) ? $this->h5pInfo[$property] : $this->h5pInfo;
    }

    /**
     * Get the H5P content parameters from the content.json file.
     *
     * @return array|bool Content parameters if file exists, false otherwise.
     */
    public function getH5PContentParams()
    {
        $extractDir = $this->baseDirectory . '/' . $this->filesDirectory;
        if (!is_dir($extractDir)) {
            return false;
        }

        $contentDir = $extractDir . '/content';
        if (!is_dir($contentDir)) {
            return false;
        }

        $contentJsonFile = $contentDir . '/content.json';
        if (!file_exists($contentJsonFile)) {
            return false;
        }

        $contentContents = file_get_contents($contentJsonFile);
        $jsonData = json_decode($contentContents, true);

        if ($jsonData === null) {
            return false;
        }

        return $jsonData;
    }

    /**
     * Get the CSS for the given H5P content type.
     *
     * @param string $machineName  The machine name of the content type.
     * @param int    $majorVersion The major version of the content type.
     * @param int    $minorVersion The minor version of the content type.
     *
     * @return string|bool CSS for content type, false if not available.
     */
    public function getH5PContentTypeCSS(
        $machineName,
        $majorVersion = null,
        $minorVersion = null
    ) {
        $extractDir = $this->baseDirectory . '/' . $this->filesDirectory;
        if (!is_dir($extractDir)) {
            return false;
        }

        $assumedContentTypeDir
            = $machineName . '-' . $majorVersion . '.' . $minorVersion;

        $contentDirs = scandir($extractDir);

        /*
         * Newer versions of H5P core also add the patch version to the directory
         * name. We don't know the patch version, however.
         */
        $dirMatching = '';
        foreach ($contentDirs as $contentDir) {
            if ($dirMatching !== '') {
                continue;
            }

            if (strpos($contentDir, $assumedContentTypeDir) !== false) {
                $dirMatching = $contentDir;
            }
        }

        $contentTypeDir = $extractDir . '/' . $dirMatching;

        if (!is_dir($contentTypeDir)) {
            return false;
        }

        $libraryJson = $this->getLibraryJson($contentTypeDir);
        if ($libraryJson === false || !isset($libraryJson['preloadedCss'])) {
            return false;
        }

        $css = '';
        for ($i = 0; $i < count($libraryJson['preloadedCss']); $i++) {
            $cssFile
                = $contentTypeDir . '/' . $libraryJson['preloadedCss'][$i]['path'];

            $newCss = file_get_contents($cssFile);
            $newCss = CSSUtils::replaceUrlsWithBase64($newCss, dirname($cssFile));
            $css .= $newCss;
        }

        return $css;
    }

    /**
     * Extract the content of the H5P file to a temporary directory.
     *
     * @param string $file The H5P file to extract.
     *
     * @return string|false Name of temporary directory or false.
     */
    private function extractContent($file)
    {
        // Create temporary directory with time stamp+uuid for garbage collection
        $directoryName = time() . '-' . GeneralUtils::createUUID();

        $extractDir = $this->baseDirectory . '/' . $directoryName;
        if (!is_dir($extractDir)) {
            if (!is_writable($this->baseDirectory)) {
                throw new \Exception(
                    'Upload directory ' . $extractDir . ' is not writable.'
                );
            }

            if (!mkdir($extractDir, 0777, true) && !is_dir($extractDir)) {
                throw new \Exception(
                    'Could not create upload directory ' . $extractDir . '.'
                );
            }
        }

        $zip = new \ZipArchive;

        if ($zip->open($file) !== true) {
            throw new \Exception('Error extracting H5P file ZIP archive.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $zip->extractTo($extractDir, $filename);
        }
        $zip->close();

        return $directoryName;
    }

    /**
     * Get the library.json file from the given directory.
     *
     * @param string $dir The directory to get the library.json file from.
     *
     * @return array|bool JSON data if the file exists and is valid, false otherwise.
     */
    private function getLibraryJson($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $libraryJsonFile = $dir . '/library.json';
        if (!file_exists($libraryJsonFile)) {
            return false;
        }

        $jsonContents = file_get_contents($libraryJsonFile);
        $jsonData = json_decode($jsonContents, true);

        if ($jsonData === null) {
            return false;
        }

        return $jsonData;
    }

    /**
     * Get the H5P content informaton from h5p.json.
     *
     * @return string|null The H5P content type CSS if it exists, null otherwise.
     */
    private function extractH5PInformation()
    {
        $extractDir = $this->baseDirectory . '/' . $this->filesDirectory;

        if (!is_dir($extractDir)) {
            throw new \Exception(
                'Directory with extracted H5P files does not exist.'
            );
        }

        $h5pJsonFile = $extractDir . '/h5p.json';

        if (!file_exists($h5pJsonFile)) {
            throw new \Exception('h5p.json file does not exist in the archive.');
        }

        $jsonContents = file_get_contents($h5pJsonFile);
        $jsonData = json_decode($jsonContents, true);

        if ($jsonData === null) {
            throw new \Exception('Error decoding h5p.json file.');
        }

        return $jsonData;
    }

    /**
     * Delete a directory and its contents.
     *
     * @param string $dir The directory to delete.
     *
     * @return void
     */
    private function deleteDirectory($dir)
    {
        $dirWithBase = $this->baseDirectory . '/' . $dir;
        if (!is_dir($dirWithBase)) {
            return;
        }

        $files = array_diff(scandir($dirWithBase), array('.', '..'));
        foreach ($files as $file) {
            if (is_dir($dirWithBase . '/' . $file)) {
                $this->deleteDirectory($dir . '/' . $file);
            } else {
                unlink($dirWithBase . '/' . $file);
            }
        }

        rmdir($dirWithBase);
    }

    /**
     * Delete directories in uploads directory that are older than time difference.
     *
     * @param int $timediff The time difference in seconds.
     *
     * @return void
     */
    private function collectGarbage($timediff = 60)
    {
        $currentTimestamp = time();

        $directories = glob($this->baseDirectory . '/*', GLOB_ONLYDIR);

        foreach ($directories as $dir) {
            $dirName = basename($dir);
            $timestamp = explode('-', $dirName)[0];

            if ($currentTimestamp - $timestamp >= $timediff) {
                $this->deleteDirectory($dirName);
            }
        }
    }
}
