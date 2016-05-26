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
class Figure extends AbstractComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(stdClass $json, DOMDocument $dom, DOMElement $parentElement)
    {
        $figure = $dom->createElement('figure');

        // <div class="image-container">
        $imageContainer = $dom->createElement('div');
        $imageContainer->setAttribute('class', 'image-container');
        $figure->appendChild($imageContainer);

        // <img>
        $image = $dom->createElement('img');
        $image->setAttribute('src', $json->src);
        $imageContainer->appendChild($image);

        // <figcaption>
        $figCaption = $dom->createElement('figcaption');
        $figure->appendChild($figCaption);

        $figCaptionText = $dom->createTextNode($json->caption);
        $figCaption->appendChild($figCaptionText);

        return $parentElement->appendChild($figure);
    }
}
