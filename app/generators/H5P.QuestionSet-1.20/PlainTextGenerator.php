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
 * Class for generating HTML for H5P.QuestionSet-1.20.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorQuestionSetMajor1Minor20 extends Generator implements GeneratorInterface
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
        if ($this->params['introPage']['showIntroPage']) {
            $container .= $this->buildIntro($container);
            $container .= "\n\n";
        }

        // This diverges from the original view of H5P.Question, because we
        // want to display all questions at once.
        foreach($this->params['questions'] as $question) {
            $container .= $this->buildSlide($originalContainer, $question, $index);
            if ($index < count($this->params['questions']) - 1) {
                $container .= "\n\n" . '---' . "\n\n";
            }
            $index++;
        }

        $container = trim($container);
    }

    /**
     * Build the intro page.
     *
     * @param string $intro The intro page to build.
     *
     * @return string The intro page.
     */
    private function buildIntro($intro)
    {
        if (isset($this->params['introPage']['title'])) {
            $intro .= '**' . $this->params['introPage']['title'] . '**' . "\n";
        }
        if (isset($this->params['introPage']['introduction'])) {
            $intro .=
                TextUtils::htmlToText($this->params['introPage']['introduction']);
        }

        return trim($intro);
    }

        /**
     * Build a slide with a question.
     *
     * @param string $slide    The slide to build.
     * @param array  $question The question to add.
     * @param int    $index    The index of the question.
     *
     * @return string The slide with the question.
     */
    private function buildSlide($slide, $question, $index) {
        $slide .= $this->buildFooter($index);

        $slide .= "\n\n";

        $questionContainer = '';
        $this->main->newRunnable(
            [
                'library' => $question['library'],
                'params' => $question['params'],
            ],
            1,
            $questionContainer,
            false,
            [
                'metadata' => $question['metadata'],
            ]
        );

        $slide .= $questionContainer;

        return $slide;
    }

    /**
     * Build the footer for a slide.
     *
     * @param int $index The index of the slide.
     *
     * @return string The footer.
     */
    private function buildFooter($index) {
        $progressText = $this->params['texts']['textualProgress'];
        $progressText = str_replace(
            '@current',
            $index + 1,
            $progressText
        );
        $progressText = str_replace(
            '@total',
            count($this->params['questions']),
            $progressText
        );
        return '*' . $progressText . '*';
    }
}
