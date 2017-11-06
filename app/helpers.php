<?php
/**
 * Created by PhpStorm.
 * User: SeungUk
 * Date: 2017. 11. 1.
 * Time: 오후 1:41
 */

function markdown($text) {
    return (new Parsedown)->text($text);
}