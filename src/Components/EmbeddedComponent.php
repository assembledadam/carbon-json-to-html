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
     *     // for rendering oEmbeds and injecting the response directly into the output HTML
     *     'providers' => [
     *         <name of provider> => <Closure($json)>,
     *         <name of provider> => <Closure($json)>, (and so on...)
     *     ],
     *
     *     // for third party handling of the oEmbed - injects an <iframe> with the given URL into the HTML
     *     'iframe' => 'https://your-oembed-provider/oembed'
     *
     *     // for third party handling of the oEmbed - injects AMP compatible iframe with the given URL into the HTML
     *     'amp' => 'https://your-oembed-provider/oembed'
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

        if (empty($this->config['iframe']) &&
            empty($this->config['amp']) &&
            empty($this->config['providers'][$json->provider])) {

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
        if (isset($this->config['providers'])) {

            $this->injectOembedResponse($json, $dom, $figure);

        } else if (isset($this->config['iframe'])) {

            $this->injectIFrame($json, $dom, $figure);

        } else {

            $this->injectAMPFrame($json, $dom, $figure);
        }
    }

    /**
     * Append oEmbed response HTML directly into document
     *
     * @param  \stdClass
     * @param  \DOMDocument
     * @param  \DOMElement
     */
    protected function injectOEmbedResponse(stdClass $json, DOMDocument $dom, DOMElement $figure)
    {
        $html = $this->config['providers'][$json->provider]($json);

        // create a temporary document and load the plain html
        $domElement = $this->loadHtml($html, false);

        // import and attach the created nodes to the paragraph
        foreach ($domElement->childNodes as $node) {

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
     * Append iframe for third party rendering of oEmbed
     *
     * @param  \stdClass
     * @param  \DOMDocument
     * @param  \DOMElement
     */
    protected function injectIFrame(stdClass $json, DOMDocument $dom, DOMElement $figure)
    {
        $url = $this->config['iframe'] . '?url=' . $json->url;

        $iframe = $dom->createElement('iframe');
        $iframe->setAttribute('src', $url);

        $figure->appendChild($iframe);
    }

    /**
     * Append AMP-compatible iframe for third party rendering of oEmbed
     *
     * @param  \stdClass
     * @param  \DOMDocument
     * @param  \DOMElement
     */
    protected function injectAMPFrame(stdClass $json, DOMDocument $dom, DOMElement $figure)
    {
        $url = $this->config['amp'] . '?plain=1&ratio=1.778&url=' . $json->url;

        $iframe = $dom->createElement('amp-iframe');
        $iframe->setAttribute('src', $url);
        $iframe->setAttribute('width', 660);
        $iframe->setAttribute('height', 373);
        $iframe->setAttribute('sandbox', 'allow-scripts allow-same-origin allow-popups');
        $iframe->setAttribute('frameborder', 0);
        $iframe->setAttribute('allowfullscreen', 'allowfullscreen');
        $iframe->setAttribute('layout', 'responsive');

        // add placeholder
        $placeholder = $dom->createElement('div');
        $placeholder->setAttribute('placeholder', 'placeholder');
        $placeholder->setAttribute('class', 'placeholder placeholder--iframe');
        $placeholderText = $dom->createTextNode('Loading... hold on!');
        $placeholder->appendChild($placeholderText);
        $iframe->appendChild($placeholder);

        $figure->appendChild($iframe);
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
