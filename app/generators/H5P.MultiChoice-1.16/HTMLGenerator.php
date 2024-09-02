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
 * Class for generating HTML for H5P.MultipleChoice-1.16.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorMultiChoiceMajor1Minor16 extends Generator implements GeneratorInterface
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
        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-question h5p-multichoice', $container);

        if ($this->params['behaviour']['randomAnswers']) {
            shuffle($this->params['answers']);
        }

        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        $container .= '<div class="h5p-question-introduction">';
        $container .= '<div>' . ($this->params['question'] ?? ''). '</div>';
        $container .= '</div>'; // Closing h5p-question-introduction

        $numCorrect = count(
            array_filter(
                $this->params['answers'],
                function ($answer) {
                    return $answer['correct'];
                }
            )
        );

        $mode = ($numCorrect === 1) ? 'h5p-radio' : 'h5p-check';
        if ($this->params['behaviour']['type'] === 'single') {
            $mode = 'h5p-radio';
        } elseif ($this->params['behaviour']['type'] === 'multi') {
            $mode = 'h5p-check';
        }

        $container .= '<div class="h5p-question-content ' . $mode . '">';

        $role = $mode === 'h5p-radio' ? 'radiogroup' : 'group';
        $container .= '<ul class="h5p-answers" role="' . $role . '">';

        $role = $mode === 'h5p-radio' ? 'radio' : 'checkbox';
        $answerCount = count($this->params['answers']);
        for ($answerIndex = 0; $answerIndex < $answerCount; $answerIndex++) {
            $container .= '<li class="h5p-answer" role="' . $role . '">';
            $container .= '<div class="h5p-alternative-container">';

            /*
             * Browsers handle divs inside spans fine, but other renderers may not
             * Changing this span to a div doesn't seem to have any negative effects
             */
            $container .= '<div class="h5p-alternative-inner">';

            $answerData = $this->params['answers'][$answerIndex];
            $container .= $answerData['text'] ?? '';

            $container .= '</span>';
            // TODO: Tips
            $container .= '</div>';
            $container .= '</li>';
        }

        $container .= '</ul>';
        $container .= '</div>'; // h5p-question-content

        $container .= $htmlClosing; // container
    }
}
