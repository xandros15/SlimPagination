<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-18
 * Time: 22:43
 */

namespace Xandros15\SlimPagination;

use Slim\Http\Request;
use Slim\Router;

class Pagination
{

    const OPT_MAX = 'max';
    const OPT_NAME = 'name';
    const OPT_TYPE = 'type';
    const QUERY_PARAM = 1;
    const ATTRIBUTE = 2;
    /** @var Request */
    private $request;
    /** @var Router */
    private $router;
    /** @var string */
    private $name;
    /** @var int */
    private $max;
    /** @var string */
    private $type;
    /** @var int */
    private $current;
    /** @var Iterator */
    private $iterator;

    public function getIterator()
    {
        return $this->iterator;
    }

    private function initPages()
    {
        $this->iterator = new Iterator([
            'name' => $this->name,
            'router' => $this->router,
            'request' => $this->request,
            'type' => $this->type,
            'max' => $this->max
        ]);
    }

    public function __construct(Request $request, Router $router, array $options)
    {
        $this->request = $request;
        $this->router = $router;
        $this->init($options);
        $this->initPages();
    }

    private function init(array $options)
    {
        $default = [
            self::OPT_MAX => 1,
            self::OPT_NAME => 'page',
            self::OPT_TYPE => self::QUERY_PARAM
        ];

        $options = array_merge($default, $options);

        if ($options[self::OPT_MAX] <= 0) {
            throw new \InvalidArgumentException('option `max` must be int and greater than 0');
        }

        if (!is_scalar($options[self::OPT_NAME]) && !method_exists($options[self::OPT_NAME], '__toString')) {
            throw new \InvalidArgumentException('option `name` must be string or instance of object with __toString method');
        }


        $this->max = $options[self::OPT_MAX];
        $this->name = $options[self::OPT_NAME];
        $this->type = $options[self::OPT_TYPE];
        $this->current = $this->getCurrentPage();

    }


    public function isCreatable() : bool
    {
        return $this->iterator->count() > 1;
    }

    public function previous() : PageInterface
    {
        return $this->iterator->get(max($this->current - 1, 1));
    }

    public function next() : PageInterface
    {
        return $this->iterator->get(min($this->current + 1, $this->max));
    }

    public function first() : PageInterface
    {
        return $this->iterator->get(1);
    }

    public function last() : PageInterface
    {
        return $this->iterator->get($this->max);
    }

    private function getCurrentPage() : int
    {
        switch ($this->type) {
            case self::ATTRIBUTE:
                return $this->request->getAttribute($this->name, 1);
            case self::QUERY_PARAM:
                return $this->request->getQueryParam($this->name, 1);
        }
        throw new \InvalidArgumentException('Wrong type of page');
    }
}