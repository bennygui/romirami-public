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

namespace RR\Actions\Actions;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../../../BX/php/LastRound.php');
require_once(__DIR__ . '/../../../BX/php/Collection.php');
require_once(__DIR__ . '/../CardDefMgr.php');
require_once(__DIR__ . '/Traits.php');

class NumberChoose extends \BX\Action\BaseActionCommand
{
    use \RR\Actions\Traits\CardNotificationTrait;

    private $cardIds;
    private $undoCards;

    public function __construct(int $playerId, array $cardIds)
    {
        parent::__construct($playerId);
        $this->cardIds = $cardIds;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $cards = [];
        foreach ($this->cardIds as $id) {
            $card = $cardMgr->getById($id);
            if ($card === null) {
                throw new \BgaSystemException("Unknown cardId $id");
            }
            if (!$card->isInNumberMarket()) {
                throw new \BgaSystemException("cardId $id is not in the number market");
            }
            $cards[] = $card;
        }

        if (!\RR\CardDefMgr::isNumberChoiceFromMarketValid(array_map(fn ($c) => $c->def(), $cards))) {
            throw new \BgaSystemException("No a valid market choice: " . implode(',', array_map(fn ($c) => $c->cardId, $cards)));
        }

        $this->saveUndoCounts();
        $this->undoCards = \BX\Meta\deepClone($cards);

        foreach ($cards as $card) {
            $card->modifyAction();
            $card->moveToPlayerHand($this->playerId);
        }

        $playerStateMgr = self::getMgr('player_state');
        $playerStateMgr->statAddNumberChooseAction($this->playerId, count($cards));

        $notifier->notify(
            NTF_UPDATE_CARDS,
            clienttranslate('${player_name} takes ${cardList} from market'),
            [
                'cards' => $cards,
                'cardList' => self::cardListNotification($cards),
            ]
        );

        $this->notifyUpdateCounts($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARDS,
            [
                'cards' => $this->undoCards,
            ]
        );
        $this->notifyUndoCounts($notifier);
    }
}

class ContractChoose extends \BX\Action\BaseActionCommand
{
    use \RR\Actions\Traits\CardNotificationTrait;

    private $contractCardId;
    private $cardIds;
    private $undoContractCard;
    private $undoBonusCards;
    private $undoDiscardCards;
    private $undoPlayerState;
    private $contractScoreAction;
    private $starScoreAction;

    public function __construct(int $playerId, int $contractCardId, array $cardIds)
    {
        parent::__construct($playerId);
        $this->contractCardId = $contractCardId;
        $this->cardIds = $cardIds;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $playerStateMgr = self::getMgr('player_state');

        $contractCard = $cardMgr->getById($this->contractCardId);
        if ($contractCard === null) {
            throw new \BgaSystemException("Unknown cardId {$this->contractCardId}");
        }
        if (!$contractCard->isInContractMarket()) {
            throw new \BgaSystemException("cardId {$this->contractCardId} is not in the contract market");
        }

        $bonusCards = [];
        $discardCards = [];
        foreach ($this->cardIds as $id) {
            $card = $cardMgr->getById($id);
            if ($card === null) {
                throw new \BgaSystemException("Unknown cardId $id");
            }
            if (!$card->isInPlayerHand($this->playerId)) {
                throw new \BgaSystemException("cardId $id is not playerId {$this->playerId} hand");
            }
            if ($card->def()->suit == $contractCard->def()->suit) {
                $bonusCards[] = $card;
            } else {
                $discardCards[] = $card;
            }
        }

        $cardDefs = array_map(fn ($c) => $c->def(), array_merge($bonusCards, $discardCards));
        $isValid = \RR\CardDefMgr::isContractFilled($contractCard->def(), $cardDefs, false);
        $useJoker = false;
        if (!$isValid && $playerStateMgr->hasJoker($this->playerId)) {
            $isValid = \RR\CardDefMgr::isContractFilled($contractCard->def(), $cardDefs, true);
            $useJoker = true;
        }
        if (!$isValid) {
            throw new \BgaSystemException("No a valid contract {$this->contractCardId} for cards: " . implode(',', array_map(fn ($c) => $c->cardId, $cardDefs)));
        }

        $ps = $playerStateMgr->getByPlayerId($this->playerId);

        $this->saveUndoCounts();
        $this->undoContractCard = \BX\Meta\deepClone($contractCard);
        $this->undoBonusCards = \BX\Meta\deepClone($bonusCards);
        $this->undoDiscardCards = \BX\Meta\deepClone($discardCards);
        $this->undoPlayerState = \BX\Meta\deepClone($ps);

        $contractCard->modifyAction();
        $contractCard->moveToPlayer($this->playerId);
        $notifier->notify(
            NTF_UPDATE_CARDS,
            clienttranslate('${player_name} completes contract ${contractList} with ${cardList} and keeps ${bonusCardCount} Suit Bonus card(s) ${cardImage}'),
            [
                'cards' => [$contractCard],
                'cardImage' => $this->contractCardId,
                'contractList' => self::contractListNotification($contractCard->def()),
                'cardList' => self::cardListNotification(array_merge($bonusCards, $discardCards), $useJoker),
                'bonusCardCount' => count($bonusCards),
            ]
        );
        $playerStateMgr->statAddNumberPlayAction($this->playerId, count($bonusCards) + count($discardCards));
        if ($useJoker) {
            $ps->modifyAction();
            $ps->jokerUsed = true;
            $notifier->notifyNoMessage(
                NTF_UPDATE_JOKER,
                [
                    'used' => $ps->jokerUsed,
                ]
            );
            $this->starScoreAction = new \BX\Player\UpdatePlayerScoreActionCommand($this->playerId);
            $this->starScoreAction->do($notifier, -1, clienttranslate('${player_name} uses their star and loses 1 point'));
        }
        foreach ($bonusCards as $card) {
            $card->modifyAction();
            $card->moveToPlayer($this->playerId);
        }
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_CARDS,
            [
                'cards' => $bonusCards,
            ]
        );

