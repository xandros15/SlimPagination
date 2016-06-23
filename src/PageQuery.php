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
        if ($this->isCurrent()) {
            return '#';
        }

        $newParams = [$this->paramName => $this->pageNumber];
        $queryParams = !($queryParams = $this->request->getQueryParams()) ? $newParams : $queryParams = array_merge($queryParams,
            $newParams);

        return $this->router->pathFor(
            $this->request->getAttribute('route')->getName(),
            $this->request->getAttributes(),
            $queryParams
        );
    }

    public function isCurrent() : bool
    {
        return $this->pageNumber == $this->current;
    }
}