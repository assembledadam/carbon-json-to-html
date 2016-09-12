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
use Candybanana\CarbonJsonToHtml\Exceptions;

/**
 * Converter
 */
class EmbeddedComponent extends AbstractComponent implements ComponentInterface
{
    /**
     * This components config: contains handler for resoloving oEmbed URLs into HTML
     *
     * [
     *     <name of provider> => <Closure($json)>,
     *     <name of provider> => <Closure($json)>, (and so on...)
     * ]
     *
     * @var array
     */
    protected $config;

    /**
     * Component constructor
     *
     * @param array|null
     */
    public function __construct(array $config = null)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(stdClass $json, DOMDocument $dom, DOMElement $parentElement)
    {
        $figure = $dom->createElement('figure');

        if (empty($this->config[$json->provider])) {

            throw new Exceptions\InvalidStructureException("Provider '$json->provider' has no handler assigned.");
        }

        $this->appendHtml($json, $dom, $figure);

        $this->appendCaption($json, $dom, $figure);

        return $parentElement->appendChild($figure);
    }

    /**
     * Append HTML to component
     *
     * @param  \stdClass
     * @param  \DOMDocument
     * @param  \DOMElement
     */
    protected function appendHtml(stdClass $json, DOMDocument $dom, DOMElement $figure)
    {
        $html = $this->config[$json->provider]($json);

        // create a temporary document and load the plain html
        $tmpDoc = new DOMDocument;
        libxml_use_internal_errors(true); // for html5 tags
        $tmpDoc->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>');
        $tmpDoc->encoding = 'UTF-8';
        libxml_clear_errors();

        // import and attach the created nodes to the paragraph
        foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {

            $node = $dom->importNode($node, true);
            $figure->appendChild($node);
        }

        // set figure classes
        $class = 'embed-container';

        // set type and serviceName as class for easy custom CSS styling (as Carbon does)
        if (! empty($json->serviceName)) {
            $class .= ' ' . $json->type . ' ' . $json->serviceName;
        }

        // if type is video and the html has an iframe, apply responsive fix class
        if ($json->type == 'video' && strpos($html, '<iframe') !== false) {
            $class .= ' responsive-video';
        }

        $figure->setAttribute('class', $class);
    }

    /**
     * Append caption to component
     *
     * @param  \stdClass
     * @param  \DOMDocument
     * @param  \DOMElement
     */
    protected function appendCaption(stdClass $json, DOMDocument $dom, DOMElement $figure)
    {
        // add figcaption if we have one
        if (! empty($json->caption)) {

            $figcaption = $dom->createElement('figcaption');
            $figcaptionText = $dom->createTextNode($json->caption);
            $figcaption->appendChild($figcaptionText);

            $figure->appendChild($figcaption);
        }
    }
}
