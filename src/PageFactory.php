<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-19
 * Time: 16:49
 */

namespace Xandros15\SlimPagination;

class PageFactory
{

    public static function create(array $params) : PageInterface
    {
        $type = $params[Pagination::OPT_PARAM_TYPE];
        unset($params[Pagination::OPT_PARAM_TYPE]);
        switch ($type) {
            case Page::QUERY:
                return new PageQuery($params);
            case Page::ATTRIBUTE:
                return new PageAttribute($params);
            case Page::EMPTY:
                return new PageEmpty($params);
        }

        throw new \InvalidArgumentException('Wrong OPT_PARAM_TYPE');
    }
}