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
abstract class AbstractComponent
{
    /**
     * [$ame description]
     *
     * @var null
     */
    protected $name = null;

    /**
     * Returns name of this component
     *
     * @return string
     */
    public function getName()
    {
        $class = get_class($this);

        return $this->name ?: substr($class, strrpos($class, '\\') + 1);
    }

    /**
     * Attempts to load given HTML into DOMDocument for parsing
     *
     * @param  string
     * @return \DOMElement
     */
    protected function loadHtml($html)
    {
        // create a temporary document and load the plain html
        $tmpDoc = new DOMDocument;
        libxml_use_internal_errors(true); // for html5 tags
        $tmpDoc->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>');
        $tmpDoc->encoding = 'UTF-8';
        libxml_clear_errors();

        return $tmpDoc->getElementsByTagName('body')->item(0);
    }
}
