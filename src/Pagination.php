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

class Pagination implements \Iterator, \Countable
{

    const QUERY_PARAM = 1;
    const ATTRIBUTE = 2;
    /** @var Request */
    private $request;
    /** @var Router */
    private $router;
    /** @var string */
    private $name;
    /** @var int */
    private $maxPages;
    /** @var int */
    private $page;
    /** @var array */
    private $list;
    /** @var string */
    private $type;

    private function initPages()
    {
        $this->page = 1;
        for ($page = $this->page; $page <= $this->maxPages; $page++) {
            $this->list[$page] = Factory::create([
                'page' => $page,
                'name' => $this->name,
                'router' => $this->router,
                'request' => $this->request,
            ], $this->type);
        }
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
            'maxPages' => 1,
            'name' => 'page',
            'type' => self::QUERY_PARAM
        ];
        $options = array_merge($default, $options);

        if ($options['maxPages'] <= 0) {
            throw new \InvalidArgumentException('maxPages must be int and greater than 0');
        }

        if (!is_scalar($options['name']) && !method_exists($options['name'], '__toString')) {
            throw new \InvalidArgumentException('name must be string or instance of object with __toString method');
        }


        $this->maxPages = $options['maxPages'];
        $this->name = $options['name'];
        $this->type = $options['type'];

    }


    public function isCreatable() : bool
    {
        return $this->count() > 1;
    }

    public function getPrevious() : PageInterface
    {
        return $this->list[max($this->page - 1, 1)];
    }

    public function getNext() : PageInterface
    {
        return $this->list[min($this->page + 1, $this->maxPages)];
    }

    public function first() : PageInterface
    {
        return $this->list[1];
    }

    public function last() : PageInterface
    {
        return $this->list[$this->maxPages];
    }
    /**
     * Part of Iterator
     */

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->list[$this->page];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->page++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->page;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return isset($this->list[$this->page]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->page = 1;
    }

    /**
     * Part of countable
     */

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->maxPages;
    }
}