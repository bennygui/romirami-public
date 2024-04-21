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

require_once(__DIR__ . '/../../BX/php/Action.php');
require_once('CardDefMgr.php');

const CARD_LOCATION_ID_DECK = 1;
const CARD_LOCATION_ID_MARKET = 2;
const CARD_LOCATION_ID_HAND = 3;
const CARD_LOCATION_ID_DISCARD = 4;
const CARD_LOCATION_ID_PLAYER = 5;

class Card extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $cardId;
    /** @dbcol */
    public $playerId;
    /** @dbcol */
    public $locationId;
    /** @dbcol */
    public $locationOrder;

    public function __construct()
    {
        $this->cardId = null;
        $this->playerId = null;
        $this->locationId = null;
        $this->locationOrder = null;
    }

    public function def()
    {
        return CardDefMgr::getByCardId($this->cardId);
    }

    public function isVisibleForPlayer(int $playerId)
    {
        if ($this->locationId === null)
            throw new \BgaSystemException("BUG! locationId is null");
        switch ($this->locationId) {
            case CARD_LOCATION_ID_DECK:
                return false;
            case CARD_LOCATION_ID_MARKET:
                return true;
            case CARD_LOCATION_ID_HAND:
                if ($this->playerId == $playerId) {
                    return true;
                }
                return false;
            case CARD_LOCATION_ID_DISCARD:
                return false;
            case CARD_LOCATION_ID_PLAYER:
                if ($this->def()->isContract()) {
                    return true;
                }
                return false;
            default:
                throw new \BgaSystemException("BUG! Unknown locationId: {$this->locationId}");
        }
    }

    public function isInNumberDeck()
    {
        return ($this->locationId == CARD_LOCATION_ID_DECK
            && $this->def()->isNumber()
        );
    }

    public function isInContractDeck()
    {
        return ($this->locationId == CARD_LOCATION_ID_DECK
            && $this->def()->isContract()
        );
    }

    public function isInNumberDiscard()
    {
        return ($this->locationId == CARD_LOCATION_ID_DISCARD
            && $this->def()->isNumber()
        );
    }

    public function isInPlayerHand(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == CARD_LOCATION_ID_HAND
            && $this->def()->isNumber()
        );
    }

    public function isInNumberMarket()
    {
        return ($this->locationId == CARD_LOCATION_ID_MARKET
            && $this->def()->isNumber()
        );
    }

    public function isInContractMarket()
    {
        return ($this->locationId == CARD_LOCATION_ID_MARKET
            && $this->def()->isContract()
        );
    }

    public function isInPlayerContract(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == CARD_LOCATION_ID_PLAYER
            && $this->def()->isContract()
        );
    }

    public function moveToPlayerHand(int $playerId)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $order = $cardMgr->getPlayerNextHandOrder($playerId);
        $this->playerId = $playerId;
        $this->locationId = CARD_LOCATION_ID_HAND;
        $this->locationOrder = $order;
    }

    public function moveToMarket(int $order)
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_ID_MARKET;
        $this->locationOrder = $order;
    }

    public function moveToPlayer(int $playerId)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $order = $cardMgr->getPlayerNextPlayerContractOrder($playerId);
        $this->playerId = $playerId;
        $this->locationId = CARD_LOCATION_ID_PLAYER;
        if ($this->def()->isNumber()) {
            $this->locationOrder = 0;
        } else {
            $this->locationOrder = $order;
        }
    }

    public function moveToDiscard()
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_ID_DISCARD;
        $this->locationOrder = 0;
    }

    public function moveToDeck(int $order)
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_ID_DECK;
        $this->locationOrder = $order;
    }
}

class PublicCountsUI extends \BX\UI\UISerializable
{
    public $deckNumberCount;
    public $handCounts;
    public $contractCounts;
    public $suitBonusCounts;
    public $trophySuitCounts;
    public $trophyKindCounts;
    public $trophyEndGamePlayerId;

    public function __construct(array $playerIdArray)
    {
        $this->deckNumberCount = 0;
        $this->handCounts = [];
        $this->contractCounts = [];
        $this->suitBonusCounts = [];
        $this->trophySuitCounts = [];
        $this->trophyKindCounts = [];
        $this->trophyEndGamePlayerId = [];
        foreach ($playerIdArray as $playerId) {
            $this->handCounts[$playerId] = 0;
            $this->contractCounts[$playerId] = 0;
            $this->suitBonusCounts[$playerId] = 0;
            $this->trophySuitCounts[$playerId] = [];
            foreach (CARD_SUITS as $suit) {
                $this->trophySuitCounts[$playerId][$suit] = 0;
            }
            $this->trophyKindCounts[$playerId] = [];
            foreach (CONTRACT_KIND_COUNTS as $type) {
                $this->trophyKindCounts[$playerId][$type] = 0;
            }
        }
    }

