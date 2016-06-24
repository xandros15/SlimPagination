<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-19
 * Time: 17:22
 */

namespace Xandros15\SlimPagination;

use Slim\Router;

abstract class Page
{
    /** for query type of param */
    const QUERY = 1;
    /** for attribute type of param */
    const ATTRIBUTE = 2;
    /** slider page */
    const EMPTY = 3;
    /** number of first page */
    const FIRST_PAGE = 1;
    /** @var Router */
    protected $router;
    /** @var string */
    protected $pageName;
    /** @var string */
    protected $pageNumber;
    /** @var string */
    protected $paramName;
    /** @var string */
    protected $routeName;
    /** @var int */
    protected $current;
    /** @var array */
    protected $query;
    /** @var array */
    protected $attributes;

    /**
     * Page constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        foreach ($params as $name => $param) {
            $this->{$name} = $param;
        }
    }
}