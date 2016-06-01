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
     * The classname that gets applied to every layout
     *
     * @var array
     */
    protected $className = 'carbon-layout';

    /**
     * Component constructor
     *
     * @param array|null
     */
    public function __construct(array $config = null)
    {
        $this->className = ! empty($config['className']) ? $config['className'] : $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(stdClass $json, DOMDocument $dom, DOMElement $parentElement)
    {
        $layout = $dom->createElement(strtolower($json->tagName));
        $layout->setAttribute('class', $this->className . ' ' . $json->type);

        return $parentElement->appendChild($layout);
    }
}
