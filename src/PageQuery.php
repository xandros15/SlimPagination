<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-18
 * Time: 22:47
 */

namespace Xandros15\SlimPagination;

use Slim\Http\Request;

class PageQuery extends Page implements PageInterface
{

    public function getPageName() : string
    {
        return $this->params['page'];
    }

    public function pathFor() : string
    {
        $queryParams = array_merge($this->params['request']->getQueryParams(), [
            $this->params['name'] => $this->params['page']
        ]);
        return $this->params['router']->pathFor(
            $this->params['request']->getAttribute('route')->getName(),
            $this->params['request']->getAttributes(),
            $queryParams
        );
    }

    public function isCurrent() : bool
    {
        return $this->params['page'] == max(1, $this->params['request']->getQueryParam($this->params['name'], 1));
    }
}