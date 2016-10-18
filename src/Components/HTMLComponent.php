<?php
/**
 * Carbon JSON to HTML converter
 *
 * @author  Adam McCann (@AssembledAdam)
 * @license MIT (see LICENSE file)
 */
namespace Candybanana\CarbonJsonToHtml\Components;

use stdClass;
use DOMDocument;
use DOMElement;

/**
 * Converter
 */
class HTMLComponent extends AbstractComponent implements ComponentInterface
{
    /**
     * This components config: sets whether we are parsing for an AMP document. Ignores all but a few tags.
     *
     * [
     *     'amp' => (bool)
     * ]
     *
     * @var array
     */
    protected $config;

    /**
     * Map for autodetection of URLs to determine type
     *
     * @var array
     */
    protected $autoDetectUrls = [
        'youtube' => 'www.youtube.com/embed/',
    ];

    /**
     * Array of detection methods that detect embdes from the given DOMElement
     *
     * [
     *     'service' => {Closure}
     * ]
     *
     * @var array
     */
    protected $detectMethods = [];

    /**
     * Component constructor
     *
     * @param array|null
     */
    public function __construct(array $config = null)
    {
        $this->config = $config;

        $this->detectMethods = [
            'youtube' => function (DOMElement $domElement) {
                return $this->detectYouTube($domElement);
            },
            'twitter' => function (DOMElement $domElement) {
                return $this->detectTwitter($domElement);
            }
        ];

        if (isset($config['detectMethods'])) {
            $this->detectMethods = array_merge($this->detectMethods, $config['detectMethods']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parse(stdClass $json, DOMDocument $dom, DOMElement $parentElement)
    {
        // create a temporary document and load the plain html
        $domElement = $this->loadHtml($json->html);

        // whether tags should be AMP compliant
        if (isset($this->config['amp'])) {

            if ($domElement = $this->convertHtmlToAmp($dom, $domElement)) {
                return $parentElement->appendChild($domElement);
            }

        } else {

            $figure = $dom->createElement('figure');

            // import and attach the created nodes to the paragraph
            foreach ($domElement->childNodes as $node) {

                $node = $dom->importNode($node, true);
                $figure->appendChild($node);
            }

            // set figure classes
            $class = 'embed-container html ';

            // detect any services
            if ($detected = $this->detectType($domElement)) {

                list($service, $data) = $detected;

                $class .= $service;

                // add extra class if youtube
                if ($service == 'youtube') {
                    $class .= ' responsive-video';
                }
            }

            $figure->setAttribute('class', $class);

            return $parentElement->appendChild($figure);
        }
    }

    /**
     * Attempt to understand HTML and return AMP substitute
     *
     * @param  \DOMDocument
     * @param  \DOMElement
     * @return \DOMElement
     */
    protected function convertHtmlToAmp(DOMDocument $dom, DOMElement $domElement)
    {
        // only render known services into AMP page
        if (! $detected = $this->detectType($domElement)) {
            return;
        }

        // attach the detected services to the document
        list($service, $data) = $detected;

        $figure = $dom->createElement('figure');

        // set figure classes
        $class = 'embed-container html ';

        switch ($service) {
            case 'youtube':
                $node = $dom->createElement('amp-youtube');
                $node->setAttribute('data-videoid', $data['id']);
                $node->setAttribute('layout', 'responsive');
                $node->setAttribute('width', 660);
                $node->setAttribute('height', 372);
                break;

            case 'twitter':
                $node = $dom->createElement('amp-twitter');
                $node->setAttribute('data-tweetid', $data['id']);
                $node->setAttribute('layout', 'responsive');
                $node->setAttribute('data-cards', 'hidden');
                $node->setAttribute('width', 660);
                $node->setAttribute('height', 372);
                break;
        }

        $figure->appendChild($node);
        $figure->setAttribute('class', $class);

        return $figure;
    }

    /**
     * Attempts to determine the embed type based on pure HTML
     *
     * @param  \DOMElement
     * @return array
     */
    protected function detectType(DOMElement $domElement)
    {
        $detected = [];

        foreach ($this->detectMethods as $service => $method) {

            if ($data = $method($domElement)) {
                return [$service, $data];
            }
        }
    }

    /**
     * Detect YouTube embed from DOMElement
     *
     * @param  DOMElement $domElement [description]
     * @return array
     */
    protected function detectYouTube(DOMElement $domElement)
    {
        $firstNode = $domElement->childNodes[0];

        if ($firstNode->tagName == 'iframe' && $firstNode->hasAttribute('src')) {

            return [
                'url' => $firstNode->getAttribute('src'),
                'id'  => self::getYouTubeIdFromUrl($firstNode->getAttribute('src')),
            ];
        }
    }

    /**
     * Detect Twitter embed from DOMElement
     *
     * @param  DOMElement $domElement [description]
     * @return array
     */
    protected function detectTwitter(DOMElement $domElement)
    {
        $firstNode = $domElement->childNodes[0];

        if ($firstNode->tagName == 'blockquote' &&
            $firstNode->hasAttribute('class') &&
            $firstNode->getAttribute('class') == 'twitter-tweet') {

            //<blockquote class=\"twitter-tweet\" lang=\"en-gb\">
            //<p lang=\"en\" dir=\"ltr\">Having a party for <a href=\"https://twitter.com/dayusz\">@dayusz</a> today as it&#39;s his last day. Party Rings cost me 50p and I&#39;m not even expensing them! <a href=\"http://t.co/hAC07bDxW9\">pic.twitter.com/hAC07bDxW9</a></p>
            //— James Orry (@VGJames) <a href=\"https://twitter.com/VGJames/status/644790122312024064\">September 18, 2015</a></blockquote><script async src=\"//platform.twitter.com/widgets.js\" charset=\"utf-8\"></script>

            // remove first paragraph
            if (isset($firstNode->childNodes[0]) && $firstNode->childNodes[0]->nodeName == 'p') {
                $firstNode->removeChild($firstNode->childNodes[0]);
            }

            if ($node = $firstNode->getElementsByTagName('a')->item(0)) {
                return [
                    'url' => $node->getAttribute('href'),
                    'id'  => self::getTwitterIdFromUrl($node->getAttribute('href')),
                ];
            }
        }
    }

    /**
     * get youtube video ID from URL
     *
     * @param string $url
     * @return string Youtube video id or FALSE if none found.
     */
    public static function getYouTubeIdFromUrl($url)
    {
        $pattern =
            '%^# Match any youtube URL
            (?:https?://)?  # Optional scheme. Either http or https
            (?:www\.)?      # Optional www subdomain
            (?:             # Group host alternatives
              youtu\.be/    # Either youtu.be,
            | youtube\.com  # or youtube.com
              (?:           # Group path alternatives
                /embed/     # Either /embed/
              | /v/         # or /v/
              | /watch\?v=  # or /watch\?v=
              )             # End path alternatives.
            )               # End host alternatives.
            ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
            $%x';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return false;
    }

    /**
     * get twitter ID from URL
     *
     * @param string $url
     * @return string Youtube video id or FALSE if none found.
     */
    public static function getTwitterIdFromUrl($url)
    {
        $parts = explode('/', $url);
        return end($parts);
    }
}
