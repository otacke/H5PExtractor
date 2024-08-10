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
    private $baseDirectory;
    private $filesDirectory;
    private $h5pInfo;

    /**
     * Constructor.
     *
     * @param string $file        The H5P file to handle.
     * @param string $uploadsPath The path to the uploads directory.
     *                            Will default to "uploads" in current directory.
     */
    public function __construct($file, $uploadsPath)
    {
        $h5pExtractorDir = $uploadsPath . DIRECTORY_SEPARATOR . 'h5p-extractor';

        if (!is_dir($h5pExtractorDir)) {
            if (!is_writable($uploadsPath)) {
                throw new \Exception(
                    'Upload directory ' . $uploadsPath . ' is not writable.'
                );
            }

            if (!mkdir($h5pExtractorDir, 0777, true) && !is_dir($h5pExtractorDir)) {
                throw new \Exception(
                    'Could not create upload directory ' . $h5pExtractorDir . '.'
                );
            }
        }

        $this->baseDirectory = $h5pExtractorDir;

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

        $this->h5pInfo['preloadedDependencies'] = array_merge(
            $this->getLibrariesFromSemantics($this->getH5PContentParams()), // soft dependencies
            $this->h5pInfo['preloadedDependencies'], // hard dependencies
        );

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
     * Get the libraries from the semantics of the H5P content.
     *
     * @param array $semantics The semantics of the H5P content.
     *
     * @return array The libraries of the H5P content.
     */
    private function getLibrariesFromSemantics($semantics)
    {
        $fullNames = [];

        // Traverse the JSON data recursively
        foreach ($semantics as $key => $value) {
            if ($key === 'library' && preg_match('/H5P\..+ \d+\.\d+/', $value)) {
                $fullNames[] = $value;
            } elseif (is_array($value) || is_object($value)) {
                $subMatches = $this->getLibrariesFromSemantics($value);
                $fullNames = array_merge($fullNames, $subMatches);
            }
        }

        $mappedValues = [];

        foreach ($fullNames as $name) {
            if (getType($name) === 'array') {
                $mappedValues[] = $name;
                continue;
            }

            $mappedValues[] = H5PUtils::getLibraryFromString($name);
        }

        return $mappedValues;
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

        return isset($property) ?
            $this->h5pInfo[$property] ?? null :
            $this->h5pInfo;
    }

    /**
     * Get the icon of the main H5P content.
     *
     * @param string $machineName The machine name of the content type to get icon for.
     *
     * @return
     */
    public function getIconPath($machineName = null)
    {
        $extractDir = $this->baseDirectory . DIRECTORY_SEPARATOR . $this->filesDirectory;
        if (!is_dir($extractDir)) {
            return false;
        }

        if (!isset($machineName)) {
            if (empty($this->h5pInfo['mainLibrary'])) {
                return false;
            }
            $machineName = $this->h5pInfo['mainLibrary'];
        }

        // We're operating on the file system, so we cannot use spaces
        $machineName = str_replace(' ', '-', $machineName);

        $pattern = $extractDir . DIRECTORY_SEPARATOR . $machineName . '*';

        $contentDirs = glob($pattern, GLOB_ONLYDIR);
        if (empty($contentDirs)) {
            return false;
        }

        $iconFile = $contentDirs[0] . DIRECTORY_SEPARATOR . 'icon.svg';
        if (!file_exists($iconFile)) {
            return false;
        }

        return $iconFile;
    }

    /**
     * Get the H5P content parameters from the content.json file.
     *
     * @return array|bool Content parameters if file exists, false otherwise.
     */
    public function getH5PContentParams()
    {
        $extractDir = $this->baseDirectory . DIRECTORY_SEPARATOR . $this->filesDirectory;
        if (!is_dir($extractDir)) {
            return false;
        }

        $contentDir = $extractDir . DIRECTORY_SEPARATOR . 'content';
        if (!is_dir($contentDir)) {
            return false;
        }

        $contentJsonFile = $contentDir . DIRECTORY_SEPARATOR . 'content.json';
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
        $extractDir = $this->baseDirectory . DIRECTORY_SEPARATOR . $this->filesDirectory;
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

        $contentTypeDir = $extractDir . DIRECTORY_SEPARATOR . $dirMatching;

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
                = $contentTypeDir . DIRECTORY_SEPARATOR . $libraryJson['preloadedCss'][$i]['path'];

            $newCss = file_get_contents($cssFile);
            $newCss = CSSUtils::simplifyFonts($newCss);
            $newCss = CSSUtils::replaceUrlsWithBase64($newCss, dirname($cssFile));
            $css .= $newCss;
        }

        return $css;
    }

    /**
     * Get the JS for the given H5P content type.
     *
     * @param string $machineName  The machine name of the content type.
     * @param int    $majorVersion The major version of the content type.
     * @param int    $minorVersion The minor version of the content type.
     *
     * @return string|bool CSS for content type, false if not available.
     */
    public function getH5PContentTypeJS(
        $machineName,
        $majorVersion = null,
        $minorVersion = null
    ) {
        $extractDir = $this->baseDirectory . DIRECTORY_SEPARATOR . $this->filesDirectory;
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

        $contentTypeDir = $extractDir . DIRECTORY_SEPARATOR . $dirMatching;

        if (!is_dir($contentTypeDir)) {
            return false;
        }

        $libraryJson = $this->getLibraryJson($contentTypeDir);
        if ($libraryJson === false || !isset($libraryJson['preloadedJs'])) {
            return false;
        }

        $js = '';
        for ($i = 0; $i < count($libraryJson['preloadedJs']); $i++) {
            $jsFile = $contentTypeDir . DIRECTORY_SEPARATOR .
                $libraryJson['preloadedJs'][$i]['path'];

            $js .= file_get_contents($jsFile);
        }

        return $js;
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

        $extractDir = $this->baseDirectory . DIRECTORY_SEPARATOR . $directoryName;
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

        $libraryJsonFile = $dir . DIRECTORY_SEPARATOR . 'library.json';
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
        $extractDir = $this->baseDirectory . DIRECTORY_SEPARATOR . $this->filesDirectory;

        if (!is_dir($extractDir)) {
            throw new \Exception(
                'Directory with extracted H5P files does not exist.'
            );
        }

        $h5pJsonFile = $extractDir . DIRECTORY_SEPARATOR . 'h5p.json';

        if (!file_exists($h5pJsonFile)) {
            throw new \Exception('h5p.json file does not exist in the archive.');
        }

        $jsonContents = file_get_contents($h5pJsonFile);
        $jsonData = json_decode($jsonContents, true);

        if ($jsonData === null) {
            throw new \Exception('Error decoding h5p.json file.');
        }

        // Ensure that the content types stylesheet is loaded last
        $mainLibrary = $jsonData['mainLibrary'];
        $preloadedDependencies = $jsonData['preloadedDependencies'] ?? [];
        foreach ($preloadedDependencies as $key => $dependency) {
            if ($dependency['machineName'] === $mainLibrary) {
                // Remove the item from the current position
                $item = $preloadedDependencies[$key];
                unset($preloadedDependencies[$key]);

                $preloadedDependencies[] = $item;

                break;
            }
        }

        // Reindex the array to fix any gaps in the keys
        $jsonData['preloadedDependencies'] = array_values($preloadedDependencies);

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
        $dirWithBase = $this->baseDirectory . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($dirWithBase)) {
            return;
        }

        $files = array_diff(scandir($dirWithBase), array('.', '..'));
        foreach ($files as $file) {
            if (is_dir($dirWithBase . DIRECTORY_SEPARATOR . $file)) {
                $this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $file);
            } else {
                unlink($dirWithBase . DIRECTORY_SEPARATOR . $file);
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

        $directories = glob($this->baseDirectory . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

        foreach ($directories as $dir) {
            $dirName = basename($dir);
            $timestamp = explode('-', $dirName)[0];

            if ($currentTimestamp - $timestamp >= $timediff) {
                $this->deleteDirectory($dirName);
            }
        }
    }
}
