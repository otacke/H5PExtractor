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
        for ($i = 0; $i < count($this->params['choices']); $i++) {
            // Sanitization
            if (!isset($this->params['choices'][$i]['question'])) {
                $this->params['choices'][$i]['question'] = '';
            }

            if (!isset($this->params['choices'][$i]['answers'])) {
                $this->params['choices'][$i]['answers'] = [];
            }

            shuffle($this->params['choices'][$i]['answers']);
        }

        for ($i = 0; $i < count($this->params['choices']); $i++) {
            $container .= $this->renderSet(
                [
                    'index' => $i,
                    'total' => count($this->params['choices']),
                    'choices' => $this->params['choices'][$i],
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
        foreach ($params['choices']['answers'] as $alternative) {
            $set .= '-' . TextUtils::htmlToText($alternative);
        }

        return $set;
    }
}
