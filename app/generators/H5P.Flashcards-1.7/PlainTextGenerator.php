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
 * Class for generating HTML for H5P.Flashcards-1.7.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorFlashcardsMajor1Minor7 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param array $params    Parameters.
     * @param int   $contentId Content ID.
     * @param array $extras    Extras.
     */
    public function __construct($params, $contentId, $extras)
    {
        parent::__construct($params, $contentId, $extras);
    }

    /**
     * Create the HTML for the given H5P content type.
     *
     * @param string $container Container for H5P content.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        $this->params['cards'] = $this->params['cards'] ?? [];
        if ($this->params['randomCards']) {
            shuffle($this->params['cards']);
        }

        if (isset($this->params['description'])) {
            $container .= TextUtils::htmlToText($this->params['description']) . "\n\n";
        }

        for ($i = 0; $i < count($this->params['cards']); $i++) {
            $container .= $this->renderCard($this->params['cards'][$i], $i, count($this->params['cards']) - 1);
            $container .= "\n\n";
        }

        $container = trim($container);
    }

    /**
     * Render a single card.
     *
     * @param array $params Card parameters.
     *
     * @return string HTML for the card.
     */
    private function renderCard($params)
    {
        error_log(print_r($params, true));

        $card = '';
        if (isset(($params['image']['path']))) {
            $card .= '![' . ($params['imageAltText'] ?? '') . ']' . "\n";
        }

        if (isset($params['text'])) {
            $card .= TextUtils::htmlToText($params['text']) . "\n";
        }

        $card .= "\n" . '________________________________________';

        return $card;
    }
}
