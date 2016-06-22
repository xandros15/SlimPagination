<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-22
 * Time: 19:47
 */

namespace Xandros15\SlimPagination;


class PageEmpty extends Page implements PageInterface
{

    /**
     * Returning parsed address uri
     *
     * @return string
     */
    public function pathFor() : string
    {
        return '#';
    }

    /**
     * Check if this page is current
     *
     * @return bool
     */
    public function isCurrent() : bool
    {
        return false;
    }

    /**
     * Returning current page number
     *
     * @return string
     */
    public function getPageName() : string
    {
        return $this->pageName;
    }
}