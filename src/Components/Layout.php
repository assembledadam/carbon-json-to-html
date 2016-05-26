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
class Layout extends AbstractComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(stdClass $json, DOMDocument $dom, DOMElement $parentElement)
    {
        $layout = $dom->createElement($json->tagName);
        $layout->setAttribute('class', $json->type);

        return $parentElement->appendChild($layout);
    }
}
