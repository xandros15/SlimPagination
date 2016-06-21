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
        return $this->page;
    }

    public function pathFor() : string
    {
        $queryParams = array_merge($this->request->getQueryParams(), [
            $this->paramName => $this->page
        ]);
        return $this->router->pathFor(
            $this->request->getAttribute('route')->getName(),
            $this->request->getAttributes(),
            $queryParams
        );
    }

    public function isCurrent() : bool
    {
        return $this->page == $this->current;
    }
}