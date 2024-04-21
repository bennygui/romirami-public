
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- romirami implementation : © Guillaume Benny bennygui@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- -- Card
CREATE TABLE IF NOT EXISTS `card` (
  -- unique id: Each card in the game have a unique id
  `card_id` int(10) unsigned NOT NULL,
  -- Player that has this card, null if no player has it
  `player_id` int(10) unsigned NULL,
  -- The location of the component (deck, market, ...)
  `location_id` int(10) unsigned NOT NULL,
  -- The order of the component
  `location_order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- State for each players
CREATE TABLE IF NOT EXISTS `player_state` (
  -- The state is for this player
  `player_id` int(10) unsigned NOT NULL,
  -- If the player used their joker (star) or not
  `joker_used` boolean NOT NULL,
  -- If the player is the first player or not
  `is_first_player` boolean NOT NULL,
  -- Stats: Number cards: choose
  `stat_number_choose` smallint(5) NOT NULL,
  -- Stats: Number cards: draw
  `stat_number_draw` smallint(5) NOT NULL,
  -- Stats: Number cards: discard
  `stat_number_discard` smallint(5) NOT NULL,
  -- Stats: Number cards: play
  `stat_number_play` smallint(5) NOT NULL,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- State for the whole game
CREATE TABLE IF NOT EXISTS `game_state` (
  -- Always 0, there is only one game state
  `game_state_id` smallint(5) NOT NULL,
  -- Is it the last round?
  `is_last_round` boolean NOT NULL,
  -- Is this trophy face up or down?
  `trophy_top0` boolean NOT NULL,
  -- Is this trophy face up or down?
  `trophy_top1` boolean NOT NULL,
  -- Is this trophy face up or down?
  `trophy_top2` boolean NOT NULL,
  -- Is this trophy face up or down?
  `trophy_top3` boolean NOT NULL,
  PRIMARY KEY (`game_state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- === BX Tables === --

-- Lock table
CREATE TABLE IF NOT EXISTS `bx_lock` (
  -- The only value to lock, has no meaning
  `lock_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`lock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Actions that are still private to a player and that can be undone
CREATE TABLE IF NOT EXISTS `bx_action_command` (
  -- unique id with no meaning 
  `action_command_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  -- json version of the class
  `action_json` varchar(65535) NOT NULL,
  PRIMARY KEY (`action_command_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
