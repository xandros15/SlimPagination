<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-20
 * Time: 19:46
 */

namespace Xandros15\SlimPagination;


class Iterator implements \Iterator, \Countable
{
    /** @var int */
    private $page;
    /** @var array */
    private $list;
    /** @var int */
    private $max;

    /**
     * Iterator constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->page = 1;
        $this->max = $data['max'];
        $show = $data['show'];
        for ($page = $this->page; $page <= $data['max']; $page++) {
            $this->list[$page] = Factory::create([
                'page' => $page,
                'paramName' => $data['name'],
                'router' => $data['router'],
                'request' => $data['request'],
                'current' => $data['current'],
                'type' => $data['type']
            ]);
        }
    }

    public function get(int $key)
    {
        if ($this->has($key)) {
            return $this->list[$key];
        }
        throw new \InvalidArgumentException("The element doesn't exist. `$key` given.");
    }

    public function has($key)
    {
        return isset($this->list[$key]);
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
        return $this->max;
    }
}