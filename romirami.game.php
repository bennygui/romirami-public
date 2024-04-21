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
 * romirami.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */


require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');
require_once("modules/BX/php/DB.php");
require_once("modules/BX/php/Lock.php");
require_once("modules/BX/php/Action.php");
require_once("modules/BX/php/UI.php");
require_once("modules/BX/php/ActiveState.php");
require_once("modules/RR/php/Globals.php");
require_once("modules/RR/php/CardDefMgr.php");
require_once("modules/RR/php/Card.php");
require_once("modules/RR/php/Player.php");
require_once("modules/RR/php/PlayerState.php");
require_once("modules/RR/php/GameState.php");
require_once("modules/RR/php/States/States.php");
require_once("modules/RR/php/States/GameEnd.php");
require_once("modules/RR/php/Actions/Actions.php");

require_once("modules/RR/php/Debug.php");

\BX\Action\BaseActionCommandNotifier::sendPrivateNotificationMessage(true);
\BX\Action\ActionRowMgrRegister::registerMgr('player', \RR\PlayerMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('card', \RR\CardMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('player_state', \RR\PlayerStateMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('game_state', \RR\GameStateMgr::class);

class romirami extends Table
{
    use BX\Action\GameActionsTrait;
    use BX\ActiveState\GameStatesTrait;
    use RR\State\States\GameStatesTrait;
    use RR\State\GameEnd\GameStatesTrait;

    use RR\Debug\GameStatesTrait;

    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        \BX\Action\BaseActionCommandNotifier::setGame($this);
        \RR\Actions\Actions\UpdateLastRound::registerIsLastRound(function () {
            $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
            return $gameStateMgr->get()->isLastRound;
        });

        self::initGameStateLabels([]);
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "romirami";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = [])
    {
        $gameinfos = self::getGameinfos();

        \BX\Lock\Locker::setup();
        $colors = \BX\Action\ActionRowMgrRegister::getMgr('player')->setup(
            $players,
            $gameinfos['player_colors']
        );

        self::reattributeColorsBasedOnPreferences($players, $colors);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        $this->initStat('table', STATS_TABLE_NB_ROUND, 1);

        $playerIdArray = $this->getPlayerIdArray();
        \BX\Action\ActionRowMgrRegister::getMgr('card')->setup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('player_state')->setup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('game_state')->setup();

        // Activate first player
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = [];

        $playerId = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
        \BX\Action\ActionCommandMgr::apply($playerId);

        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');

        $stateId = $this->gamestate->state_id();
        $gameHasEnded = ($stateId == STATE_GAME_END_ID);
        $result['cardDefs'] = \RR\CardDefMgr::getAll();
        $result['players'] = $playerMgr->getAllForUI($gameHasEnded);
        $result['cards'] = $cardMgr->getAllVisibleForPlayer($playerId);
        $result['playerStates'] = $playerStateMgr->getAll();
        $result['rrGameState'] = $gameStateMgr->get();
        $result['publicCounts'] = $cardMgr->getPublicCounts($gameHasEnded);
        $result['privateCounts'] = $cardMgr->getPrivateCounts($playerId);
        $result['isFirstMove'] = (
            ($stateId == STATE_PRE_CHOOSE_NUMBER_FROM_MARKET_ID
                || $stateId == STATE_CHOOSE_NUMBER_FROM_MARKET_ID)
            && \BX\BGAGlobal\GlobalMgr::getCurrentMoveNumber() == 1
        );
        \BX\LastRound\UpdateLastRoundActionCommand::getAllDatas($this, $result);

        return $result;
    }

    protected function initTable()
    {
        parent::initTable();
        \BX\DB\RowMgrRegister::clearAllMgrCache();
    }

    public function currentPlayerId()
    {
        return $this->getCurrentPlayerId();
    }

    public function _($text)
    {
        return parent::_($text);
    }

    public function getPlayerIdArray()
    {
        $playersInfos = $this->loadPlayersBasicInfos();
        $playerIdArray = array_keys($playersInfos);
        usort($playerIdArray, function ($p1, $p2) use (&$playersInfos) {
            return ($playersInfos[$p1]['player_no'] <=> $playersInfos[$p2]['player_no']);
        });
        return $playerIdArray;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        return $cardMgr->getGameProgression();
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */
    function zombieTurn($state, $playerId)
    {
        $this->notifyAllPlayers(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('The next actions are done automatically since player ${player_name} left'),
            [
                'player_name' => $this->loadPlayersBasicInfos()[$playerId]['player_name'],
            ]
        );
        \BX\Action\ActionCommandMgr::apply($playerId);

        $statename = $state['name'];
        switch ($statename) {
            case STATE_CHOOSE_NUMBER_FROM_MARKET:
            case STATE_CHOOSE_CONTRACT_FROM_MARKET:
            case STATE_CHOOSE_NUMBER_TO_DISCARD:
                \BX\Action\ActionCommandMgr::zombieRemoveAll($playerId);
                $this->notifyAllPlayers(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('${player_name} passes (automatic)'),
                    [
                        'player_name' => $this->loadPlayersBasicInfos()[$playerId]['player_name'],
                    ]
                );
                $this->gamestate->jumpToState(STATE_END_OF_TURN_ID);
                break;
            default:
                throw new \BgaSystemException("BUG! Zombie mode not supported for this game state: " . $statename);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    function upgradeTableDb($from_version)
    {
    }
}