    public function addCard(Card $c)
    {
        if ($c->playerId === null) {
            switch ($c->locationId) {
                case CARD_LOCATION_ID_DECK:
                    if ($c->def()->isNumber())
                        $this->deckNumberCount += 1;
                    break;
            }
            return;
        }
        switch ($c->locationId) {
            case CARD_LOCATION_ID_DECK:
            case CARD_LOCATION_ID_MARKET:
            case CARD_LOCATION_ID_DISCARD:
                break;
            case CARD_LOCATION_ID_HAND:
                $this->handCounts[$c->playerId] += 1;
                break;
            case CARD_LOCATION_ID_PLAYER:
                if ($c->def()->isNumber()) {
                    $this->suitBonusCounts[$c->playerId] = null;
                } else {
                    $this->contractCounts[$c->playerId] += 1;
                    $this->trophySuitCounts[$c->playerId][$c->def()->suit] += 1;
                    foreach ($c->def()->contracts as $contract) {
                        $this->trophyKindCounts[$c->playerId][$contract->count] += 1;
                    }
                }
                break;
        }
    }

    public function setEndGame()
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');

        $gs = $gameStateMgr->get();
        $trophyCounts = [];
        foreach ($this->trophySuitCounts as $playerId => $counts) {
            foreach ($counts as $suit => $count) {
                $trophyId = SUIT_TO_TROPHY[$suit];
                $trophyName = 'trophyTop' . $trophyId;
                if ($gs->$trophyName) {
                    if (!array_key_exists($trophyId, $trophyCounts)) {
                        $trophyCounts[$trophyId] = [];
                    }
                    $trophyCounts[$trophyId][$playerId] = $count;
                }
            }
        }
        foreach ($this->trophyKindCounts as $playerId => $counts) {
            foreach ($counts as $kind => $count) {
                $trophyId = KIND_TO_TROPHY[$kind];
                $trophyName = 'trophyTop' . $trophyId;
                if (!$gs->$trophyName) {
                    if (!array_key_exists($trophyId, $trophyCounts)) {
                        $trophyCounts[$trophyId] = [];
                    }
                    $trophyCounts[$trophyId][$playerId] = $count;
                }
            }
        }

        foreach ($trophyCounts as $trophyId => $counts) {
            $maxPlayerIds = array_keys($counts, max($counts));
            if (count($maxPlayerIds) == 1) {
                $this->trophyEndGamePlayerId[$trophyId] = $maxPlayerIds[0];
            }
        }

        foreach (array_keys($this->suitBonusCounts) as $playerId) {
            $this->suitBonusCounts[$playerId] = $cardMgr->getPrivateCounts($playerId)->suitBonus;
        }
    }
}

class PrivateCountsUI extends \BX\UI\UISerializable
{
    public $playerId;
    public $suitBonus;

    public function __construct(int $playerId)
    {
        $this->playerId = $playerId;
        $this->suitBonus = 0;
    }

    public function addCard(Card $c)
    {
        if ($c->playerId === null || $c->playerId != $this->playerId) {
            return;
        }
        switch ($c->locationId) {
            case CARD_LOCATION_ID_DECK:
            case CARD_LOCATION_ID_MARKET:
            case CARD_LOCATION_ID_DISCARD:
            case CARD_LOCATION_ID_HAND:
                break;
            case CARD_LOCATION_ID_PLAYER:
                if ($c->def()->isNumber()) {
                    $this->suitBonus += 1;
                }
                break;
        }
    }
}

class CardMgr extends \BX\Action\BaseActionRowMgr
{
    private const SETUP_CARDS_PER_PLAYER = 3;

    public function __construct()
    {
        parent::__construct('card', \RR\Card::class);
    }

    public function setup(array $playerIdArray)
    {
        $this->setupCards();
        $this->setupPlayerCards($playerIdArray);
        $this->setupNumberMarket();
        $this->setupContractMarket();
    }

    private function setupCards()
    {
        $cards = CardDefMgr::getAll();
        shuffle($cards);
        foreach ($cards as $i => $def) {
            $c = $this->db->newRow();
            $c->cardId = $def->cardId;
            $c->playerId = null;
            $c->locationId = CARD_LOCATION_ID_DECK;
            $c->locationOrder = $i;
            $this->db->insertRow($c);
        }
    }

