/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * romirami implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () { };

define([
    "dojo",
    "dojo/_base/declare",
],
    function (dojo, declare) {
        return declare("rr.TrophyMgr", null, {
            NB_TROPHY: 4,

            setup(gamedatas) {
                const container = this.getAreaTrophy();
                for (let id = 0; id < this.NB_TROPHY; ++id) {
                    const isTop = gameui.isTrue(gamedatas.rrGameState['trophyTop' + id]);
                    const elem = this.createTrophy(id, isTop, true);
                    elem.style.opacity = 0;
                    container.appendChild(elem);
                    gameui.addBasicTooltipToElement(
                        elem,
                        this.getTrophyDesc(id, isTop)
                        + ': '
                        + _('The player with the maximum number of this icon on their Contract cards will score this Trophy. In case of a tie, no one gets the Trophy.')
                    );
                }
            },

            getAreaTrophy() {
                return document.getElementById('rr-area-trophy');
            },

            getTrophy(id) {
                return document.getElementById('rr-trophy-' + id);
            },

            getTrophyDesc(id, isTop) {
                switch (id) {
                    case 0:
                        if (isTop) {
                            return _('Cherries');
                        } else {
                            return _('2-Card Combos');
                        }
                    case 1:
                        if (isTop) {
                            return _('Hearts');
                        } else {
                            return _('3-Card Combos');
                        }
                    case 2:
                        if (isTop) {
                            return _('Clovers');
                        } else {
                            return _('4-Card Combos');
                        }
                    case 3:
                        if (isTop) {
                            return _('Diamonds');
                        } else {
                            return _('5-Card Combos');
                        }
                }
                return '';
            },

            createTrophy(id, isTop, setId = false) {
                const elem = document.createElement('div');
                elem.classList.add('rr-trophy');
                elem.dataset.trophyId = id;
                elem.dataset.trophySide = (isTop ? 1 : 0);
                if (setId) {
                    elem.id = 'rr-trophy-' + id;
                }
                return elem;
            },

            moveTrophyToPlayerId(trophyId, playerId, isInstantaneous = false) {
                const trophyElem = this.getTrophy(trophyId);
                const targetElem = document.querySelector('#rr-area-player-' + playerId + ' .rr-area-player-trophy')
                return gameui.slide(trophyElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            animateTrophyThrow(animate) {
                for (let id = 0; id < this.NB_TROPHY; ++id) {
                    const elem = this.getTrophy(id);
                    if (elem !== null) {
                        if (animate) {
                            gameui.wait(Math.floor(500 + Math.random() * 500))
                                .then(() => {
                                    elem.style.opacity = null;
                                    elem.classList.add('rr-fall');
                                    return gameui.wait(1000);
                                })
                                .then(() => {
                                    elem.classList.remove('rr-fall');
                                });
                        } else {
                            elem.style.opacity = null;
                        }
                    }
                }
            },
        });
    });
