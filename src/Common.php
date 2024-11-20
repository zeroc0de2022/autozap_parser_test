<?php
declare(strict_types = 1);
/***
 * Date 18.11.2024
 * @author zeroc0de <98693638+zeroc0de2022@users.noreply.github.com>
 */

namespace Parser;

trait Common
{

    /**
     * Print array|object|string in a convenient way
     * @param $any - array|string|object
     * @param bool $exit - exit program - true|false
     * @param bool $usePre - use tag 'pre' in the output - true|false
     */
    public function printPre($any, bool $exit = false, bool $usePre = false): void
    {
        print_r($usePre ? '<pre>' : '');
        print_r($any);
        print_r($usePre ? '</pre>' : '');
        if($exit) {
            exit();
        }
    }


}