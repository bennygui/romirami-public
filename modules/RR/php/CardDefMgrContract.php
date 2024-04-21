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

trait CardDefMgrContract
{
    private static function getCardDefContract()
    {
        return [
            // Row: 1
            (new ContractCardDefBuilder())
                ->score(2)
                ->heart()
                ->contract((new ContractDefBuilder())->count(4)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(2)
                ->clover()
                ->contract((new ContractDefBuilder())->count(4)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(2)
                ->diamond()
                ->contract((new ContractDefBuilder())->count(4)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(4)
                ->cherry()
                ->contract((new ContractDefBuilder())->count(5)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(4)
                ->heart()
                ->contract((new ContractDefBuilder())->count(5)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(4)
                ->clover()
                ->contract((new ContractDefBuilder())->count(5)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(4)
                ->cherry()
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(4)
                ->clover()
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->build(),
            // Row: 2
            (new ContractCardDefBuilder())
                ->score(5)
                ->cherry()
                ->contract((new ContractDefBuilder())->count(2)->kind(1)->build())
                ->contract((new ContractDefBuilder())->count(4)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(5)
                ->clover()
                ->contract((new ContractDefBuilder())->count(2)->kind(5)->build())
                ->contract((new ContractDefBuilder())->count(4)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(1)
                ->diamond()
                ->contract((new ContractDefBuilder())->count(2)->kind(2)->build())
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(1)
                ->heart()
                ->contract((new ContractDefBuilder())->count(2)->kind(4)->build())
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(5)
                ->heart()
                ->contract((new ContractDefBuilder())->count(2)->kind(1)->build())
                ->contract((new ContractDefBuilder())->count(4)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(5)
                ->cherry()
                ->contract((new ContractDefBuilder())->count(2)->kind(5)->build())
                ->contract((new ContractDefBuilder())->count(4)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(4)
                ->heart()
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->contract((new ContractDefBuilder())->count(3)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(4)
                ->clover()
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->contract((new ContractDefBuilder())->count(3)->sequence()->build())
                ->build(),
            // Row: 3
            (new ContractCardDefBuilder())
                ->score(4)
                ->diamond()
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->contract((new ContractDefBuilder())->count(3)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(4)
                ->cherry()
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(4)
                ->heart()
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(2)
                ->diamond()
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(2)
                ->cherry()
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(2)
                ->clover()
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(1)
                ->cherry()
                ->contract((new ContractDefBuilder())->count(4)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(1)
                ->diamond()
                ->contract((new ContractDefBuilder())->count(4)->sequence()->build())
                ->build(),
            // Row: 4
            (new ContractCardDefBuilder())
                ->score(1)
                ->clover()
                ->contract((new ContractDefBuilder())->count(4)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(3)
                ->diamond()
                ->contract((new ContractDefBuilder())->count(5)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(3)
                ->heart()
                ->contract((new ContractDefBuilder())->count(5)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(3)
                ->cherry()
                ->contract((new ContractDefBuilder())->count(5)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(6)
                ->clover()
                ->contract((new ContractDefBuilder())->count(3)->sequence()->build())
                ->contract((new ContractDefBuilder())->count(4)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(6)
                ->heart()
                ->contract((new ContractDefBuilder())->count(3)->sequence()->build())
                ->contract((new ContractDefBuilder())->count(4)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(6)
                ->diamond()
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->contract((new ContractDefBuilder())->count(4)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(6)
                ->cherry()
                ->contract((new ContractDefBuilder())->count(3)->kind()->build())
                ->contract((new ContractDefBuilder())->count(4)->sequence()->build())
                ->build(),
            // Row: 5
            (new ContractCardDefBuilder())
                ->score(6)
                ->diamond()
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->contract((new ContractDefBuilder())->count(5)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(6)
                ->clover()
                ->contract((new ContractDefBuilder())->count(2)->kind()->build())
                ->contract((new ContractDefBuilder())->count(5)->sequence()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(9)
                ->heart()
                ->contract((new ContractDefBuilder())->count(3)->sequence()->build())
                ->contract((new ContractDefBuilder())->count(5)->kind()->build())
                ->build(),
            (new ContractCardDefBuilder())
                ->score(9)
                ->diamond()
                ->contract((new ContractDefBuilder())->count(3)->sequence()->build())
                ->contract((new ContractDefBuilder())->count(5)->kind()->build())
                ->build(),
        ];
    }
}
