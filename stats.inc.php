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
 * stats.inc.php
 *
 * romirami game statistics description
 *
 */

require_once('modules/RR/php/Globals.php');

$stats_type = [
    // Statistics global to table
    'table' => [
        STATS_TABLE_NB_ROUND => ['id' => 10, 'name' => totranslate('Nb Rounds'), 'type' => 'int'],
    ],

    // Statistics for each player
    'player' => [
        STATS_PLAYER_SCORE_FROM_CONTRACT => ['id' => 10, 'name' => totranslate('Score from Contracts'), 'type' => 'int'],
        STATS_PLAYER_SCORE_FROM_TROPHY => ['id' => 11, 'name' => totranslate('Score from Trophies'), 'type' => 'int'],
        STATS_PLAYER_SCORE_FROM_SUIT_BONUS => ['id' => 12, 'name' => totranslate('Score from Suit Bonus'), 'type' => 'int'],
        STATS_PLAYER_SCORE_FROM_STAR => ['id' => 13, 'name' => totranslate('Score from Star'), 'type' => 'int'],
        STATS_PLAYER_NB_CONTRACT => ['id' => 14, 'name' => totranslate('Nb Contracts'), 'type' => 'int'],
        STATS_PLAYER_NB_TROPHY => ['id' => 15, 'name' => totranslate('Nb Trophies'), 'type' => 'int'],
        STATS_PLAYER_NB_NUMBER_CHOOSE => ['id' => 16, 'name' => totranslate('Nb Choosen Number cards'), 'type' => 'int'],
        STATS_PLAYER_NB_NUMBER_DRAW => ['id' => 17, 'name' => totranslate('Nb Drawn Number cards to complete hand'), 'type' => 'int'],
        STATS_PLAYER_NB_NUMBER_DISCARD => ['id' => 18, 'name' => totranslate('Nb Discarded Number cards for maximum hand size'), 'type' => 'int'],
        STATS_PLAYER_NB_NUMBER_PLAY => ['id' => 19, 'name' => totranslate('Nb Number cards used for contracts'), 'type' => 'int'],
        STATS_PLAYER_IS_FIRST_PLAYER => ['id' => 20, 'name' => totranslate('Is first player (1 = first, 0 = not first)'), 'type' => 'int'],
    ],
];
