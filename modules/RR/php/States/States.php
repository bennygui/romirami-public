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

namespace RR\State\States;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../Actions/Actions.php');

trait GameStatesTrait
{
    public function stPreChooseNumberFromMarket()
    {
        $playerId = $this->getActivePlayerId();
        $this->giveExtraTime($playerId);

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        if (count($cardMgr->getNumberCardInMarket()) > 0) {
            $this->gamestate->nextState('hasNumber');
        } else {
            $this->gamestate->nextState('noNumber');
        }
    }

    public function argChooseNumberFromMarket()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
                return [
                    'cardIds' => array_keys($cardMgr->getNumberCardInMarket()),
                ];
            }
        );
    }

    public function numberChoose(array $cardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("numberChoose");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \RR\Actions\Actions\NumberChoose($playerId, $cardIds));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId));
        $creator->save();
    }

    public function stPreChooseContractFromMarket()
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        if (count($cardMgr->getContractCardInMarket()) > 0) {
            $this->gamestate->nextState('hasContract');
        } else {
            $this->gamestate->nextState('noContract');
        }
    }

    public function argChooseContractFromMarket()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
                $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
                return [
                    'handCardIds' => array_keys($cardMgr->getCardsInPlayerHand($playerId)),
                    'contractCardIds' => array_keys($cardMgr->getContractCardInMarket()),
                    'hasJoker' => $playerStateMgr->hasJoker($playerId),
                ];
            }
        );
    }

    public function contractChoose(int $contractCardId, array $cardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("contractChoose");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \RR\Actions\Actions\ContractChoose($playerId, $contractCardId, $cardIds));
        $creator->add(new \RR\Actions\Actions\UpdateLastRound($playerId));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'chooseContract'));
        $creator->save();
    }

    public function contractPass()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("contractPass");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \BX\Action\SendMessage($playerId, clienttranslate('${player_name} passes')));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'choosePass'));
        $creator->save();
    }

    public function stRefillMarket()
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $playerId = $this->getActivePlayerId();
        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \RR\Actions\Actions\RefillMarket($playerId));
        if (count($cardMgr->getCardsInPlayerHand($playerId)) > MAX_HAND_SIZE) {
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'mustDiscard'));
        } else {
            $creator->add(new \RR\Actions\Actions\RefillHand($playerId));
            $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId, 'endTurn'));
        }
        $creator->commit();
    }

    public function argChooseNumberToDiscard()
    {
        $playerId = $this->getActivePlayerId();
        return \BX\ActiveState\argsActive(
            $playerId,
            function ($playerId) {
                $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
                return [
                    'handCardIds' => array_keys($cardMgr->getCardsInPlayerHand($playerId)),
                    'nbCards' => count($cardMgr->getCardsInPlayerHand($playerId)) - MAX_HAND_SIZE,
                ];
            }
        );
    }

    public function numberDiscard(array $cardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction("numberDiscard");
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \RR\Actions\Actions\NumberDiscard($playerId, $cardIds));
        $creator->add(new \RR\Actions\Actions\RefillMarket($playerId));
        $creator->add(new \BX\ActiveState\NextStateActionCommand($playerId));
        $creator->commit();
    }

    public function stEndOfTurn()
    {
        $this->activeNextPlayer();
        $playerId = $this->getActivePlayerId();

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');

        if ($gameStateMgr->get()->isLastRound && $playerId == $playerStateMgr->getFirstPlayerId()) {
            // Normal game end
            $this->gamestate->nextState('endGame');
        } else if (
            count($cardMgr->getContractCardInMarket()) == 0
            || count($cardMgr->getNumberCardInMarket()) == 0
        ) {
            // Empty market, end the game
            $this->notifyAllPlayers(
                \BX\Action\NTF_MESSAGE,
                'The market cannot be filled, ending the game',
                []
            );
            $this->gamestate->nextState('endGame');
        } else {
            // Not the end of the game, next player
            if ($playerId == $playerStateMgr->getFirstPlayerId()) {
                $this->incStat(1, STATS_TABLE_NB_ROUND);
            }
            $this->gamestate->nextState('nextPlayer');
        }
    }
}
