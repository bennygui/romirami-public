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

require_once(__DIR__ . '/../../BX/php/Player.php');

class PlayerMgr extends \BX\Player\PlayerMgr
{
    public function __construct()
    {
        parent::__construct(\BX\Player\Player::class);
    }

    public function setup(array $setupNewGamePlayers, array $colors)
    {
        $colors = parent::setup($setupNewGamePlayers, $colors);
        foreach ($this->getAllRowsByKey() as $p) {
            $p->playerScore = 1;
            $this->db->updateRow($p);
        }
        return $colors;
    }
}
