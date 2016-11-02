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
use HTMLPurifier;
use HTMLPurifier_Config;

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

    // /**
    //  * Array of whitelisted custom HTML attributes for HTML Purifier
    //  *
    //  * @var array
    //  */
    // protected $whitelistedAttrs = [
    //     'data-xhr-url'      => 'div',
    //     'data-total-images' => 'div',
    //     'data-size'         => 'a',
    //     'data-position'     => 'a',
    //     'data-permalink'    => 'a',
    //     'srcset'            => 'img',
    //     'sizes'             => 'img',
    // ];

    // /**
    //  * Array of whitelisted custom HTML elements for HTML Purifier
    //  *
    //  * @var array
    //  */
    // // $type, $contents, $attr_collections, $attributes
    // protected $whitelistedElems = [
    //     'picture' => [
    //         'type'            => 'Inline',
    //         'contents'        => 'Inline',
    //         'attrCollections' => 'Common',
    //         'attrs'           => null,
    //     ],
    //     'source' => [
    //         'type'            => 'Inline',
    //         'contents'        => 'Empty',
    //         'attrCollections' => 'Common',
    //         'attrs'           => [
    //             'srcset' => 'Text',
    //             'sizes'  => 'Text',
    //             'type'   => 'Text'
    //         ]
    //     ],
    //     'svg' => [
    //         'type'            => 'Inline',
    //         'contents'        => 'Inline',
    //         'attrCollections' => 'Common',
    //         'attrs'           => [
    //             'role'    => 'Text',
    //             'class'   => 'Text',
    //             'viewBox' => 'Text'
    //         ]
    //     ],
    //     'use' => [
    //         'type'            => 'Inline',
    //         'contents'        => 'Empty',
    //         'attrCollections' => 'Common',
    //         'attrs'           => [
    //             'xlink:href' => 'Text',
    //         ]
    //     ],
    // ];

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
    protected function loadHtml($html, $purify = true)
    {
        // create a temporary document and load the plain html
        $tmpDoc = new DOMDocument;
        libxml_use_internal_errors(true); // for html5 tags

        if ($purify) {

            // purify HTML to convert HTML chars in text nodes etc.
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Trusted', true);

            $this->whitelistAttrs($config);
            $this->whitelistElems($config);

            $originalHtml = $html;

            $html = (new HTMLPurifier($config))->purify($html);
        }

        // @todo: here to safeguard over purification - remove once we've verified it works well.
        if (empty($html)) {
            $html = $originalHtml;
        }

        $tmpDoc->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>');
        $tmpDoc->encoding = 'UTF-8';
        libxml_clear_errors();

        return $tmpDoc->getElementsByTagName('body')->item(0);
    }

    /**
     * Whitelisted attributes
     *
     * @param \HTMLPurifier_Config
     */
    // protected function whitelistAttrs($config)
    // {
    //     // allow data attributes
    //     $def = $config->getHTMLDefinition(true);

    //     foreach ($this->whitelistedAttrs as $attr => $tag) {
    //         $def->addAttribute($tag, $attr, 'Text');
    //     }
    // }

    /**
     * Whitelisted elements
     *
     * @param \HTMLPurifier_Config
     */
    // protected function whitelistElems($config)
    // {
    //     // allow data attributes
    //     $def = $config->getHTMLDefinition(true);

    //     foreach ($this->whitelistedElems as $element => $props) {

    //         $def->addElement(
    //             $element,
    //             $props['type'],
    //             $props['contents'],
    //             $props['attrCollections'],
    //             $props['attrs']
    //         );
    //     }
    // }
}
