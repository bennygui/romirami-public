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

namespace RR;

require_once('CardDef.php');

const SETUP_CARD_NUMBER_COUNTS = [
    1 => 4,
    2 => 5,
    3 => 5,
    4 => 5,
    5 => 4,
];

trait CardDefMgrNumber
{
    private static function getCardDefNumber()
    {
        $ret = [];
        foreach (SETUP_CARD_NUMBER_COUNTS as $number => $count) {
            foreach (CARD_SUITS as $suit) {
                for ($i = 0; $i < $count; ++$i) {
                    $ret[] = (new NumberCardDefBuilder($i))
                        ->number($number)
                        ->suit($suit)
                        ->build();
                }
            }
        }
        return $ret;
    }
}