        foreach ($discardCards as $card) {
            $card->modifyAction();
            $card->moveToDiscard();
        }
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_CARDS,
            [
                'cards' => $discardCards,
            ]
        );

        $this->contractScoreAction = new \BX\Player\UpdatePlayerScoreActionCommand($this->playerId);
        $this->contractScoreAction->do($notifier, $contractCard->def()->score);

        $this->notifyUpdateCounts($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->contractScoreAction !== null) {
            $this->contractScoreAction->undo($notifier);
        }
        if ($this->starScoreAction !== null) {
            $this->starScoreAction->undo($notifier);
        }
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARDS,
            [
                'cards' => [$this->undoContractCard],
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARDS,
            [
                'from' => \RR\CARD_LOCATION_ID_DISCARD,
                'cards' => $this->undoDiscardCards,
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARDS,
            [
                'from' => \RR\CARD_LOCATION_ID_PLAYER,
                'cards' => $this->undoBonusCards,
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_JOKER,
            [
                'used' => $this->undoPlayerState->jokerUsed,
            ]
        );
        $this->notifyUndoCounts($notifier);
    }
}

class RefillMarket extends \BX\Action\BaseActionCommandNoUndo
{
    use \RR\Actions\Traits\CardNotificationTrait;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');

        $missingContract = range(0, MARKET_SIZE_CONTRACT - 1);
        foreach ($cardMgr->getContractCardInMarket() as $card) {
            unset($missingContract[$card->locationOrder]);
        }
        foreach ($missingContract as $order) {
            $card = $cardMgr->getTopContractCardFromDeck();
            if ($card === null) {
                $notifier->notify(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('Contract deck is empty, the market will not be refilled'),
                    []
                );
                break;
            }
            $card->modifyAction();
            $card->moveToMarket($order);
            $notifier->notify(
                NTF_UPDATE_CARDS,
                clienttranslate('Contract ${contractList} is drawn to fill the market ${cardImage}'),
                [
                    'from' => \RR\CARD_LOCATION_ID_DECK,
                    'cards' => [$card],
                    'cardImage' => $card->cardId,
                    'contractList' => self::contractListNotification($card->def()),
                ]
            );
        }

        $missingNumber = range(0, MARKET_SIZE_NUMBER - 1);
        foreach ($cardMgr->getNumberCardInMarket() as $card) {
            unset($missingNumber[$card->locationOrder]);
        }
        foreach ($missingNumber as $order) {
            $card = $cardMgr->getTopNumberCardFromDeck();
            if ($card === null) {
                $cardMgr->shuffleDeckAndDiscardAction();
                $card = $cardMgr->getTopNumberCardFromDeck();
                if ($card === null) {
                    $notifier->notify(
                        \BX\Action\NTF_MESSAGE,
                        clienttranslate('Number deck and Number discard are empty, the market will not be refilled'),
                        []
                    );
                    break;
                }
                $notifier->notify(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('Number deck is empty, the discard is shuffled'),
                    []
                );
            }
            $card->modifyAction();
            $card->moveToMarket($order);
            $notifier->notify(
                NTF_UPDATE_CARDS,
                clienttranslate('Number card ${cardList} is drawn to fill the market ${cardImage}'),
                [
                    'from' => \RR\CARD_LOCATION_ID_DECK,
                    'cards' => [$card],
                    'cardImage' => $card->cardId,
                    'cardList' => self::cardListNotification([$card], false),
                ]
            );
        }

