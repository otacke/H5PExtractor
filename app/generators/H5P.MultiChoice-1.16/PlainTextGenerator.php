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
 * Class for generating HTML for H5P.MultiChoice-1.16.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorMultiChoiceMajor1Minor16 extends Generator implements GeneratorInterface
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
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Utils.php';
        $answers = $this->params['answers'] ?? [];
        if ($this->params['behaviour']['randomAnswers']) {
            $answers = GeneralUtils::shuffle($answers);
        }

        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        $container .= TextUtils::htmlToText(($this->params['question'] ?? ''));

        $numCorrect = count(
            array_filter(
                $answers,
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

        $listItem = "( )";
        if ($mode === 'h5p-check') {
            $listItem = '[ ]';
        }

        $answerCount = count($answers);
        for ($answerIndex = 0; $answerIndex < $answerCount; $answerIndex++) {
            $answerData = $answers[$answerIndex];
            $container .= $listItem . ' ' .
                TextUtils::htmlToText(($answerData['text'] ?? "\n")) . "\n";
        }

        $container = trim($container);
    }

    /**
     * Get the text of correct answers.
     *
     * @return string The solution text.
     */
    public function showSolutions()
    {
        return UtilsMultiChoiceMajor1Minor16::getSolutionTexts($this->params['answers']);
    }
}
