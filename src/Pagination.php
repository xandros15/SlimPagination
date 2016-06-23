<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-18
 * Time: 22:43
 */

namespace Xandros15\SlimPagination;

use Slim\Collection;
use Slim\Http\Request;
use Slim\Router;

class Pagination
{

    const OPT_TOTAL = 'total';
    const OPT_NAME = 'name';
    const OPT_TYPE = 'type';
    const OPT_SHOW = 'show';
    /** @var string */
    private $routeName;
    /** @var array */
    private $attributes;
    /** @var array */
    private $query;
    /** @var Request */
    private $request;
    /** @var Router */
    private $router;
    /** @var int */
    private $current;
    /** @var Collection */
    private $iterator;
    /** @var array */
    private $options;
    /** @var int */
    private $page;
    /** @var array */
    private $list;
    /** @var int */
    private $max;
    /** @var int */
    private $show;
    private $start;
    private $end;
    private $data;

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
            self::OPT_TOTAL => 1,
            self::OPT_SHOW => 2,
            self::OPT_NAME => 'page',
            self::OPT_TYPE => Page::QUERY_PARAM
        ];

        $options = array_merge($default, $options);

        if ($options[self::OPT_TOTAL] <= 0) {
            throw new \InvalidArgumentException('option `OPT_TOTAL` must be int and greater than 0');
        }

        if (!is_scalar($options[self::OPT_NAME]) && !method_exists($options[self::OPT_NAME], '__toString')) {
            throw new \InvalidArgumentException('option `OPT_NAME` must be string or instance of object with __toString method');
        }

        if ($options[self::OPT_SHOW] < 2) {
            throw new \InvalidArgumentException('option `OPT_SHOW` must be int and greater or equal than 2');
        }

        $this->options = $options;
        $this->current = $this->getCurrentPage();
        $this->attributes = $this->request->getAttributes();
        $this->query = $this->request->getQueryParams();
        $this->routeName = $this->request->getAttribute('route')->getName();
    }

    private function getCurrentPage() : int
    {
        switch ($this->options[self::OPT_TYPE]) {
            case Page::ATTRIBUTE:
                return $this->request->getAttribute($this->options[self::OPT_NAME], 1);
            case Page::QUERY_PARAM:
                return $this->request->getQueryParam($this->options[self::OPT_NAME], 1);
        }
        throw new \InvalidArgumentException('Wrong type of page');
    }

    private function initPages()
    {
        $this->iterator = new Collection();
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    public function isCreatable() : bool
    {
        return true;
    }

    public function previous() : PageInterface
    {
        return $this->iterator->get(max($this->current - 1, 1));
    }

    public function next() : PageInterface
    {
        return $this->iterator->get(min($this->current + 1, $this->options[self::OPT_TOTAL]));
    }

    public function first() : PageInterface
    {
        return $this->iterator->get(1);
    }

    public function last() : PageInterface
    {
        return $this->iterator->get($this->options[self::OPT_TOTAL]);
    }

    /**
     * Iterator constructor.
     * @param $data
     */
    public function make($data)
    {
        $collection = new Collection();
        $this->show = $data['show'];
        $this->max = $data['max'];
        $data['paramName'] = $data['name'];
        unset($data['name']);
        unset($data['show']);
        unset($data['max']);
        $this->setStartEnd($data);
        $this->data = $data;

    }

    private function setStartEnd(array $data)
    {
        $offset = (int) ($this->show / 2.1);
        $start = $data['current'] - $offset; // current - edge length - 1 'cuz start on 0
        $end = $data['current'] + $offset;

        if ($end > $this->max) { // if wanna edge maximum is very last element
            $end = $this->max;
            $start = $this->max - $this->show + 1;
        } elseif ($start < 1) { // if wanna edge minimum is very first element
            $start = 1;
            $end = $this->show;
        }
        $this->start = $start;
        $this->end = $end;
    }

    public function toArray()
    {
        return $this->compile();
    }

    private function compile(): array
    {
        $list = [];

        for ($current = $this->start; $current <= $this->end; $current++) {
            $list[] = Factory::create($this->data + [
                    'pageNumber' => $current,
                    'pageName' => $current
                ]);
        }
        /** @var $next PageInterface */
        /** @var $previous PageInterface */
        /** @var $first PageInterface */
        /** @var $last PageInterface */
        $sideControls = $this->createPreviousAndNext($this->data);
        $edgeControls = $this->createFirstAndLast($this->data);


        if ($edgeControls['first']->pageNumber < $this->start) {
            array_unshift($list, $edgeControls['first']);
        }
        if ($edgeControls['last']->pageNumber > $this->end) {
            $list[] = $edgeControls['last'];
        }

        array_unshift($list, $sideControls['previous']);
        $list[] = $sideControls['next'];

        return $list;
    }

    private function createPreviousAndNext(array $data) : array
    {
        return [
            'previous' => Factory::create($data + [
                    'pageNumber' => max(1, $data['current'] - 1),
                    'pageName' => '&lt;'
                ]),
            'next' => Factory::create($data + [
                    'pageNumber' => min($data['current'] + 1, $this->max),
                    'pageName' => '&gt;'
                ])
        ];

    }

    private function createFirstAndLast(array $data) : array
    {
        return [
            'first' => Factory::create($data + [
                    'pageNumber' => 1,
                    'pageName' => 1
                ]),
            'last' => Factory::create($data + [
                    'pageNumber' => $this->max,
                    'pageName' => $this->max
                ])
        ];
    }
}