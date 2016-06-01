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
class Paragraph extends AbstractComponent implements ComponentInterface
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
        $paragraph = $dom->createElement(strtolower($json->paragraphType));

        // apply formatting to text if applicable
        if (! empty($json->formats)) {

            $paragraph = (new Formats($json, $dom, $paragraph, $this->customAttrs))->render();

        } else {

            $paragraphText = $dom->createTextNode($json->text);
            $paragraph->appendChild($paragraphText);
        }

        return $parentElement->appendChild($paragraph);
    }
}
