<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * theisleofcats implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

namespace RR\Debug;

require_once(__DIR__ . '/../../BX/php/Debug.php');

trait GameStatesTrait
{
    use \BX\Debug\GameStatesTrait;

    public function debugLoadBug()
    {
        $this->debugLoadBugInternal(function ($studioPlayerId, $replacePlayerId) {
            return array_merge(
                $this->debugGetSqlForActionCommand($studioPlayerId, $replacePlayerId),
                [
                    "UPDATE `card` SET player_id = $studioPlayerId WHERE player_id = $replacePlayerId",
                    "UPDATE `player_state` SET player_id = $studioPlayerId WHERE player_id = $replacePlayerId",
                ],
            );
        });
    }

    public function debugPreEndGame()
    {
        $this->preGameEnd();
    }
}
