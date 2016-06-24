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
    public function getPageName() : string
    {
        return $this->pageName;
    }

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