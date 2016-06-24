<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-24
 * Time: 00:23
 */

namespace Xandros15\SlimPagination;

use Slim\Collection;

class PageList extends Collection
{

    /** for type list */
    const MINI = 1;
    /** for type list */
    const NORMAL = 2;
    /** for type list */
    const NONE = 3;

    /**
     * PageList constructor.
     * @param array $params
     * @param array $options
     */
    public function __construct(array $params, array $options)
    {
        parent::__construct();
        $this->compile($params, $options);
    }

    /**
     * init pages
     *
     * @param array $params
     * @param array $options
     */
    private function compile(array $params, array $options)
    {
        $params += [
            Pagination::OPT_PARAM_TYPE => $options[Pagination::OPT_PARAM_TYPE],
            'paramName' => $options[Pagination::OPT_PARAM_NAME],
        ];
        $this->preCompile($params);

        switch ($options[Pagination::OPT_LIST_TYPE]) {
            case self::MINI:
                $this->compileMiniList();
                break;
            case self::NORMAL:
                $this->compileNormalList($params, 2 * $options[Pagination::OPT_SIDE_LENGTH]);
                break;
            case self::NONE:
                $this->set('list', []);
                break;
            default:
                throw new \InvalidArgumentException('Wrong OPT_LIST_TYPE');
        }
    }

    /**
     * compile previous, next, last and first page
     * to get it use $this->get('next'), $this->get('previous'), $this->get('first') or $this->get('last')
     * @param $params
     */
    private function preCompile($params)
    {
        $this->compileSidePages($params);
        $this->compileEdgePages($params);
    }

    /**
     * compile next and previous page
     * to get it use $this->get('next') or $this->get('previous')
     * @param array $params
     */
    private function compileSidePages(array $params)
    {
        //todo: $this->current - 1 < 1 ? 1 : $this->current - 1 // over min
        //todo: $this->current + 1 > $this->lastPage ? $this->lastPage : $this->current + 1 // over max
        $this->set('previous', PageFactory::create($params + [
                'pageNumber' => max(1, $params['current'] - 1),
                'pageName' => '&laquo;'
            ]));
        $this->set('next', PageFactory::create($params + [
                'pageNumber' => min($params['current'] + 1, $params['lastPage']),
                'pageName' => '&raquo;'
            ]));
    }

    /**
     * compile first and last page
     * to get it use $this->get('first') or $this->get('last')
     * @param array $params
     */
    private function compileEdgePages(array $params)
    {
        $this->set('first', PageFactory::create($params + [
                'pageNumber' => Page::FIRST_PAGE,
                'pageName' => Page::FIRST_PAGE
            ]));
        $this->set('last', PageFactory::create($params + [
                'pageNumber' => $params['lastPage'],
                'pageName' => $params['lastPage']
            ]));
    }

    /**
     * compile mini list type
     * to get it use $this->get('list')
     */
    private function compileMiniList()
    {
        $this->set('list', [
            $this->get('previous'),
            $this->get('next')
        ]);
    }

    /**
     * compile normal list type
     * to get it use $this->get('list')
     *
     * @param $params
     * @param $totalSpace
     */
    private function compileNormalList($params, $totalSpace)
    {
        if ($params['current'] <= $totalSpace) {
            $this->compileLeftList($params, $totalSpace);
        } elseif ($params['current'] > ($params['lastPage'] - $totalSpace)) {
            $this->compileRightList($params, $totalSpace);
        } else {
            $this->compileAdjacentList($params, (int) $totalSpace / 2);
        }
    }

    /**
     * compile list too close to left
     *
     * @param array $params
     * @param int $totalSpace
     */
    private function compileLeftList(array $params, int $totalSpace)
    {
        $list = $this->getRangePages(['start' => Page::FIRST_PAGE, 'end' => $totalSpace + 2], $params);
        $list[] = PageFactory::create(['pageName' => '...', Pagination::OPT_PARAM_TYPE => Page::EMPTY]);
        $list[] = $this->get('last');
        $this->set('list', $list);
    }

    /**
     * get range of pages
     *
     * @param array $range
     * @param array $params
     * @return array
     */
    private function getRangePages(array $range, array $params)
    {
        $list = [];
        for ($page = $range['start']; $page <= $range['end']; $page++) {
            $list[$page] = PageFactory::create($params + [
                    'pageNumber' => $page,
                    'pageName' => $page
                ]);
        }
        return $list;
    }

    /**
     * compile list too close to right
     *
     * @param array $params
     * @param int $totalSpace
     */
    private function compileRightList(array $params, int $totalSpace)
    {
        $list = [
            $this->get('first'),
            PageFactory::create(['pageName' => '...', Pagination::OPT_PARAM_TYPE => Page::EMPTY])
        ];
        $range = $this->getRangePages([
            'start' => $params['lastPage'] - ($totalSpace + 2),
            'end' => $params['lastPage']
        ], $params);
        $this->set('list', array_merge($list, $range));
    }

    /**
     * compile list if is center
     *
     * @param array $params
     * @param int $sideLength
     */
    private function compileAdjacentList(array $params, int $sideLength)
    {
        $list = [];
        $list[] = $this->get('first');
        $list[] = PageFactory::create(['pageName' => '...', Pagination::OPT_PARAM_TYPE => Page::EMPTY]);
        $list = array_merge($list, $this->getRangePages([
            'start' => $params['current'] - $sideLength,
            'end' => $params['current'] + $sideLength
        ], $params));
        $list[] = PageFactory::create(['pageName' => '...', Pagination::OPT_PARAM_TYPE => Page::EMPTY]);
        $list[] = $this->get('last');
        $this->set('list', $list);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->get('list', []));
    }

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
        return count($this->get('list'));
    }
}