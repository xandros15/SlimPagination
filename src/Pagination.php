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

    /**
     * set to total of items
     *
     * default: 1
     */
    const OPT_TOTAL = 'total';
    /**
     * set param name
     *
     * default: 'page'
     */
    const OPT_PARAM_NAME = 'paramName';
    /**
     * set param type
     *
     * default: Page::QUERY
     */
    const OPT_PARAM_TYPE = 'paramType';
    /**
     * set how many items should be show on one page
     *
     * default: 10
     */
    const OPT_PER_PAGE = 'show';
    /**
     * set how many buttons should be show before slider
     *
     * default: 3
     */
    const OPT_SIDE_LENGTH = 'side';
    /**
     * set type of list
     *
     * default: PageList::NORMAL
     */
    const OPT_LIST_TYPE = 'listType';
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
    /** @var PageList */
    private $pageList;
    /** @var array */
    private $options;

    /**
     * Pagination constructor.
     *
     * @param Request $request
     * @param Router $router
     * @param array $options
     */
    public function __construct(Request $request, Router $router, array $options)
    {
        $this->init($request, $router, $options);
    }

    /**
     * initialise pagination
     *
     * @param Request $request
     * @param Router $router
     * @param array $options
     */
    private function init(Request $request, Router $router, array $options)
    {
        $this->router = $router;
        $this->initOptions($options);
        $this->lastPage = (int) ceil($this->options[self::OPT_TOTAL] / $this->options[self::OPT_PER_PAGE]);
        $this->options[self::OPT_LIST_TYPE] = !($this->lastPage > Page::FIRST_PAGE) ? PageList::NONE : $this->options[self::OPT_LIST_TYPE];
        $this->initRequest($request);
        $this->pageList = new PageList([
            'router' => $this->router,
            'query' => $this->query,
            'attributes' => $this->attributes,
            'current' => $this->current,
            'routeName' => $this->routeName,
            'lastPage' => $this->lastPage
        ], $this->options);
    }

    /**
     * initialise options
     *
     * @throws \InvalidArgumentException
     * @param array $options
     */
    private function initOptions(array $options)
    {
        $default = [
            self::OPT_TOTAL => 1,
            self::OPT_PER_PAGE => 10,
            self::OPT_PARAM_NAME => 'page',
            self::OPT_PARAM_TYPE => Page::QUERY,
            self::OPT_SIDE_LENGTH => 3,
            self::OPT_LIST_TYPE => PageList::NORMAL
        ];

        $options = array_merge($default, $options);

        if (filter_var($options[self::OPT_TOTAL], FILTER_VALIDATE_INT) === false || $options[self::OPT_TOTAL] <= 0) {
            throw new \InvalidArgumentException('option `OPT_TOTAL` must be int and greater than 0');
        }

        if (!is_scalar($options[self::OPT_PARAM_NAME]) && !method_exists($options[self::OPT_PARAM_NAME],
                '__toString')
        ) {
            throw new \InvalidArgumentException('option `OPT_PARAM_NAME` must be string or instance of object with __toString method');
        }

        if (filter_var($options[self::OPT_PER_PAGE],
                FILTER_VALIDATE_INT) === false || $options[self::OPT_PER_PAGE] <= 0
        ) {
            throw new \InvalidArgumentException('option `OPT_PER_PAGE` must be int and greater than 0');
        }

        if (filter_var($options[self::OPT_SIDE_LENGTH],
                FILTER_VALIDATE_INT) === false || $options[self::OPT_SIDE_LENGTH] <= 0
        ) {
            throw new \InvalidArgumentException('option `OPT_SIDE_LENGTH` must be int and greater than 0');
        }

        $this->options = $options;
    }

    /**
     * initialise request properties
     *
     * @param Request $request
     */
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

        switch ($this->options[self::OPT_PARAM_TYPE]) {
            case Page::ATTRIBUTE:
                $current = $request->getAttribute($this->options[self::OPT_PARAM_NAME], 1);
                break;
            case Page::QUERY:
                $current = $request->getQueryParam($this->options[self::OPT_PARAM_NAME], 1);
                break;
            default:
                throw new \InvalidArgumentException('Wrong OPT_PARAM_TYPE');
        }

        if ($current > $this->lastPage) {
            return $this->lastPage;
        } elseif ($current < Page::FIRST_PAGE) {
            return Page::FIRST_PAGE;
        } else {
            return $current;
        }
    }

    /**
     * get a iterator
     * member of \IteratorAggregate
     *
     * @return PageList
     */
    public function getIterator()
    {
        return $this->pageList;
    }

    /**
     * check if pagination can be created
     *
     * @return bool
     */
    public function canCreate() : bool
    {
        return $this->lastPage > Page::FIRST_PAGE;
    }

    /**
     * returns first PageInterface instance
     *
     * @return PageInterface
     */
    public function first() : PageInterface
    {
        return $this->pageList->get('first');
    }

    /**
     * returns last PageInterface instance
     *
     * @return PageInterface
     */
    public function last() : PageInterface
    {
        return $this->pageList->get('last');
    }

    /**
     * returning params in json string
     * params:
     * - per_page: how many items on one page
     * - current_page: number of current page
     * - next_page_url: path for next page
     * - prev_page_url: path for previous page
     * - from: number of first item
     * - to: number of last item
     *
     * @param int $options JSON options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * returning params in array
     * params:
     * - per_page: how many items on one page
     * - current_page: number of current page
     * - next_page_url: path for next page
     * - prev_page_url: path for previous page
     * - from: number of first item
     * - to: number of last item
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'per_page' => $this->options[self::OPT_PER_PAGE],
            'current_page' => $this->current,
            'next_page_url' => $this->next()->pathFor(),
            'prev_page_url' => $this->previous()->pathFor(),
            'from' => Page::FIRST_PAGE,
            'to' => $this->lastPage
        ];
    }

    /**
     * returning next PageInterface instance
     *
     * @return PageInterface
     */
    public function next() : PageInterface
    {
        return $this->pageList->get('next');
    }

    /**
     * returning previous PageInterface instance
     *
     * @return PageInterface
     */
    public function previous() : PageInterface
    {
        return $this->pageList->get('previous');
    }
}