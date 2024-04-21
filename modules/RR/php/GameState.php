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

const GAME_STATE_ID = 0;

class GameState extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $gameStateId;
    /** @dbcol */
    public $isLastRound;
    /** @dbcol */
    public $trophyTop0;
    /** @dbcol */
    public $trophyTop1;
    /** @dbcol */
    public $trophyTop2;
    /** @dbcol */
    public $trophyTop3;

    public function __construct()
    {
        $this->gameStateId = GAME_STATE_ID;
        $this->isLastRound = false;
        $this->trophyTop0 = true;
        $this->trophyTop1 = true;
        $this->trophyTop2 = true;
        $this->trophyTop3 = true;
    }
}

class GameStateMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('game_state', \RR\GameState::class);
    }

    public function setup()
    {
        $gs = $this->db->newRow();
        $gs->trophyTop0 = (\bga_rand(0, 1) == 0);
        $gs->trophyTop1 = (\bga_rand(0, 1) == 0);
        $gs->trophyTop2 = (\bga_rand(0, 1) == 0);
        $gs->trophyTop3 = (\bga_rand(0, 1) == 0);
        $this->db->insertRow($gs);
    }

    public function get()
    {
        return $this->getRowByKey(GAME_STATE_ID);
    }

    public function getTrophyName(int $trophyId)
    {
        $gs = $this->get();
        switch ($trophyId) {
            case 0:
                if ($gs->trophyTop0) {
                    return clienttranslate('Cherries');
                } else {
                    return clienttranslate('2-Card Combos');
                }
            case 1:
                if ($gs->trophyTop1) {
                    return clienttranslate('Hearts');
                } else {
                    return clienttranslate('3-Card Combos');
                }
            case 2:
                if ($gs->trophyTop2) {
                    return clienttranslate('Clovers');
                } else {
                    return clienttranslate('4-Card Combos');
                }
            case 3:
                if ($gs->trophyTop3) {
                    return clienttranslate('Diamonds');
                } else {
                    return clienttranslate('5-Card Combos');
                }
        }
        throw new \BgaSystemException("Unknow trophyId $trophyId");
    }

    public function getTrophyScore(int $trophyId)
    {
        $gs = $this->get();
        switch ($trophyId) {
            case 0:
                if ($gs->trophyTop0) {
                    return 3;
                } else {
                    return 5;
                }
            case 1:
                if ($gs->trophyTop1) {
                    return 3;
                } else {
                    return 4;
                }
            case 2:
                if ($gs->trophyTop2) {
                    return 3;
                } else {
                    return 3;
                }
            case 3:
                if ($gs->trophyTop3) {
                    return 3;
                } else {
                    return 2;
                }
        }
        throw new \BgaSystemException("Unknow trophyId $trophyId");
    }
}
