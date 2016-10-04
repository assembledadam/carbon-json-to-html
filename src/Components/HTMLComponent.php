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
     * This components config: an array of custom attributes to apply to formatting tags within the text
     *
     * @var array
     */
    protected $customAttrs;

    /**
     * Map for autodetection of URLs to determine type
     *
     * @var array
     */
    protected $autoDetectUrls = [
        'youtube' => 'www.youtube.com/embed/',
    ];

    /**
     * Component constructor
     *
     * @param array|null
     */
    public function __construct(array $config = null)
    {
        $this->customAttrs = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(stdClass $json, DOMDocument $dom, DOMElement $parentElement)
    {
        $figure = $dom->createElement('figure');

        // create a temporary document and load the plain html
        $tmpDoc = new DOMDocument;
        libxml_use_internal_errors(true); // for html5 tags
        $tmpDoc->loadHTML('<?xml encoding="UTF-8"><html><body>' . $json->html . '</body></html>');
        $tmpDoc->encoding = 'UTF-8';
        libxml_clear_errors();

        // import and attach the created nodes to the paragraph
        foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {

            $node = $dom->importNode($node, true);
            $figure->appendChild($node);
        }

        // try to autodetect type
        $service = null;

        foreach ($this->autoDetectUrls as $service => $url) {

            if (strpos($json->html, $url) !== false) {
                break;
            }
        }

        // set figure classes
        $class = 'embed-container html ';

        if ($service) {
            $class .= $service;
        }

        // if type is video and the html has an iframe, apply responsive fix class
        if ($service == 'youtube' && strpos($json->html, '<iframe') !== false) {
            $class .= ' responsive-video';
        }

        $figure->setAttribute('class', $class);

        return $parentElement->appendChild($figure);
    }
}
