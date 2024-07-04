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
class HtmlGeneratorQuestionSetMajor1Minor20 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param HTMLGeneratorMain $main The main HTML generator.
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

        $styleProps = ['overflow: hidden'];

        if (($this->params['backgroundImage']['path'] ?? '') !== '') {
            $imagePath = $this->main->h5pFileHandler->getBaseDirectory() . '/' .
            $this->main->h5pFileHandler->getFilesDirectory() . '/' .
                'content' . '/' . $this->params['backgroundImage']['path'];

            $styleProps[] = 'background-image: url(' .
                FileUtils::fileToBase64($imagePath) .
            ')';
        }

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', '', $container);
        $container = str_replace(
            '">',
            '" style="' . implode('; ', $styleProps) . '">',
            $container
        );

        $originalContainer = $container;

        $container = '';

        if ($this->params['introPage']['showIntroPage']) {
            $container .= $this->buildIntro($originalContainer);
            $container .= '<div style="height: 1rem;"></div>';
        }

        $index = 0;
        $poolSize = $this->params['poolSize'] ?? 0;
        $needsShuffling = ($this->params['randomQuestions'] ?? false) ||
            $poolSize > 0;

        if ($needsShuffling) {
            shuffle($this->params['questions']);
        }

        if ($poolSize > 0) {
            $this->params['questions'] = array_slice(
                $this->params['questions'],
                0,
                $this->params['poolSize']
            );
        }

        // This diverges from the original view of H5P.Question, because we
        // want to display all questions at once.
        foreach ($this->params['questions'] as $question) {
            $container .= $this->buildSlide($originalContainer, $question, $index);
            if ($index < count($this->params['questions']) - 1) {
                $container .= '<div style="height: 1rem;"></div>';
            }
            $index++;
        }

        $container .= $htmlClosing;
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
        $intro = preg_replace('/\s*style="[^"]*"/i', '', $intro);
        $introClosing = TextUtils::getClosingTag($intro);

        $styleProps = [];
        if (isset($this->params['introPage']['backgroundImage']['path'])) {
            $imagePath = $this->main->h5pFileHandler->getBaseDirectory() . '/' .
            $this->main->h5pFileHandler->getFilesDirectory() . '/' .
                'content' . '/' .
                $this->params['introPage']['backgroundImage']['path'];

            $styleProps[] = 'background: url(' .
                FileUtils::fileToBase64($imagePath) .
            ') 50% 50% / auto 100% no-repeat rgb(255, 255, 255);';

            list($width, $height) = getimagesize($imagePath);
            $renderHeight = $this->main->renderWidth / $width * $height;

            $styleProps[] = 'min-height: ' . $renderHeight . 'px';
        }

        $intro .= '<div class="intro-page"' .
            ' style="' . implode('; ', $styleProps) . '">';
            '>';

        if (isset($this->params['introPage']['title'])) {
            $intro .= '<div class="title"><h1>' .
                $this->params['introPage']['title'] .
                '</h1></div>';
        }
        if (isset($this->params['introPage']['introduction'])) {
            $intro .= '<div class="introduction">' .
                $this->params['introPage']['introduction'] .
                '</div>';
        }
        $intro .= '</div>';

        $intro .= $introClosing;

        return $intro;
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
    private function buildSlide($slide, $question, $index)
    {
        $slideClosing = TextUtils::getClosingTag($slide);

        $questionContainer = '<div class="h5p-question-container h5pClassName" style="">';
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
        $slide .= $this->buildFooter($index);
        $slide .= $slideClosing;

        return $slide;
    }

    /**
     * Build the footer for a slide.
     *
     * @param int $index The index of the slide.
     *
     * @return string The footer.
     */
    private function buildFooter($index)
    {
        $footer = '<div class ="qs-footer">';
        $footer .= '<div class="qs-progress">';

        if ($this->params['progressType'] === 'dots') {
            $footer .= '<ul class="dots-container>';
            for ($i = 0; $i < count($this->params['questions']); $i++) {
                $footer .= '<li class="progress-item">';
                $footer .= '<a href=""' .
                    ' class="progress-dot unanswered' .
                    ($i === $index ? ' current' : '') .
                    '"></a>';
                $footer .= '</li>';
            }
            $footer .= '</ul>';
        } elseif ($this->params['progressType'] === 'textual') {
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
            $footer .=
                '<span class="progress-text">' . $progressText . '</span>';
        }

        $footer .= '</div>'; // qs-progress
        $footer .= '</div>'; // qs-footer

        return $footer;
    }
}
