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
 * Class for generating HTML for H5P.Agamotto-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorAgamottoMajor1Minor5 extends Generator implements GeneratorInterface
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
        $output = '';
        if (isset($this->params['items'])) {
            foreach ($this->params['items'] as $item) {
                if (!isset($item['image'])) {
                    continue;
                }

                $imageParams = $item['image'];
                $imageContainer = '';
                        $this->main->newRunnable(
                            [
                                'library' => $imageParams['library'],
                                'params' => $imageParams['params'],
                            ],
                            1,
                            $imageContainer,
                            false,
                            [
                                'metadata' => $imageParams['metadata'],
                            ]
                        );

                $output .= $imageContainer;

                if (isset($item['description'])) {
                    $output .= "\n";
                    $output .= TextUtils::htmlToText($item['description']);
                }

                $output .= "\n";
            }
        }

        $container = trim($output);
    }
}
