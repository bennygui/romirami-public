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
 * romirami.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in romirami_romirami.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_romirami_romirami extends game_view
{
  public function getGameName()
  {
    return "romirami";
  }

  private function insertPlayerAreaBlock($playerId, $playerInfo)
  {
    $this->page->insert_block(
      "player-area",
      [
        "PLAYER_ID" => $playerId,
        "PLAYER_NAME" => $playerInfo['player_name'],
        "PLAYER_COLOR" => $playerInfo['player_color'],
      ]
    );
  }

  public function build_page($viewArgs)
  {
    $this->tpl['ZOOM_AUTO'] = self::_('Automatic Zoom');
    $this->tpl['ZOOM_MANUAL'] = self::_('Manual Zoom');
    $this->tpl['SHORTCUTS'] = self::_('Shortcuts');
    $this->tpl['BACKGROUND_DARK'] = self::_('Dark Background');
    $this->tpl['CONFIRM'] = self::_('Confirm Actions');
    $this->tpl['COMPACT'] = self::_('Compact Contracts');
    $this->tpl['CONTRACT_HELP'] = self::_('Contract Help');


    $currentPlayerId = $this->game->currentPlayerId();
    $playersInfos = $this->game->loadPlayersBasicInfos();
    $this->page->begin_block("romirami_romirami", "player-area");
    if (array_key_exists($currentPlayerId, $playersInfos)) {
      $this->insertPlayerAreaBlock($currentPlayerId, $playersInfos[$currentPlayerId]);
    }

    $playerIdArray = array_keys($playersInfos);
    usort($playerIdArray, function ($p1, $p2) use (&$playersInfos) {
      return ($playersInfos[$p1]['player_no'] <> $playersInfos[$p2]['player_no']);
    });

    $currentPlayerIndex = array_search($currentPlayerId, $playerIdArray);
    if ($currentPlayerIndex === false) {
      $currentPlayerIndex = -1;
    }

    // Insert players that are after the current player
    foreach ($playerIdArray as $i => $playerId) {
      if ($i > $currentPlayerIndex) {
        $this->insertPlayerAreaBlock($playerId, $playersInfos[$playerId]);
      }
    }

    // Insert players that are before the current player
    foreach ($playerIdArray as $i => $playerId) {
      if ($i < $currentPlayerIndex) {
        $this->insertPlayerAreaBlock($playerId, $playersInfos[$playerId]);
      }
    }
    /*********** Do not change anything below this line  ************/
  }
}
