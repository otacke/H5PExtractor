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
 * Class for generating HTML for H5P.Dialogcards-1.9.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorDialogCardsMajor1Minor9 extends Generator implements GeneratorInterface
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
     * Create the HTML for the given H5P content type.
     *
     * @param string $container Container for H5P content.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        $container .= '## ' . TextUtils::htmlToText($this->params['title']);
        $container .= TextUtils::htmlToText($this->params['description']) . "\n";

        if ($this->params['behaviour']['randomCards'] ?? false) {
            shuffle($this->params['dialogs']);
        }

        $cardsCount = count($this->params['dialogs']);
        for ($index = 0; $index < $cardsCount; $index++) {
            $progressText = str_replace('@card', $index + 1, $this->params['progressText']);
            $progressText = str_replace('@total', $cardsCount, $progressText);

            $dialog = $this->params['dialogs'][$index] ?? [];

            $container .= '### ' . $progressText . "\n";

            $container .= self::buildCardholder([
                'imageAltText' => $dialog['imageAltText'] ?? '',
                'text' => $dialog['text'] ?? '',
                'hint' => $dialog['tips']['front'] ?? '',
                'side' => 'front'
            ]);

            $container .= self::buildCardholder([
                'imageAltText' => $dialog['imageAltText'] ?? '',
                'text' => $dialog['answer'] ?? '',
                'hint' => $dialog['tips']['back'] ?? '',
                'side' => 'back'
            ]);

            $container .= "\n";
        }

        $container = trim($container);
    }

    /**
     * Render a cardholder.
     *
     * @param array $params Parameters.
     *
     * @return string The HTML for the cardholder.
     */
    private function buildCardholder($params)
    {
        $card = '- ' . '_' . $params['side'] . ':_';

        if ($params['imageAltText'] !== '') {
            $card .= ' ![' . TextUtils::htmlToText($params['imageAltText']) . ']';
        }
        if (isset($params['text']) && $params['text'] !== '') {
            $card .= ' ' . TextUtils::htmlToText($params['text']);
        }
        if ($params['hint'] !== '') {
            $card .= ' (' . TextUtils::htmlToText($params['hint']) . ')';
        }

        return $card;
    }
}