    private function setupPlayerCards(array $playerIdArray)
    {
        foreach ($playerIdArray as $playerId) {
            for ($i = 0; $i < self::SETUP_CARDS_PER_PLAYER; ++$i) {
                $card = $this->getTopNumberCardFromDeck();
                $card->moveToPlayerHand($playerId);
                $this->db->updateRow($card);
            }
        }
    }

    private function setupNumberMarket()
    {
        for ($i = 0; $i < MARKET_SIZE_NUMBER; ++$i) {
            $card = $this->getTopNumberCardFromDeck();
            $card->moveToMarket($i);
            $this->db->updateRow($card);
        }
    }

    private function setupContractMarket()
    {
        for ($i = 0; $i < MARKET_SIZE_CONTRACT; ++$i) {
            $card = $this->getTopContractCardFromDeck();
            $card->moveToMarket($i);
            $this->db->updateRow($card);
        }
    }

    public function getById(int $cardId)
    {
        return $this->getRowByKey($cardId);
    }

    public function getAll()
    {
        return $this->getAllRowsByKey();
    }

    public function getAllVisibleForPlayer(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isVisibleForPlayer($playerId));
    }

    public function getTopNumberCardFromDeck()
    {
        $topCard = null;
        foreach ($this->getAll() as $c) {
            if (!$c->isInNumberDeck()) {
                continue;
            }
            if ($topCard === null || $c->locationOrder < $topCard->locationOrder) {
                $topCard = $c;
            }
        }
        return $topCard;
    }

    public function getTopContractCardFromDeck()
    {
        $topCard = null;
        foreach ($this->getAll() as $c) {
            if (!$c->isInContractDeck()) {
                continue;
            }
            if ($topCard === null || $c->locationOrder < $topCard->locationOrder) {
                $topCard = $c;
            }
        }
        return $topCard;
    }

    public function shuffleDeckAndDiscardAction()
    {
        $cards = array_filter($this->getAll(), fn ($c) => $c->isInNumberDeck() || $c->isInNumberDiscard());
        shuffle($cards);
        foreach ($cards as $i => $c) {
            $c->modifyAction();
            $c->moveToDeck($i);
        }
    }

    public function getCardsInPlayerHand(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerHand($playerId));
    }

    public function getPlayerNextHandOrder(int $playerId)
    {
        $max = -1;
        foreach ($this->getCardsInPlayerHand($playerId) as $c) {
            if ($c->locationOrder > $max) {
                $max = $c->locationOrder;
            }
        }
        return ($max + 1);
    }

    public function getContractForPlayer(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerContract($playerId));
    }

    public function getPlayerNextPlayerContractOrder(int $playerId)
    {
        $max = -1;
        foreach ($this->getContractForPlayer($playerId) as $c) {
            if ($c->locationOrder > $max) {
                $max = $c->locationOrder;
            }
        }
        return ($max + 1);
    }

    public function getNumberCardInMarket()
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInNumberMarket());
    }

    public function getContractCardInMarket()
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInContractMarket());
    }

    public function getPublicCounts(bool $isEndGame = false)
    {
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $counts = new PublicCountsUI($playerMgr->getAllPlayerIds());
        foreach ($this->getAll() as $c) {
            $counts->addCard($c);
        }
        if ($isEndGame) {
            $counts->setEndGame();
        }
        return $counts;
    }

    public function getPrivateCounts(int $playerId)
    {
        $counts = new PrivateCountsUI($playerId);
        foreach ($this->getAll() as $c) {
            $counts->addCard($c);
        }
        return $counts;
    }

    public function detectLastRound()
    {
        $counts = $this->getPublicCounts();

        $endGameCount = $this->getContractEndGameCount($counts);
        foreach ($counts->contractCounts as $c) {
            if ($c >= $endGameCount) {
                return true;
            }
        }
        return false;
    }

    public function getGameProgression()
    {
        $counts = $this->getPublicCounts();

        $endGameCount = $this->getContractEndGameCount($counts);
        $max = max($counts->contractCounts);
        if ($max >= $endGameCount) {
            return 100;
        }
        return (100 * $max / $endGameCount);
    }

    private function getContractEndGameCount(PublicCountsUI $counts)
    {
        switch (count($counts->contractCounts)) {
            case 2:
                return 7;
            case 3:
                return 6;
        }
        return 5;
    }
}
