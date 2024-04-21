<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * romirami implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * romirami.action.php
 *
 * romirami main action entry point
 *
 */

require_once("modules/RR/php/Globals.php");

class action_romirami extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = $this->getArg("table", AT_posint, true);
    } else {
      $this->view = "romirami_romirami";
      self::trace("Complete reinitialization of board game");
    }
  }

  public function undoLast()
  {
    self::setAjaxMode();
    $this->game->undoLast();
    self::ajaxResponse();
  }

  public function undoAll()
  {
    self::setAjaxMode();
    $this->game->undoAll();
    self::ajaxResponse();
  }

  public function numberChoose()
  {
    self::setAjaxMode();

    $cardIds = $this->getCardIds();
    $this->game->numberChoose($cardIds);

    self::ajaxResponse();
  }

  public function contractChoose()
  {
    self::setAjaxMode();

    $contractCardId = $this->getArg("contractCardId", AT_posint, true);
    $cardIds = $this->getCardIds();
    $this->game->contractChoose($contractCardId, $cardIds);

    self::ajaxResponse();
  }

  public function contractPass()
  {
    self::setAjaxMode();

    $this->game->contractPass();

    self::ajaxResponse();
  }

  public function numberDiscard()
  {
    self::setAjaxMode();

    $cardIds = $this->getCardIds();
    $this->game->numberDiscard($cardIds);

    self::ajaxResponse();
  }

  private function getCardIds()
  {
    $cardIds = $this->getArg("cardIds", AT_numberlist, true);
    if ($cardIds === null) {
      $cardIds = '';
    }
    $cardIds = trim($cardIds);
    if (strlen($cardIds) == 0) {
      $cardIds = [];
    } else {
      $cardIds = explode(',', $cardIds);
    }
    return $cardIds;
  }
}
