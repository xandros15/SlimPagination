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

    public function __construct(array $params, array $options)
    {
        parent::__construct();
        $this->compile($params, $options);
    }

    private function compile(array $params, array $options)
    {
        $params += [
            'type' => $options[Pagination::OPT_TYPE],
            'paramName' => $options[Pagination::OPT_NAME],
        ];

        $this->compileSidePages($params);
        $this->compileEdgePages($params);
        if ($params['lastPage'] < 2) {
            $this->set('list', []);
            return;
        }

        $totalSpace = 2 * $options[Pagination::OPT_SIDE_LENGTH];

        if ($params['current'] <= $totalSpace) {
            $this->compileLeftList($params, $totalSpace);
        } elseif ($params['current'] > ($params['lastPage'] - $totalSpace)) {
            $this->compileRightList($params, $totalSpace);
        } else {
            $this->compileAdjacentList($params, $options[Pagination::OPT_SIDE_LENGTH]);
        }
    }

    private function compileSidePages(array $params)
    {
        //todo: $this->current - 1 < 1 ? 1 : $this->current - 1 // over min
        //todo: $this->current + 1 > $this->lastPage ? $this->lastPage : $this->current + 1 // over max
        $this->set('previous', Factory::create($params + [
                'pageNumber' => max(1, $params['current'] - 1),
                'pageName' => '&lt;'
            ]));
        $this->set('next', Factory::create($params + [
                'pageNumber' => min($params['current'] + 1, $params['lastPage']),
                'pageName' => '&gt;'
            ]));
    }

    private function compileEdgePages(array $params)
    {
        $this->set('first', Factory::create($params + [
                'pageNumber' => Page::FIRST_PAGE,
                'pageName' => Page::FIRST_PAGE
            ]));
        $this->set('last', Factory::create($params + [
                'pageNumber' => $params['lastPage'],
                'pageName' => $params['lastPage']
            ]));
    }

    private function compileLeftList(array $params, int $totalSpace)
    {
        $list = $this->getRangeList(['start' => Page::FIRST_PAGE, 'end' => $totalSpace + 2], $params);
        $list[] = Factory::create(['pageName' => '...', Pagination::OPT_TYPE => Page::EMPTY]);
        $list[] = $this->get('last');
        $this->set('list', $list);
    }

    private function getRangeList(array $range, array $params)
    {
        $list = [];
        for ($page = $range['start']; $page <= $range['end']; $page++) {
            $list[$page] = Factory::create($params + [
                    'pageNumber' => $page,
                    'pageName' => $page
                ]);
        }
        return $list;
    }

    private function compileRightList(array $params, int $totalSpace)
    {
        $list = [
            $this->get('first'),
            Factory::create(['pageName' => '...', Pagination::OPT_TYPE => Page::EMPTY])
        ];
        $range = $this->getRangeList([
            'start' => $params['lastPage'] - ($totalSpace + 2),
            'end' => $params['lastPage']
        ], $params);
        $this->set('list', array_merge($list, $range));
    }

    private function compileAdjacentList(array $params, int $sideLength)
    {
        $list = [];
        $list[] = $this->get('first');
        $list[] = Factory::create(['pageName' => '...', Pagination::OPT_TYPE => Page::EMPTY]);
        $list = array_merge($list, $this->getRangeList([
            'start' => $params['current'] - $sideLength,
            'end' => $params['current'] + $sideLength
        ], $params));
        $list[] = Factory::create(['pageName' => '...', Pagination::OPT_TYPE => Page::EMPTY]);
        $list[] = $this->get('last');
        $this->set('list', $list);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->get('list', []));
    }

    public function count()
    {
        return count($this->get('list'));
    }

    private function compileMiniList()
    {
        $this->set('list', [
            $this->get('previous'),
            $this->get('next')
        ]);
    }
}