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
        return $this->params['page'];
    }

    public function pathFor() : string
    {
        $data = $this->params['request']->getAttribute('route')->getArguments();
        return $this->params['router']->pathFor(
            $this->params['request']->getAttribute('route')->getName(),
            array_merge($data, [$this->params['name'] => $this->params['page']]),
            $this->params['request']->getQueryParams()
        );
    }

    public function isCurrent() : bool
    {
        return $this->params['page'] == $this->params['request']
            ->getAttribute('route')
            ->getArgument($this->params['name'], 1);
    }
}