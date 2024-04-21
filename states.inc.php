<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * romirami implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * romirami game states description
 *
 */

require_once("modules/BX/php/Globals.php");
require_once("modules/RR/php/Globals.php");

$machinestates = [

    // The initial state. Please do not modify.
    STATE_GAME_START_ID => [
        "name" => STATE_GAME_START,
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => [
            "" => STATE_PRE_CHOOSE_NUMBER_FROM_MARKET_ID,
        ],
    ],

    STATE_PRE_CHOOSE_NUMBER_FROM_MARKET_ID => [
        "name" => STATE_PRE_CHOOSE_NUMBER_FROM_MARKET,
        "type" => "game",
        "action" => 'stPreChooseNumberFromMarket',
        "transitions" => [
            'hasNumber' => STATE_CHOOSE_NUMBER_FROM_MARKET_ID,
            'noNumber' => STATE_PRE_CHOOSE_CONTRACT_FROM_MARKET_ID,
        ],
    ],
    STATE_CHOOSE_NUMBER_FROM_MARKET_ID => [
        "name" => STATE_CHOOSE_NUMBER_FROM_MARKET,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose Number cards from Market'),
        "type" => "activeplayer",
        "args" => "argChooseNumberFromMarket",
        "possibleactions" => [
            'numberChoose',
        ],
        "transitions" => [
            '' => STATE_PRE_CHOOSE_CONTRACT_FROM_MARKET_ID,
        ],
    ],

    STATE_PRE_CHOOSE_CONTRACT_FROM_MARKET_ID => [
        "name" => STATE_PRE_CHOOSE_CONTRACT_FROM_MARKET,
        "type" => "game",
        "action" => 'stPreChooseContractFromMarket',
        "transitions" => [
            'hasContract' => STATE_CHOOSE_CONTRACT_FROM_MARKET_ID,
            'noContract' => STATE_REFILL_MARKET_ID,
        ],
    ],
    STATE_CHOOSE_CONTRACT_FROM_MARKET_ID => [
        "name" => STATE_CHOOSE_CONTRACT_FROM_MARKET,
        "description" => clienttranslate('${actplayer} must play'),
        "descriptionmyturn" => clienttranslate('${you} can complete a contract from the Market or Pass'),
        "type" => "activeplayer",
        "args" => "argChooseContractFromMarket",
        "possibleactions" => [
            'contractChoose',
            'contractPass',
        ],
        "transitions" => [
            'chooseContract' => STATE_PRE_CHOOSE_CONTRACT_FROM_MARKET_ID,
            'choosePass' => STATE_REFILL_MARKET_ID,
        ],
    ],

    STATE_REFILL_MARKET_ID => [
        "name" => STATE_REFILL_MARKET,
        "type" => "game",
        "action" => 'stRefillMarket',
        "updateGameProgression" => true,
        "transitions" => [
            'mustDiscard' => STATE_CHOOSE_NUMBER_TO_DISCARD_ID,
            'endTurn' => STATE_END_OF_TURN_ID,
        ],
    ],
    STATE_CHOOSE_NUMBER_TO_DISCARD_ID => [
        "name" => STATE_CHOOSE_NUMBER_TO_DISCARD,
        "description" => clienttranslate('${actplayer} must discard to 10 cards'),
        "descriptionmyturn" => clienttranslate('${you} must discard to 10 cards'),
        "type" => "activeplayer",
        "args" => "argChooseNumberToDiscard",
        "possibleactions" => [
            'numberDiscard',
        ],
        "transitions" => [
            '' => STATE_END_OF_TURN_ID,
        ],
    ],

    STATE_END_OF_TURN_ID => [
        "name" => STATE_END_OF_TURN,
        "type" => "game",
        "action" => 'stEndOfTurn',
        "transitions" => [
            'nextPlayer' => STATE_PRE_CHOOSE_NUMBER_FROM_MARKET_ID,
            'endGame' => STATE_PRE_GAME_END_ID,
        ],
    ],
    STATE_PRE_GAME_END_ID => [
        "name" => STATE_PRE_GAME_END,
        "type" => "game",
        "action" => 'stPreGameEnd',
        "transitions" => [
            '' => STATE_GAME_END_ID,
        ],
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    STATE_GAME_END_ID => [
        "name" => STATE_GAME_END,
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ],
];
