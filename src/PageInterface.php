<?php
/**
 * Created by PhpStorm.
 * User: xandros15
 * Date: 2016-06-19
 * Time: 16:50
 */

namespace Xandros15\SlimPagination;


interface PageInterface
{
    /**
     * Returning parsed address uri
     *
     * @return string
     */
    public function pathFor() : string;

    /**
     * Check if this page is current
     *
     * @return bool
     */
    public function isCurrent() : bool;

    /**
     * Returning current page number
     *
     * @return string
     */
    public function getPageName() : string;
}