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
}
