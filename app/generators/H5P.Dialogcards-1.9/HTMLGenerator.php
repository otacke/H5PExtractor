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
class HtmlGeneratorDialogcardsMajor1Minor9 extends Generator implements GeneratorInterface
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
     * @param array             $params Parameters.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        $htmlClosing = TextUtils::getClosingTag($container);
        // Ignoring the scaleTextNotCard parameter, will always scale
        $container = str_replace('h5pClassName', 'h5p-dialogcards h5p-text-scaling', $container);

        // Unclassified div
        $container .= '<div>';

        // Title
        $container .= '<div class="h5p-dialogcards-title">';
        $container .= '<div class="h5p-dialogcards-title-inner">';
        $container .= $this->params['title'];
        $container .= '</div>';
        $container .= '</div>';

        // Description
        $container .= '<div class="h5p-dialogcards-description">' .
            $this->params['description'] .
            '</div>';

        if ($this->params['behaviour']['randomCards'] ?? false) {
            shuffle($this->params['dialogs']);
        }

        $cardsCount = count($this->params['dialogs']);
        for ($index = 0; $index < $cardsCount; $index++) {
            $container .= self::buildCardWrapSet(
                $this->params['dialogs'][$index]
            );
            $container .= self::buildFooter(
                $index + 1,
                count($this->params['dialogs'])
            );
            if ($index + 1 !== $cardsCount) {
                $container .= '<span>&nbsp;</span>';
            }
        }

        // Closing unclassified div
        $container .= '</div>';

        $container .= $htmlClosing;
    }

    private function buildCardWrapSet($dialog)
    {
        // Cardwrap-Set
        $setHeight = isset($dialog['image']['path']) ?
            29 * $this->getBaseFontSize() .'px' :
            16 * $this->getBaseFontSize() .'px';
        $set  = '<div class="h5p-dialogcards-cardwrap-set" style="break-inside: avoid; height: ' . $setHeight . ';">';
        $set .=
            '<div ' .
                'class="h5p-dialogcards-cardwrap h5p-dialogcards-mode-normal h5p-dialogcards-current" ' .
                'style="height: inherit"' .
                '>';

        // Custom wrapper to display to cards side by side
        // $set .= '<div style="display: grid; grid-template-columns: 1fr 1fr; grid-gap: 1rem;">';
        $set .= '<div style="display: flex; flex-direction: row;">';

        $imagePath = isset($dialog['image']) ? $dialog['image']['path'] : '';

        $set .= self::buildCardholder([
            'image' => $this->buildFileSource($imagePath),
            'audio' => count($dialog['audio'] ?? []) > 0,
            'text' => $dialog['text'] ?? '',
            'hint' => $dialog['tips']['front'] ?? '',
            'side' => 'front'
        ]);

        $set .= self::buildCardholder([
            'image' => $this->buildFileSource($imagePath),
            'audio' => count($dialog['audio'] ?? []) > 0,
            'text' => $dialog['answer'] ?? '',
            'hint' => $dialog['tips']['back'] ?? '',
            'side' => 'back'
        ]);

        // Closing custom wrapper
        $set .= '</div>';

        // Closing cardwrap
        $set .= '</div>';

        // Closing cardwrap-set
        $set .= '</div>';

        return $set;
    }

    /**
     * Build the footer.
     *
     * @param int $current Current card.
     * @param int $total   Total cards.
     *
     * @return string The HTML for the footer.
     */
    private function buildFooter($current, $total)
    {
        $footer =  '<nav class="h5p-dialogcards-footer">';
        $footer .= '<div class="h5p-dialogcards-progress">';

        $progressText = str_replace('@card', $current, $this->params['progressText']);
        $progressText = str_replace('@total', $total, $progressText);

        $footer .= $progressText;

        $footer .= '</div>';
        $footer .= '</nav>';

        return $footer;
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
        $styleProps = [
            'width: 100%',
        ];
        if ($params['side'] === 'front') {
            $styleProps[] = 'margin-right: 1rem';
        }

        $cardholder  = '<div class="h5p-dialogcards-cardholder" style="' . implode(';', $styleProps) . '">';
        $cardholder .= '<div class="h5p-dialogcards-card-content">';

        // Image
        $imageHeight = 15 * $this->getBaseFontSize();
        $wrapperHeight = ($params['image'] !== '') ? $imageHeight . 'px' : 'auto';
        $cardholder .= '<div class="h5p-dialogcards-image-wrapper" style="height: ' . $wrapperHeight . ';">';
        if ($params['image'] !== '') {
            $cardholder .=
                '<img ' .
                    'style="height: ' . $imageHeight . 'px" ' .
                    'class="h5p-dialogcards-image" ' .
                    'src="' . $params['image'] . '"' .
                '/>';
        }
        $cardholder .= '</div>';

        // Text
        $cardholder .= '<div class="h5p-dialogcards-card-text-wrapper">';
        $textInnerHeightStyle = ($params['image'] !== '') ? '' : 'height: ' . 12 * $this->getBaseFontSize() . 'px;';
        $cardholder .= '<div class="h5p-dialogcards-card-text-inner" style="' . $textInnerHeightStyle .'">';

        $cardholder .= '<div class="h5p-dialogcards-card-text-inner-content">';

        // Audio
        $hideAudioWrapperClass = $params['audio'] ? ''  : ' hide';
        $cardholder .= '<div class="h5p-dialogcards-audio-wrapper h5p-audio-wrapper' . $hideAudioWrapperClass . '">';

        $hideAudioInnerClass = $params['side'] === 'back' ? ' hide' : '';

        $cardholder .= '<div class="h5p-audio-inner' . $hideAudioInnerClass . '">';
        $cardholder .= '<button class="h5p-audio-minimal-button h5p-audio-minimal-play"></button>';
        $cardholder .= '</div>';

        $cardholder .= '</div>';
        $cardholder .= '<div class="h5p-dialogcards-card-text">';

        // 16 padding, 16 gap, 64 inner card padding
        $textAreaWidth = ($this->getRenderWidth() - 32) / 2 - 64;
        $textAreaHeight = 72; // Fixed text area height in pixels from CSS

        if ($params['hint'] !== '') {
            $params['text'] .=
                '<p style="margin-top: 0.5rem; text-align: center">' .
                    '(' . "\u{2139}\u{fe0f}: " . $params['hint'] .')' .
                '</p>';
        }

        $fontSize = DOMUtils::estimateFittingFontSize(
            $params['text'],
            $textAreaWidth,
            $textAreaHeight,
            4, // Minimum font size
            16, // Maximum font size
            0.5, // Average character width in ems
            1.5 // Line height factor
        );

        $cardholder .= '<div class="h5p-dialogcards-card-text-area" style="font-size: ' . $fontSize . 'px">';
        $cardholder .= $params['text'] ?? '';
        $cardholder .= '</div>';
        $cardholder .= '</div>';
        $cardholder .= '</div>';

        // Closing h5p-dialogcards-text-inner"
        $cardholder .= '</div>';
        // Closing h5p-dialogcards-text-wrapper
        $cardholder .= '</div>';

        // Closing h5p-dialogcards-card-content
        $cardholder .= '</div>';
        // Closing h5p-dialogcards-cardholder
        $cardholder .= '</div>';

        return $cardholder;
    }
}
