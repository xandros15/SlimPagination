<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-19
 * Time: 16:49
 */

namespace Xandros15\SlimPagination;

class Factory
{

    public static function create(array $params) : PageInterface
    {
        $type = $params['type'];
        unset($params['type']);
        switch ($type) {
            case Pagination::QUERY_PARAM:
                return new PageQuery($params);
            case Pagination::ATTRIBUTE:
                return new PageAttribute($params);
        }

        throw new \InvalidArgumentException('Wrong type of page');
    }
}