<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-18
 * Time: 22:47
 */

namespace Xandros15\SlimPagination;

class PageAttribute extends Page implements PageInterface
{

    /**
     * Returning current page number
     *
     * @return string
     */
    public function getPageName() : string
    {
        return $this->pageName;
    }

    /**
     * Returning parsed address uri
     *
     * @return string
     */
    public function pathFor() : string
    {
        $newAttributes = [$this->paramName => $this->pageNumber];
        $attributes = !($this->attributes) ? $newAttributes : array_merge($this->attributes, $newAttributes);

        return $this->router->pathFor(
            $this->routeName,
            $attributes,
            $this->query
        );
    }

    /**
     * Check if this page is current
     *
     * @return bool
     */
    public function isCurrent() : bool
    {
        return $this->pageNumber == $this->current;
    }

    /**
     * check if is Slider
     *
     * @return bool
     */
    public function isSlider() : bool
    {
        return false;
    }
}