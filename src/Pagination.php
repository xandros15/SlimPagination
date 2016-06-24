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

class Pagination implements \IteratorAggregate
{

    const OPT_TOTAL = 'total';
    const OPT_NAME = 'name';
    const OPT_TYPE = 'type';
    const OPT_PER = 'show';
    const OPT_SIDE_LENGTH = 'side';
    /** @var string */
    private $routeName;
    /** @var array */
    private $attributes;
    /** @var array */
    private $query;
    /** @var Router */
    private $router;
    /** @var int */
    private $current;
    /** @var int */
    private $lastPage;
    /** @var Slider */
    private $slider;
    /** @var array */
    private $options;

    public function __construct(Request $request, Router $router, array $options)
    {
        $this->init($request, $router, $options);
    }

    private function init(Request $request, Router $router, array $options)
    {
        $this->router = $router;
        $this->initOptions($options);
        $this->lastPage = (int) ceil($this->options[self::OPT_TOTAL] / $this->options[self::OPT_PER]);
        $this->initRequest($request);
        $this->slider = new Slider([
            'router' => $this->router,
            'query' => $this->query,
            'attributes' => $this->attributes,
            'current' => $this->current,
            'routeName' => $this->routeName,
            'lastPage' => $this->lastPage
        ], $this->options);
    }

    private function initOptions(array $options)
    {
        $default = [
            self::OPT_TOTAL => 1,
            self::OPT_PER => 10,
            self::OPT_NAME => 'page',
            self::OPT_TYPE => Page::QUERY_PARAM,
            self::OPT_SIDE_LENGTH => 3
        ];

        $options = array_merge($default, $options);

        if (filter_var($options[self::OPT_TOTAL], FILTER_VALIDATE_INT) === false || $options[self::OPT_TOTAL] <= 0) {
            throw new \InvalidArgumentException('option `OPT_TOTAL` must be int and greater than 0');
        }

        if (!is_scalar($options[self::OPT_NAME]) && !method_exists($options[self::OPT_NAME], '__toString')) {
            throw new \InvalidArgumentException('option `OPT_NAME` must be string or instance of object with __toString method');
        }

        if (filter_var($options[self::OPT_PER], FILTER_VALIDATE_INT) === false || $options[self::OPT_PER] <= 0) {
            throw new \InvalidArgumentException('option `OPT_PER` must be int and greater than 0');
        }

        if (filter_var($options[self::OPT_PER], FILTER_VALIDATE_INT) === false || $options[self::OPT_PER] <= 0) {
            throw new \InvalidArgumentException('option `OPT_PER` must be int and greater than 0');
        }

        if (filter_var($options[self::OPT_SIDE_LENGTH],
                FILTER_VALIDATE_INT) === false || $options[self::OPT_SIDE_LENGTH] <= 0
        ) {
            throw new \InvalidArgumentException('option `OPT_SIDE_LENGTH` must be int and greater than 0');
        }

        $this->options = $options;
    }

    private function initRequest(Request $request)
    {
        $this->current = $this->getCurrentPage($request);
        $this->attributes = $request->getAttributes();
        $this->query = $request->getQueryParams();
        $this->routeName = $request->getAttribute('route')->getName();
    }

    private function getCurrentPage(Request $request) : int
    {
        if (!isset($this->lastPage)) {
            throw new \RuntimeException('You must set `lastPage` property before call ' . __METHOD__);
        }

        switch ($this->options[self::OPT_TYPE]) {
            case Page::ATTRIBUTE:
                $current = $request->getAttribute($this->options[self::OPT_NAME], 1);
                break;
            case Page::QUERY_PARAM:
                $current = $request->getQueryParam($this->options[self::OPT_NAME], 1);
                break;
            default:
                throw new \InvalidArgumentException('Wrong type of page');
        }

        if ($current > $this->lastPage) {
            return $this->lastPage;
        } elseif ($current < 1) {
            return 1;
        } else {
            return $current;
        }
    }

    public function getIterator()
    {
        return $this->slider;
    }

    public function isEmpty() : bool
    {
        return $this->slider->count() > 0;
    }

    public function first() : PageInterface
    {
        return $this->slider->get('first');
    }

    public function last() : PageInterface
    {
        return $this->slider->get('last');
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function toArray()
    {
        //todo: better `from` attribute
        return [
            'per_page' => $this->options[self::OPT_PER],
            'current_page' => $this->current,
            'next_page_url' => $this->next()->pathFor(),
            'prev_page_url' => $this->previous()->pathFor(),
            'from' => 1,
            'to' => $this->lastPage
        ];
    }

    public function next() : PageInterface
    {
        return $this->slider->get('next');
    }

    public function previous() : PageInterface
    {
        return $this->slider->get('previous');
    }
}