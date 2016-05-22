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
 * ComponentInterface
 */
interface ComponentInterface
{
    /**
     * Parse a component into HTML
     *
     * @param  \stdClass
     * @param  mixed
     * @return string
     */
    public function parse(stdClass $json, DOMDocument $dom, DOMElement $element);
}
