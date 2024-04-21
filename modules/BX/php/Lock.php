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

namespace BX\Lock;

class Locker
{
    public static function setup()
    {
        $db = new class extends \APP_DbObject
        {
            public function executeQuery(string $sql)
            {
                return $this->DBQuery($sql);
            }
        };

        $db->executeQuery("INSERT INTO bx_lock (lock_id) VALUES (0)");
    }

    public static function lock()
    {
        $db = new class extends \APP_DbObject
        {
            public function executeSelect(string $sql)
            {
                return $this->getObjectListFromDB($sql);
            }
        };

        $db->executeSelect("SELECT lock_id FROM bx_lock WHERE 1 ORDER BY lock_id FOR UPDATE");
        \BX\DB\RowMgrRegister::clearAllMgrCache();
    }
}
