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

const CARD_SUIT_CHERRY = 1;
const CARD_SUIT_DIAMOND = 2;
const CARD_SUIT_HEART = 3;
const CARD_SUIT_CLOVER = 4;
const CARD_SUITS = [
    CARD_SUIT_CHERRY,
    CARD_SUIT_DIAMOND,
    CARD_SUIT_HEART,
    CARD_SUIT_CLOVER,
];

const CARD_ID_BASE_NUMBER = 1000;
const CARD_ID_BASE_CONTRACT = 2000;
const CARD_ID_JOKER = -1;

class CardDef
{
    public $cardId;
    public $number;
    public $suit;
    public $score;
    public $contracts;
    public $description;

    public function __construct()
    {
        $this->cardId = null;
        $this->number = null;
        $this->suit = null;
        $this->score = null;
        $this->contracts = [];
        $this->description = null;
    }

    public function isNumber()
    {
        return ($this->number !== null);
    }

    public function isContract()
    {
        return ($this->number === null);
    }

    public function isJoker()
    {
        return ($this->cardId == CARD_ID_JOKER);
    }

    public function hasMatchingNumber(CardDef $other)
    {
        if ($this->isJoker() || $other->isJoker()) {
            return true;
        }
        return ($this->number == $other->number);
    }

    public function hasNextNumber(CardDef $other)
    {
        if ($this->isJoker() || $other->isJoker()) {
            return true;
        }
        return ($this->number == $other->number + 1);
    }

    public function hasNextNextNumber(CardDef $other)
    {
        if ($this->isJoker() || $other->isJoker()) {
            return true;
        }
        return ($this->number == $other->number + 2);
    }

    public function hasMatchingSuit(CardDef $other)
    {
        if ($this->isJoker() || $other->isJoker()) {
            return true;
        }
        return ($this->suit == $other->suit);
    }

    public function totalCardsForContracts()
    {
        return array_sum(array_map(fn ($c) => $c->count, $this->contracts));
    }
}

class ContractDef
{
    public $isSequence;
    public $count;
    public $number;

    public function __construct()
    {
        $this->isSequence = false;
        $this->count = null;
        $this->number = null;
    }

    public function hasMatchingNumber(CardDef $other)
    {
        if ($other->isJoker()) {
            return true;
        }
        return ($this->number === null || $this->number == $other->number);
    }

    public function getDescription()
    {
        if ($this->isSequence) {
            switch ($this->count) {
                case 3:
                    return clienttranslate('Sequence of 3');
                case 4:
                    return clienttranslate('Sequence of 4');
                case 5:
                    return clienttranslate('Sequence of 5');
                default:
                    throw new \BgaSystemException("No description for sequence of {$this->count}");
            }
        } else {
            if ($this->number !== null) {
                switch ($this->number) {
                    case 1:
                        return clienttranslate('Pair of 1');
                    case 2:
                        return clienttranslate('Pair of 2');
                    case 3:
                        return clienttranslate('Pair of 3');
                    case 4:
                        return clienttranslate('Pair of 4');
                    case 5:
                        return clienttranslate('Pair of 5');
                    default:
                        throw new \BgaSystemException("No description for pair-number of {$this->number}");
                }
            } else {
                switch ($this->count) {
                    case 2:
                        return clienttranslate('Pair');
                    case 3:
                        return clienttranslate('3 of a Kind');
                    case 4:
                        return clienttranslate('4 of a Kind');
                    case 5:
                        return clienttranslate('5 of a Kind');
                    default:
                        throw new \BgaSystemException("No description for kind of size {$this->count}");
                }
            }
        }
        throw new \BgaSystemException("No description");
    }
}

class NumberCardDefBuilder
{
    private $def;
    private $baseId;

    public function __construct(int $baseId)
    {
        $this->def = new CardDef();
        $this->baseId = $baseId;
    }

    public function number(int $number)
    {
        $this->def->number = $number;
        return $this;
    }

    public function suit(int $suit)
    {
        $this->def->suit = $suit;
        return $this;
    }

    public function build()
    {
        $this->def->cardId =
            CARD_ID_BASE_NUMBER
            + $this->def->number * 100
            + $this->def->suit * 10
            + $this->baseId;
        return $this->def;
    }
}

class ContractCardDefBuilder
{
    private $def;

    public function __construct()
    {
        $this->def = new CardDef();
    }

    public function score(int $score)
    {
        $this->def->score = $score;
        return $this;
    }

    public function cherry()
    {
        $this->def->suit = CARD_SUIT_CHERRY;
        return $this;
    }

    public function diamond()
    {
        $this->def->suit = CARD_SUIT_DIAMOND;
        return $this;
    }

    public function heart()
    {
        $this->def->suit = CARD_SUIT_HEART;
        return $this;
    }

    public function clover()
    {
        $this->def->suit = CARD_SUIT_CLOVER;
        return $this;
    }

    public function contract(ContractDef $contract)
    {
        $this->def->contracts[] = $contract;
        return $this;
    }

    public function build()
    {
        $this->def->description = self::contractListDescription($this->def);
        return $this->def;
    }

    private static function contractListDescription(\RR\CardDef $contractCard)
    {
        $logs = [];
        $args = [
            'completeDesc' => [
                'log' => clienttranslate('To complete this contract and score ${startb}${score}${endb} point(s), you need to discard ${startb}${count}${endb} Number cards matching this:'),
                'args' => [
                    'score' => $contractCard->score,
                    'count' => $contractCard->totalCardsForContracts(),
                    'startb' => '<b>',
                    'endb' => '</b>',
                ],
            ],
            'suitDesc' => clienttranslate('The Number cards do not need to match the Suit, but if they do, you will keep those cards as Suit Bonus and score 1 point for each of those cards. The suit for this contract is:'),
            'startb' => '<b>',
            'endb' => '</b>',
            'suitImage1' => clienttranslate('Cherry'),
            'suitImage2' => clienttranslate('Diamond'),
            'suitImage3' => clienttranslate('Heart'),
            'suitImage4' => clienttranslate('Clover'),
            'i18n' => ['completeDesc', 'suitDesc', 'suitImage1', 'suitImage2', 'suitImage3', 'suitImage4'],
        ];

        foreach ($contractCard->contracts as $i => $c) {
            $logs[] = '${contractDesc' . $i . '}';
            $args['contractDesc' . $i] = $c->getDescription();
            $args['i18n'][] = 'contractDesc' . $i;
        }
        return [
            'log' => '${completeDesc} ${startb}' . implode(', ', $logs) . '${endb}. ${suitDesc} ${suitImage' . $contractCard->suit . '}',
            'args' => $args,
        ];
    }
}

class ContractDefBuilder
{
    private $def;

    public function __construct()
    {
        $this->def = new ContractDef();
    }

    public function count(int $count)
    {
        $this->def->count = $count;
        return $this;
    }

    public function kind(?int $number = null)
    {
        $this->def->isSequence = false;
        $this->def->number = $number;
        return $this;
    }

    public function sequence()
    {
        $this->def->isSequence = true;
        return $this;
    }

    public function build()
    {
        return $this->def;
    }
}
