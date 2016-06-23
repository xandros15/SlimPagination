<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-24
 * Time: 00:23
 */

namespace Xandros15\SlimPagination;


use Slim\Collection;

class Slider extends Collection
{
    /** @var int */
    private $start;
    /** @var int */
    private $end;

    public function __construct(array $params, array $options)
    {
        parent::__construct();
        $this->setStartEnd($params['current'], $options);
        $this->compile($params, $options);
    }

    private function setStartEnd(int $current, array $options)
    {
        $offset = (int) ($options[Pagination::OPT_PER] / 2.1);
        $start = $current - $offset; // current - edge length - 1 'cuz start on 0
        $end = $current + $offset;

        if ($end > $options[Pagination::OPT_TOTAL]) { // if wanna edge maximum is very last element
            $end = $options[Pagination::OPT_TOTAL];
            $start = $options[Pagination::OPT_TOTAL] - $options[Pagination::OPT_PER] + 1;
        } elseif ($start < 1) { // if wanna edge minimum is very first element
            $start = 1;
            $end = $options[Pagination::OPT_PER];
        }
        $this->start = $start;
        $this->end = $end;
    }

    private function compile(array $params, array $options)
    {
        $params += [
            'type' => $options[Pagination::OPT_TYPE],
            'paramName' => $options[Pagination::OPT_NAME],
        ];

        $list = [];

        for ($current = $this->start; $current <= $this->end; $current++) {
            $list[] = Factory::create($params + [
                    'pageNumber' => $current,
                    'pageName' => $current
                ]);
        }
        $sideControls = $this->createPreviousAndNext($params, $options);
        $edgeControls = $this->createFirstAndLast($params, $options);


        if ($edgeControls['first']->pageNumber < $this->start) {
            array_unshift($list, $edgeControls['first']);
        }
        if ($edgeControls['last']->pageNumber > $this->end) {
            $list[] = $edgeControls['last'];
        }

        array_unshift($list, $sideControls['previous']);
        $list[] = $sideControls['next'];
        foreach ($list as $key => $item) {
            $this->set($key + 1, $item);
        }
    }

    private function createPreviousAndNext(array $params, array $options) : array
    {
        return [
            'previous' => Factory::create($params + [
                    'pageNumber' => max(1, $params['current'] - 1),
                    'pageName' => '&lt;'
                ]),
            'next' => Factory::create($params + [
                    'pageNumber' => min($params['current'] + 1, $options[Pagination::OPT_TOTAL]),
                    'pageName' => '&gt;'
                ])
        ];

    }

    private function createFirstAndLast(array $params, array $options) : array
    {
        return [
            'first' => Factory::create($params + [
                    'pageNumber' => 1,
                    'pageName' => 1
                ]),
            'last' => Factory::create($params + [
                    'pageNumber' => $options[Pagination::OPT_TOTAL],
                    'pageName' => $options[Pagination::OPT_TOTAL]
                ])
        ];
    }
}