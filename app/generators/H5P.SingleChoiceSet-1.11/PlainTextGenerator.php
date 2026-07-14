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
 * Class for generating HTML for H5P.SingleChoiceSet-1.11.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorSingleChoiceSetMajor1Minor11 extends Generator implements GeneratorInterface
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
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Utils.php';

        $choices = $this->params['choices'];
        for ($i = 0; $i < count($choices); $i++) {
            // Sanitization
            if (!isset($choices[$i]['question'])) {
                $choices[$i]['question'] = '';
            }

            if (!isset($choices[$i]['answers'])) {
                $choices[$i]['answers'] = [];
            }

            $choices[$i]['answers'] = GeneralUtils::shuffle($choices[$i]['answers']);
        }

        for ($i = 0; $i < count($choices); $i++) {
            $container .= $this->renderSet(
                [
                    'index' => $i,
                    'total' => count($choices),
                    'choices' => $choices[$i],
                ]
            );
            $container .= "\n";
        }

        $container = trim($container);
    }

    /**
     * Render a single set.
     *
     * @param array $params Parameters.
     *
     * @return string HTML for the set.
     */
    private function renderSet($params)
    {
        $set = TextUtils::htmlToText($params['choices']['question']);

        if (!str_starts_with($params['choices']['question'], '<p')) {
            $set .= "\n";
        }

        foreach ($params['choices']['answers'] as $alternative) {
            $option = TextUtils::htmlToText($alternative);
            $option = rtrim($option, "\n"); // $alternative could be <p> or plain text
            $set .= '( ) ' . $option . "\n";
        }

        return $set;
    }

    /**
     * Get the solution text.
     *
     * @return string The solution text.
     */
    public function showSolutions()
    {
        return UtilsSingleChoiceSetMajor1Minor11::getSolutionTexts($this->params);
    }
}
