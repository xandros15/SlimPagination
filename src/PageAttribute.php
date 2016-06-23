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
        if ($this->isCurrent()) {
            return '#';
        }
        $data = $this->request->getAttribute('route')->getArguments();
        return $this->router->pathFor(
            $this->request->getAttribute('route')->getName(),
            array_merge($data, [$this->paramName => $this->pageNumber]),
            $this->request->getQueryParams()
        );
    }

    public function isCurrent() : bool
    {
        return $this->pageNumber == $this->current;
    }
}