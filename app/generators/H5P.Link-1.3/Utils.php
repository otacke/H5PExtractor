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
 * Utility class for H5P.Link-1.3.
 *
 * @category Utility
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsLinkMajor1Minor3
{
    /**
     * Sanitize parameters.
     *
     * @param array $params Parameters.
     */
    public static function sanitizeParams(&$params = [])
    {
        $params['linkWidget'] = $params['linkWidget'] ?? [];

        $params['title'] = $params['title'] ?? 'New link'; // TODO: i18n
        $params['linkWidget']['protocol'] = $params['linkWidget']['protocol'] ?? '';
        $params['linkWidget']['url'] = $params['linkWidget']['url'] ?? '';
    }

    /**
     * Get URL from link widget.
     *
     * @param array $linkWidget Link widget.
     *
     * @return string URL.
     */
    public static function getUrl($linkWidget = [])
    {
        $url = '';
        if ($linkWidget['protocol'] !== 'other') {
            $url .= $linkWidget['protocol'];
        }
        $url .= $linkWidget['url'] ?? '';

        return $url;
    }

    /**
     * Remove illegal URL protocols from URI.
     *
     * @param string $uri URI.
     *
     * @return string Sanitized URI.
     */
    public static function sanitizeUrlProtocol($uri)
    {
        $allowedProtocols = [
          'http',
          'https',
          'ftp',
          'irc',
          'mailto',
          'news',
          'nntp',
          'rtsp',
          'sftp',
          'ssh',
          'tel',
          'telnet',
          'webcal'
        ];

        $pattern = '~\b(?:' .
          implode('|', $allowedProtocols) .
          '):(?![/|?#])[^\\s]*~i';

        return preg_replace($pattern, '', $uri);
    }
}
