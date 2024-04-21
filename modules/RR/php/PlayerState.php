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

class PlayerState extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $playerId;
    /** @dbcol */
    public $jokerUsed;
    /** @dbcol */
    public $isFirstPlayer;
    /** @dbcol */
    public $statNumberChoose;
    /** @dbcol */
    public $statNumberDraw;
    /** @dbcol */
    public $statNumberDiscard;
    /** @dbcol */
    public $statNumberPlay;

    public function __construct()
    {
        $this->playerId = null;
        $this->jokerUsed = false;
        $this->isFirstPlayer = false;
        $this->statNumberChoose = 0;
        $this->statNumberDraw = 0;
        $this->statNumberDiscard = 0;
        $this->statNumberPlay = 0;
    }
}

class PlayerStateMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('player_state', \RR\PlayerState::class);
    }

    public function setup(array $playerIdArray)
    {
        $isFirstPlayer = true;
        foreach ($playerIdArray as $playerId) {
            $ps = $this->db->newRow();
            $ps->playerId = $playerId;
            $ps->isFirstPlayer = $isFirstPlayer;
            $this->db->insertRow($ps);
            $isFirstPlayer = false;
        }
    }

    public function getAll()
    {
        return $this->getAllRowsByKey();
    }

    public function getByPlayerId(int $playerId)
    {
        return $this->getRowByKey($playerId);
    }

    public function hasJoker(int $playerId)
    {
        $ps = $this->getByPlayerId($playerId);
        return !$ps->jokerUsed;
    }

    public function getFirstPlayerId()
    {
        foreach ($this->getAll() as $ps) {
            if ($ps->isFirstPlayer) {
                return $ps->playerId;
            }
        }
        return null;
    }

    public function statAddNumberChooseAction(int $playerId, int $count)
    {
        if ($count < 0) {
            throw new \BgaSystemException("Stat Add: count $count cannot be negative");
        }
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNumberChoose += $count;
    }

    public function statAddNumberDrawAction(int $playerId, int $count)
    {
        if ($count < 0) {
            throw new \BgaSystemException("Stat Add: count $count cannot be negative");
        }
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNumberDraw += $count;
    }

    public function statAddNumberDiscardAction(int $playerId, int $count)
    {
        if ($count < 0) {
            throw new \BgaSystemException("Stat Add: count $count cannot be negative");
        }
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNumberDiscard += $count;
    }

    public function statAddNumberPlayAction(int $playerId, int $count)
    {
        if ($count < 0) {
            throw new \BgaSystemException("Stat Add: count $count cannot be negative");
        }
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNumberPlay += $count;
    }
}
