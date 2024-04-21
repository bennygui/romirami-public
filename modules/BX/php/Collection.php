<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * romirami implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

namespace BX\Collection;

function rotateValueToFront(array $array, $value)
{
    $index = array_search($value, $array);
    if ($index === false || $index == 0) {
        return $array;
    }
    $front = array_slice($array, 0, $index);
    $back = array_slice($array, $index);
    return array_values(array_merge($back, $front));
}
