<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-19
 * Time: 17:22
 */

namespace Xandros15\SlimPagination;

use Slim\Http\Request;
use Slim\Router;

/**
 * @property Request request
 * @property Router router
 * @property int page
 * @property string name
 */

abstract class Page
{
    /** @var array */
    private $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function __get(string $name)
    {
        if (isset($params[$name])) {
            return $params[$name];
        }
        throw new \InvalidArgumentException('Property `' . __CLASS__ . '::' . $name . '` doesn\'t exist');
    }

}