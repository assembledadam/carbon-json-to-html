<?php
/**
 * Carbon JSON to HTML converter
 *
 * @author  Adam McCann (@AssembledAdam)
 * @license MIT (see LICENSE file)
 */
namespace CarbonJsonToHtml;

/**
 * Converter
 */
class Converter
{
    /**
     * New Line
     */
    const NEWLINE = PHP_EOL;

    /**
     * @var \DomDocument
     */
    protected $dom;

    /**
     * JSON object string to convert
     *
     * @var string
     */
    protected $json;

    /**
     * @var array
     */
    // protected $parsers = [];

    /**
     * Constructor
     *
     * @param string
     * @param \DomDocument
     */
    public function __construct($json, \DomDocument $doc = null)
    {
        $this->json = $json;
        $this->doc  = $doc;

        if (json_decode($json) === false) {

            throw new Exception\NotTraversableException(
                'The JSON provided is not valid'
            );
        }

        $this->convert();
    }

    /**
     * Traverse a string of JSON and proxy out to convert markdownable tags
     *
     * @param  string $html
     * @return string
     */
    public function convert()
    {

    }
}
