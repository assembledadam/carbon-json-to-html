<?php
/**
 * Carbon JSON to HTML converter
 *
 * @author  Adam McCann (@AssembledAdam)
 * @license MIT (see LICENSE file)
 */
namespace Candybanana\CarbonJsonToHtml;

use DOMDocument;
use DOMElement;

/**
 * Converter
 */
class Converter
{
    /**
     * A representation of the HTML document we are building
     *
     * @var \DomDocument
     */
    protected $dom;

    /**
     * An object representing the JSON to convert
     *
     * @var string
     */
    protected $json;

    /**
     * Array of default components and their configurations, representing Carbon components
     *
     * @var array
     */
    protected $defaultComponents = [
        'Section',
        'Layout',
        'Paragraph',
        'Figure',
    ];

    /**
     * Array of instantiated components
     *
     * @var array
     */
    protected $components = [];

    /**
     * Constructor
     *
     * @return string
     */
    public function __construct()
    {
        $this->dom = new DOMDocument('1.0');

        // add default components
        foreach ($this->defaultComponents as $componentName => $config) {

            // do we have a config?
            if (! is_array($config)) {
                $componentName = $config;
                $config = [];
            }

            $component = '\\Candybanana\\CarbonJsonToHtml\\Components\\' . ucfirst($componentName);

            $this->addComponent($componentName, new $component($config));
        }
    }

    /**
     * Adds a component parser
     *
     * @param  string
     * @param  \Candybanana\CarbonJsonToHtml\Components\ComponentInterface
     * @return \Candybanana\CarbonJsonToHtml\Converter
     */
    public function addComponent($componentName, Components\ComponentInterface $component)
    {
        $this->components[$componentName] = $component;

        return $this;
    }

    /**
     * Perform the conversion
     *
     * @return string
     */
    public function convert($json)
    {
        if (($this->json = json_decode($json)) === null) {

            throw new Exception\NotTraversableException(
                'The JSON provided is not valid'
            );
        }

        // sections is *always* our first node
        if (! isset($this->json->sections)) {

            throw new Exception\InvalidStructureException(
                'The JSON provided is not in a Carbon Editor format.'
            );
        }

        $this->convertRecursive($this->json->sections);

        return trim($this->dom->saveHTML());
    }

    /**
     * Recursively walk the object and build the HTML
     *
     * @param  array
     */
    protected function convertRecursive(array $json, DOMElement $parentElement = null)
    {
        foreach ($json as $jsonNode) {

            $component = ucfirst($jsonNode->component);

            if (empty($this->components[$component])) {

                throw new Exception\InvalidStructureException(
                    "The JSON contains the component '$component', but that isn't loaded."
                );
            }

            $element = $this->components[$component]->parse($jsonNode, $this->dom, $parentElement);

            if (isset($jsonNode->components)) {
                $this->convertRecursive($jsonNode->components, $element);
            }
        }
    }
}
