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
     * Trims empty paragraphs from the end of the output
     *
     * @var boolean
     */
    protected $trim;

    /**
     * Component constructor
     *
     * @param array|null
     */
    public function __construct(array $config = null, $trim = true)
    {
        $this->customAttrs = $config;
        $this->trim = $trim;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(stdClass $json, DOMDocument $dom, DOMElement $parentElement)
    {
        if ($this->trim && empty($json->text)) {
            return $parentElement;
        }

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
