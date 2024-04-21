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

namespace RR\Actions\Traits;

require_once(__DIR__ . '/../../../BX/php/Action.php');

trait CardNotificationTrait
{
    private $publicUndoCounts;
    private $privateUndoCounts;

    private function notifyUpdateCounts(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $notifier->notifyNoMessage(
            NTF_UPDATE_PUBLIC_COUNTS,
            [
                'publicCounts' => $cardMgr->getPublicCounts(),
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PRIVATE_COUNTS,
            [
                'privateCounts' => $cardMgr->getPrivateCounts($this->playerId),
            ]
        );
    }

    private function saveUndoCounts()
    {
        $cardMgr = self::getMgr('card');
        $this->publicUndoCounts = \BX\Meta\deepClone($cardMgr->getPublicCounts());
        $this->privateUndoCounts = \BX\Meta\deepClone($cardMgr->getPrivateCounts($this->playerId));
    }

    private function notifyUndoCounts(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_UPDATE_PUBLIC_COUNTS,
            [
                'publicCounts' => $this->publicUndoCounts,
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_PRIVATE_COUNTS,
            [
                'privateCounts' => $this->privateUndoCounts,
            ]
        );
    }

    private static function cardListNotification(array $cards, bool $useStar = false)
    {
        return [
            'log' => implode(' ', array_map(fn ($c) => '${numberImage' . $c->def()->number . '} ${suitImage' . $c->def()->suit . '}', $cards))
                . ($useStar ? ' ${starImage}' : ''),
            'args' => [
                'numberImage1' => '1',
                'numberImage2' => '2',
                'numberImage3' => '3',
                'numberImage4' => '4',
                'numberImage5' => '5',
                'suitImage1' => clienttranslate('Cherry'),
                'suitImage2' => clienttranslate('Diamond'),
                'suitImage3' => clienttranslate('Heart'),
                'suitImage4' => clienttranslate('Clover'),
                'starImage' => clienttranslate('Star'),
                'i18n' => ['suitImage1', 'suitImage2', 'suitImage3', 'suitImage4', 'starImage'],
            ],
        ];
    }

    private static function contractListNotification(\RR\CardDef $contractCard)
    {
        $logs = [];
        $args = [
            'suitImage1' => clienttranslate('Cherry'),
            'suitImage2' => clienttranslate('Diamond'),
            'suitImage3' => clienttranslate('Heart'),
            'suitImage4' => clienttranslate('Clover'),
            'i18n' => ['suitImage1', 'suitImage2', 'suitImage3', 'suitImage4'],
        ];

        foreach ($contractCard->contracts as $i => $c) {
            $logs[] = '${contractDesc' . $i . '}';
            $args['contractDesc' . $i] = $c->getDescription();
            $args['i18n'][] = 'contractDesc' . $i;
        }
        return [
            'log' => '${suitImage' . $contractCard->suit . '} ' . implode(', ', $logs),
            'args' => $args,
        ];
    }
}
