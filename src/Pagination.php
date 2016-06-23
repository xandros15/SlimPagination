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

class Pagination implements \IteratorAggregate
{

    const OPT_TOTAL = 'total';
    const OPT_NAME = 'name';
    const OPT_TYPE = 'type';
    const OPT_PER = 'show';
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
    /** @var Collection */
    private $iterator;
    /** @var array */
    private $options;
    private $start;
    private $end;

    public function __construct(Request $request, Router $router, array $options)
    {
        $this->iterator = new Collection();
        $this->router = $router;
        $this->init($options);
        $this->initRequest($request);
        $this->initPages();
        $this->setStartEnd();
        $this->compile();
    }

    private function init(array $options)
    {
        $default = [
            self::OPT_TOTAL => 1,
            self::OPT_PER => 10,
            self::OPT_NAME => 'page',
            self::OPT_TYPE => Page::QUERY_PARAM
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
        switch ($this->options[self::OPT_TYPE]) {
            case Page::ATTRIBUTE:
                return $request->getAttribute($this->options[self::OPT_NAME], 1);
            case Page::QUERY_PARAM:
                return $request->getQueryParam($this->options[self::OPT_NAME], 1);
        }
        throw new \InvalidArgumentException('Wrong type of page');
    }

    private function initPages()
    {
        $this->iterator = new Collection();
    }

    private function setStartEnd()
    {
        $offset = (int) ($this->options[self::OPT_PER] / 2.1);
        $start = $this->current - $offset; // current - edge length - 1 'cuz start on 0
        $end = $this->current + $offset;

        if ($end > $this->options[self::OPT_TOTAL]) { // if wanna edge maximum is very last element
            $end = $this->options[self::OPT_TOTAL];
            $start = $this->options[self::OPT_TOTAL] - $this->options[self::OPT_PER] + 1;
        } elseif ($start < 1) { // if wanna edge minimum is very first element
            $start = 1;
            $end = $this->options[self::OPT_PER];
        }
        $this->start = $start;
        $this->end = $end;
    }

    private function compile()
    {
        $data = [
            'type' => $this->options[self::OPT_TYPE],
            'paramName' => $this->options[self::OPT_NAME],
            'router' => $this->router,
            'query' => $this->query,
            'attributes' => $this->attributes,
            'current' => $this->current,
            'routeName' => $this->routeName
        ];
        $list = [];

        for ($current = $this->start; $current <= $this->end; $current++) {
            $list[] = Factory::create($data + [
                    'pageNumber' => $current,
                    'pageName' => $current
                ]);
        }
        $sideControls = $this->createPreviousAndNext($data);
        $edgeControls = $this->createFirstAndLast($data);


        if ($edgeControls['first']->pageNumber < $this->start) {
            array_unshift($list, $edgeControls['first']);
        }
        if ($edgeControls['last']->pageNumber > $this->end) {
            $list[] = $edgeControls['last'];
        }

        array_unshift($list, $sideControls['previous']);
        $list[] = $sideControls['next'];
        foreach ($list as $key => $item) {
            $this->iterator->set($key, $item);
        }
    }

    private function createPreviousAndNext(array $data) : array
    {
        return [
            'previous' => Factory::create($data + [
                    'pageNumber' => max(1, $this->current - 1),
                    'pageName' => '&lt;'
                ]),
            'next' => Factory::create($data + [
                    'pageNumber' => min($this->current + 1, $this->options[self::OPT_TOTAL]),
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
                    'pageNumber' => $this->options[self::OPT_TOTAL],
                    'pageName' => $this->options[self::OPT_TOTAL]
                ])
        ];
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    public function isEmpty() : bool
    {
        return $this->iterator->count() > 0;
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
}