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
class HtmlGeneratorFlashcardsMajor1Minor7 extends Generator implements GeneratorInterface
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
        $this->params['cards'] = $this->params['cards'] ?? [];
        if ($this->params['randomCards']) {
            shuffle($this->params['cards']);
        }

        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-standalone h5p-flashcards', $container);

        if ($this->main->target === 'print') {
            $container = str_replace('style="', 'style="background-color: #ffffff;', $container);
            $descriptionStyle = 'color: inherit;';
        }

        $container .=
            '<div ' .
                'class="h5p-description"' .
                'style="' . $descriptionStyle . '"' .
            '>' .
                $this->params['description'] .
            ' </div>';
        $container .=
            '<div ' .
                'class="h5p-inner"' .
                // Older renderers do not support 'grid' - and also not 'gap', so we use margin on cards
                'style="display: flex; flex-direction: row; flex-wrap: wrap; justify-content: space-between; padding-top: 1rem"' .
            '>';

        for ($i = 0; $i < count($this->params['cards']); $i++) {
            $container .= $this->renderCard($this->params['cards'][$i], $i, count($this->params['cards']) - 1);
        }

        // Closing h5p-inner
        $container .= '</div>';

        $container .= $htmlClosing;
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
        $imageSrc = $this->buildFileSource($params['image']['path'] ?? null);

        // Older renderers do not support 'gap', so we use margin on cards
        $properties = [
            'break-inside' => 'avoid',
            'margin' => '0 1rem 1rem 1rem',
            'position' => 'inherit'
        ];

        $style = DOMUtils::buildStyleAttribute($properties);

        $card = '<div class="h5p-card h5p-current" ' . $style . '>';
        $card .= '<div class="h5p-cardholder">';

        $card .= '<div class="h5p-imageholder">';

        if ($imageSrc !== '') {
            $card .= '<img class="h5p-clue" src="' . $imageSrc . '" />';
        } else {
            $card .= '<div class="h5p-clue"></div>';
        }

        $card .= '</div>';

        $card .= '<div class="h5p-foot">';
        $card .= '<div class="h5p-imagetext">' . $params['text'] . '</div>';
        $card .= '<div class="h5p-answer">';

        $card .= '<div class="h5p-input">';

        $card .= '<input type="text" class="h5p-textinput" placeholder="Your answer" />';
        // These are fine in modern browsers, but older render engines mess the height up
        // $card .= '<button type="button" class="h5p-button h5p-check-button">Check</button>';
        // $card .= '<button class="h5p-button h5p-icon-button"></button>';

        $card .= '</div>'; // Closing h5p-input
        $card .= '</div>'; // Closing h5p-answer
        $card .= '</div>'; // Closing h5p-foot
        $card .= '</div>'; // Closing h5p-cardholder
        $card .= '</div>'; // Closing h5p-card

        return $card;
    }
}