        $this->notifyUpdateCounts($notifier);
    }
}

class RefillHand extends \BX\Action\BaseActionCommandNoUndo
{
    use \RR\Actions\Traits\CardNotificationTrait;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $cards = [];
        while (count($cardMgr->getCardsInPlayerHand($this->playerId)) < MIN_HAND_SIZE) {
            $card = $cardMgr->getTopNumberCardFromDeck();
            if ($card === null) {
                $cardMgr->shuffleDeckAndDiscardAction();
                $card = $cardMgr->getTopNumberCardFromDeck();
                if ($card === null) {
                    $notifier->notify(
                        \BX\Action\NTF_MESSAGE,
                        clienttranslate('${player_name} cannot refill their hand because the Number deck and Number discard are empty'),
                        []
                    );
                    break;
                }
                $notifier->notify(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('Number deck is empty, the discard is shuffled'),
                    []
                );
            }
            $card->modifyAction();
            $card->moveToPlayerHand($this->playerId);
            $cards[] = $card;
        }
        
        $playerStateMgr = self::getMgr('player_state');
        $playerStateMgr->statAddNumberDrawAction($this->playerId, count($cards));

        if (count($cards) > 0) {
            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} draws ${drawCount} Number card(s) to refill their hand'),
                [
                    'drawCount' => count($cards),
                ]
            );
        }

        foreach ($cards as $card) {
            $notifier->notifyPrivate(
                NTF_UPDATE_CARDS,
                clienttranslate('${player_name} draws Number card ${cardList} to refill their hand ${cardImage}'),
                [
                    'from' => \RR\CARD_LOCATION_ID_DECK,
                    'cards' => [$card],
                    'cardImage' => $card->cardId,
                    'cardList' => self::cardListNotification([$card], false),
                ]
            );
        }

        $this->notifyUpdateCounts($notifier);
    }
}

class NumberDiscard extends \BX\Action\BaseActionCommandNoUndo
{
    use \RR\Actions\Traits\CardNotificationTrait;

    private $cardIds;

    public function __construct(int $playerId, array $cardIds)
    {
        parent::__construct($playerId);
        $this->cardIds = $cardIds;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $cards = [];
        foreach ($this->cardIds as $id) {
            $card = $cardMgr->getById($id);
            if ($card === null) {
                throw new \BgaSystemException("Unknown cardId $id");
            }
            if (!$card->isInPlayerHand($this->playerId)) {
                throw new \BgaSystemException("cardId $id is not in player {$this->playerId} hand");
            }
            $cards[] = $card;
        }

        foreach ($cards as $card) {
            $card->modifyAction();
            $card->moveToDiscard();
        }

        $playerStateMgr = self::getMgr('player_state');
        $playerStateMgr->statAddNumberDiscardAction($this->playerId, count($cards));

        if (count($cardMgr->getCardsInPlayerHand($this->playerId)) != MAX_HAND_SIZE) {
            throw new \BgaSystemException("Player {$this->playerId} did not discard the right number of cards");
        }

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} discards ${discardCount} Number card(s) to the maximum of 10 cards in hand'),
            [
                'discardCount' => count($cards),
            ]
        );

        foreach ($cards as $card) {
            $notifier->notifyPrivate(
                NTF_UPDATE_CARDS,
                clienttranslate('${player_name} discards Number card ${cardList} ${cardImage}'),
                [
                    'from' => \RR\CARD_LOCATION_ID_DECK,
                    'cards' => [$card],
                    'cardImage' => $card->cardId,
                    'cardList' => self::cardListNotification([$card], false),
                ]
            );
        }

        $this->notifyUpdateCounts($notifier);
    }
}

class UpdateLastRound extends \BX\LastRound\UpdateLastRoundActionCommand
{
    protected function detectLastRound()
    {
        $cardMgr = self::getMgr('card');
        return $cardMgr->detectLastRound();
    }

    protected function onDetectLastRound(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');
        $gs = $gameStateMgr->get();
        $gs->modifyAction();
        $gs->isLastRound = true;

        $playerStateMgr = self::getMgr('player_state');
        $firstPlayerId = $playerStateMgr->getFirstPlayerId();

        $playerMgr = self::getMgr('player');
        $playerIds = $playerMgr->getAllPlayerIds();
        $playerIds = \BX\Collection\rotateValueToFront($playerIds, $firstPlayerId);
        
        $lastPlayerId = $playerIds[count($playerIds) - 1];

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} will be the last to play'),
            [
                'player_name' => $playerMgr->getByPlayerId($lastPlayerId)->playerName,
            ]
        );
    }
}
