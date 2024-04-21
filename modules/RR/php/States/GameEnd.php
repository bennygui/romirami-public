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

namespace RR\State\GameEnd;

require_once(__DIR__ . '/../../../BX/php/Action.php');

trait GameStatesTrait
{
    public function stPreGameEnd()
    {
        $this->preGameEnd();
        $this->gamestate->nextState();
    }

    private function preGameEnd()
    {
        $this->notifyAllPlayers(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('End of game: Awarding Trophies'),
            []
        );

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');

        // Stats
        foreach ($playerMgr->getAll() as $p) {
            $scoreFromContract = $p->playerScore;
            $scoreFromStar = 0;
            if ($playerStateMgr->hasJoker($p->playerId)) {
                $scoreFromStar = 1;
                $scoreFromContract -= 1;
            }
            $this->setStat(
                $scoreFromContract,
                STATS_PLAYER_SCORE_FROM_CONTRACT,
                $p->playerId
            );
            $this->setStat(
                $scoreFromStar,
                STATS_PLAYER_SCORE_FROM_STAR,
                $p->playerId
            );

            $ps = $playerStateMgr->getByPlayerId($p->playerId);
            $this->setStat(
                $ps->statNumberChoose,
                STATS_PLAYER_NB_NUMBER_CHOOSE,
                $p->playerId
            );
            $this->setStat(
                $ps->statNumberDraw,
                STATS_PLAYER_NB_NUMBER_DRAW,
                $p->playerId
            );
            $this->setStat(
                $ps->statNumberDiscard,
                STATS_PLAYER_NB_NUMBER_DISCARD,
                $p->playerId
            );
            $this->setStat(
                $ps->statNumberPlay,
                STATS_PLAYER_NB_NUMBER_PLAY,
                $p->playerId
            );
            $this->setStat(
                $ps->isFirstPlayer ? 1 : 0,
                STATS_PLAYER_IS_FIRST_PLAYER,
                $p->playerId
            );
            $this->setStat(
                count($cardMgr->getContractForPlayer($p->playerId)),
                STATS_PLAYER_NB_CONTRACT,
                $p->playerId
            );
            
            // Will add more to the stats below later
            $this->setStat(
                0,
                STATS_PLAYER_SCORE_FROM_TROPHY,
                $p->playerId
            );
            $this->setStat(
                0,
                STATS_PLAYER_NB_TROPHY,
                $p->playerId
            );
            $this->setStat(
                0,
                STATS_PLAYER_SCORE_FROM_SUIT_BONUS,
                $p->playerId
            );
        }

        $counts = $cardMgr->getPublicCounts(true);
        foreach ($counts->trophyEndGamePlayerId as $trophyId => $playerId) {
            $this->notifyAllPlayers(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} wins trophy: ${trophyName}'),
                [
                    'player_name' => $this->loadPlayersBasicInfos()[$playerId]['player_name'],
                    'trophyName' => $gameStateMgr->getTrophyName($trophyId),
                    'i18n' => ['trophyName'],
                ]
            );
            $trophyScore = $gameStateMgr->getTrophyScore($trophyId);
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \BX\Player\UpdatePlayerScoreActionCommand($playerId, $trophyScore));
            $creator->commit();

            $this->incStat(
                $trophyScore,
                STATS_PLAYER_SCORE_FROM_TROPHY,
                $playerId
            );
            $this->incStat(
                1,
                STATS_PLAYER_NB_TROPHY,
                $playerId
            );
        }

        $this->notifyAllPlayers(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('End of game: Revealing Suit Bonus'),
            []
        );

        foreach ($counts->suitBonusCounts as $playerId => $count) {
            $this->notifyAllPlayers(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} has ${suitBonus} card(s) in their Suit Bonus'),
                [
                    'player_name' => $this->loadPlayersBasicInfos()[$playerId]['player_name'],
                    'suitBonus' => $count,
                ]
            );
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \BX\Player\UpdatePlayerScoreActionCommand($playerId, $count));
            $creator->commit();
            $playerMgr->updatePlayerScoreAuxNow($playerId, $count);
            $this->setStat(
                $count,
                STATS_PLAYER_SCORE_FROM_SUIT_BONUS,
                $playerId
            );
        }

        $this->notifyAllPlayers(
            NTF_UPDATE_PUBLIC_COUNTS,
            clienttranslate('End of game: Final scoring finished'),
            [
                'publicCounts' => $counts,
                'isEndGame' => true,
            ]
        );
    }
}
