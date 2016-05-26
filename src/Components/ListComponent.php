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
class ListComponent extends AbstractComponent implements ComponentInterface
{
    /**
     * This components config: an array of custom attributes to apply to formatting tags within the text
     *
     * @var array
     */
    protected $customAttrs;

    /**
     * The name of this component
     *
     * @var string
     */
    protected $name = 'List';

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
        dd($json);
        dd($parentElement->nodeValue);

        $list = $dom->createElement($json->tagName);

        foreach ($json->components as $item) {

            $listItem = $dom->createElement($item->paragraphType);

            // apply formatting to text if applicable
            if (! empty($item->formats)) {

                $listItem = (new Formats($item, $dom, $listItem, $this->customAttrs))->render();

            } else {

                $listItemText = $dom->createTextNode($item->text);
                $listItem->appendChild($listItemText);
            }

            $list->appendChild($listItem);
        }

        return $parentElement->appendChild($list);
    }
}
