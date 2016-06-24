<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-19
 * Time: 17:22
 */

namespace Xandros15\SlimPagination;

use Slim\Router;

/**
 * @property Router router
 * @property string pageName
 * @property string pageNumber
 * @property string paramName
 * @property string routeName
 * @property int current
 * @property array query
 * @property array attributes
 */

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
    /** @var array */
    private $params;

    /**
     * Page constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        throw new \InvalidArgumentException('Property `' . __CLASS__ . '::' . $name . '` doesn\'t exist');
    }

}