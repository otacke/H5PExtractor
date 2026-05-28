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
 * Class for generating HTML for H5P.InteractiveBook-1.13
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorInteractiveBookMajor1Minor13 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param array $params     Parameters.
     * @param int   $contentId  Content ID.
     * @param array $extras     Extras.
     */
    public function __construct($params, $contentId, $extras)
    {
        parent::__construct($params, $contentId, $extras);
    }

    /**
     * Create the plain text for the given H5P content type.
     *
     * @param string $container Container for H5P content.
     *
     * @return string The plain text for the H5P content type.
     */
    public function attach(&$container)
    {
        $this->attachCover($container);

        foreach ($this->params['chapters'] ?? [] as $index => $chapter) {
            $this->attachChapter($container, $chapter, $index);
        }

        $container = trim($container);
    }

    /**
     * Append the book cover (media + title + description) to the container.
     *
     * @param string $container Container for H5P content.
     */
    private function attachCover(&$container)
    {
        $showCoverPage = ($this->params['showCoverPage'] ?? false) === true;
        $bookCover = $this->params['bookCover'] ?? false;
        if (!$showCoverPage || $bookCover === false) {
            return;
        }

        $libraryContent = $bookCover['coverMedium'];

        $mediaContainer = '';
        $this->main->newRunnable(
            [
                'library' => $libraryContent['library'],
                'params' => $libraryContent['params'],
            ],
            1,
            $mediaContainer,
            false,
            [
                'metadata' => $libraryContent['metadata'] ?? [],
            ]
        );
        if ($mediaContainer !== '') {
            $container .= $mediaContainer . "\n";
        }

        $title = $this->extras['metadata']['title'] ?? null;
        if ($title !== null) {
            $container .= '## ' . $title . "\n";
        }

        if (isset($bookCover['coverDescription'])) {
            $coverDescription = TextUtils::htmlToText($bookCover['coverDescription']);
            $coverDescription = rtrim($coverDescription, "\n");
            $container .= '_' . $coverDescription . '_' . "\n";
        }

        $container .= "\n";
    }

    /**
     * Append a single chapter (heading + chapter content) to the container.
     *
     * @param string $container Container for H5P content.
     * @param array  $chapter   Chapter parameters.
     * @param int    $index     Chapter index, used as fallback title.
     */
    private function attachChapter(&$container, $chapter, $index)
    {
        $chapterTitle = $chapter['metadata']['title'] ?? '';
        if ($chapterTitle === '') {
            $chapterTitle = $index;
        }

        if (!str_ends_with($container, "\n")) {
            $container .= "\n";
        }
        if (!str_ends_with($container, "\n\n")) {
            $container .= "\n";
        }
        $container .= '### ' . $chapterTitle . "\n";

        $innerContainer = '';
        $this->main->newRunnable(
            [
                'library' => $chapter['library'],
                'params' => $chapter['params'],
            ],
            1,
            $innerContainer,
            false,
            [
                'metadata' => $chapter['metadata'] ?? [],
            ]
        );

        $container .= $innerContainer;
    }
}
