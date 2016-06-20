<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-19
 * Time: 17:22
 */

namespace Xandros15\SlimPagination;

abstract class Page
{
    /** @var array */
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }
}