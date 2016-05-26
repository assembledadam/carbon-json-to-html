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
        $container = $dom->createElement('div');

        // create a temporary document and load the plain html
        $tmpDoc = new DOMDocument;
        $tmpDoc->loadHTML('<html><body>' . $json->html . '</body></html>');

        // import and attach the created nodes to the paragraph
        foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {

            $node = $dom->importNode($node, true);
            $container->appendChild($node);
        }

        return $parentElement->appendChild($container);

        //
        // $temp = $dom->createElement('div')

        // // apply formatting to text if applicable
        // if (! empty($json->formats)) {

        //     $paragraph = (new Formats($json, $dom, $paragraph, $this->customAttrs))->render();

        // } else {

        //     $paragraphText = $dom->createTextNode($json->text);
        //     $paragraph->appendChild($paragraphText);
        // }

        // return $parentElement->appendChild($figure);
    }
}
