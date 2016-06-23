<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-18
 * Time: 22:47
 */

namespace Xandros15\SlimPagination;

class PageQuery extends Page implements PageInterface
{

    public function getPageName() : string
    {
        return $this->pageName;
    }

    public function pathFor() : string
    {
        $newParams = [$this->paramName => $this->pageNumber];
        $queryParams = !($this->query) ? $newParams : array_merge($this->query, $newParams);

        return $this->router->pathFor(
            $this->routeName,
            $this->attributes,
            $queryParams
        );
    }

    public function isCurrent() : bool
    {
        return $this->pageNumber == $this->current;
    }
}