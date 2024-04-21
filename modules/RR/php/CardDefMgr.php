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

require_once('Globals.php');
require_once('CardDef.php');
require_once('CardDefMgrNumber.php');
require_once('CardDefMgrContract.php');

class CardDefMgr
{
    public static function getAll()
    {
        self::initCardDefs();
        return self::$cardDefs;
    }

    public static function getAllNumber()
    {
        self::initCardDefs();
        return array_filter(self::$cardDefs, fn ($cd) => $cd->isNumber());
    }

    public static function getAllContract()
    {
        self::initCardDefs();
        return array_filter(self::$cardDefs, fn ($cd) => $cd->isContract());
    }

    public static function getByCardId(int $cardId)
    {
        self::initCardDefs();
        if (!array_key_exists($cardId, self::$cardDefs)) {
            return null;
        }
        return self::$cardDefs[$cardId];
    }

    public static function getCardJoker()
    {
        self::initCardDefs();
        return self::$cardJoker;
    }

    public static function isNumberChoiceFromMarketValid(array $numberCards)
    {
        $count = count($numberCards);
        if ($count <= 0 || $count > MARKET_MAX_NUMBER_CHOICE) {
            return false;
        }
        if ($count == 1) {
            return true;
        }
        $first = array_shift($numberCards);

        $matchNumber = true;
        $matchSuit = true;
        foreach ($numberCards as $card) {
            if (!$first->hasMatchingNumber($card)) {
                $matchNumber = false;
            }
            if (!$first->hasMatchingSuit($card)) {
                $matchSuit = false;
            }
        }
        return ($matchNumber || $matchSuit);
    }

    public static function isContractFilled(CardDef $contractCard, array $numberCards, bool $useJoker)
    {
        if (
            !$useJoker
            && $contractCard->totalCardsForContracts() == count($numberCards)
            && self::isContractFilledImpl($contractCard->contracts, 0, $numberCards, [])
        ) {
            return true;
        }
        if (
            $useJoker
            && $contractCard->totalCardsForContracts() == count($numberCards) + 1
            && self::isContractFilledImpl($contractCard->contracts, 0, array_merge($numberCards, [self::getCardJoker()]), [])
        ) {
            return true;
        }
        return false;
    }

    private static function isContractFilledImpl(array $contracts, int $contractIndex, array $numberCards, array $selectedCards)
    {
        if ($contractIndex >= count($contracts)) {
            return true;
        }
        $contract = $contracts[$contractIndex];
        if (count($selectedCards) == $contract->count) {
            return self::isContractFilledImpl($contracts, $contractIndex + 1, $numberCards, []);
        }
        foreach ($numberCards as $i => $card) {
            if (!$contract->hasMatchingNumber($card)) {
                continue;
            }
            if (count($selectedCards) >= 1) {
                $lastCard = $selectedCards[count($selectedCards) - 1];
                if ($contract->isSequence) {
                    if (!$card->hasNextNumber($lastCard)) {
                        continue;
                    }
                } else {
                    if (!$card->hasMatchingNumber($lastCard)) {
                        continue;
                    }
                }
            }
            if (count($selectedCards) >= 2) {
                $lastLastCard = $selectedCards[count($selectedCards) - 2];
                if ($contract->isSequence) {
                    if (!$card->hasNextNextNumber($lastLastCard)) {
                        continue;
                    }
                } else {
                    if (!$card->hasMatchingNumber($lastLastCard)) {
                        continue;
                    }
                }
            }
            $remainingCards = $numberCards;
            unset($remainingCards[$i]);
            if (self::isContractFilledImpl($contracts, $contractIndex, $remainingCards, array_merge($selectedCards, [$card]))) {
                return true;
            }
        }
        return false;
    }

    use \RR\CardDefMgrNumber;
    use \RR\CardDefMgrContract;

    private static $cardDefs;
    private static $cardJoker;

    private static function initCardDefs()
    {
        if (self::$cardDefs != null) {
            return;
        }
        self::$cardJoker = new CardDef();
        self::$cardJoker->cardId = CARD_ID_JOKER;
        self::$cardDefs = [];
        foreach (self::getCardDefNumber() as $cardDef) {
            self::$cardDefs[$cardDef->cardId] = $cardDef;
        }
        foreach (self::getCardDefContract() as $i => $cardDef) {
            $cardDef->cardId = CARD_ID_BASE_CONTRACT + $i + 1;
            self::$cardDefs[$cardDef->cardId] = $cardDef;
        }
    }
}
