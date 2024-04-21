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
        return declare("rr.PlayerPanelMgr", null, {
            setup(gamedatas) {
                for (const playerId in gamedatas.players) {
                    this.createPlayerPanel(playerId, gamedatas.playerStates[playerId], gamedatas.rrGameState);
                }

                // Shortcuts
                const shortcutsCheckbox = document.querySelector('#rr-area-pref-shortcut input');
                shortcutsCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.RR_PREF_SHORTCUTS_ID, shortcutsCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(document.getElementById('rr-area-pref-shortcut'), _('Display a planel of shortcuts to navigate the page'));

                this.setupScrollShortcuts();
            },


            createPlayerPanel(playerId, playerState, gameState) {
                // Replace star with score icon
                const starElem = document.getElementById('icon_point_' + playerId);
                if (starElem != null) {
                    starElem.classList.add('bx-invisible');

                    const scoreElem = document.createElement('div');
                    scoreElem.classList.add('rr-score-icon')
                    scoreElem.id = 'rr-score-icon-' + playerId;

                    starElem.parentElement.insertBefore(scoreElem, starElem);
                }
                // Counter rows
                let row = null;
                let firstRow = null;

                for (const info of this.getCounters(playerId, gameState)) {
                    if (info === null) {
                        row = null;
                        continue;
                    }
                    if (row === null) {
                        row = gameui.appendPlayerPanelRow(playerId);
                        row.style.setProperty('--rr-zoom', 0.22);
                        if (firstRow === null) {
                            firstRow = row;
                        }
                    }
                    const elem = gameui.createPillElem(
                        info[1],
                        info[2],
                        (e) => info[0].addTarget(e)
                    );
                    row.appendChild(elem);
                    gameui.addBasicTooltipToElement(elem, info[3]);
                }
                const star = gameui.tokenMgr.createStar();
                firstRow.appendChild(star);
                gameui.addBasicTooltipToElement(star, gameui.tokenMgr.getStarDescription());
                this.setJokerUsed(playerState.playerId, playerState.jokerUsed);
                if (gameui.isTrue(playerState.isFirstPlayer)) {
                    const firstPlayer = gameui.tokenMgr.createFirstPlayer();
                    firstRow.appendChild(firstPlayer);
                    gameui.addBasicTooltipToElement(firstPlayer, _('Indicates who is the first player. Does not score any points.'));
                }
            },

            getCounters(playerId, gameState) {
                return [
                    [gameui.counters[playerId].hand, ['rr-ui', 'rr-ui-number'], null, _('Number of cards in hand')],
                    [gameui.counters[playerId].contract, ['rr-ui', 'rr-ui-contract'], null, _('Number of completed contracts / Number of contracts to end the game')],
                    [gameui.counters[playerId].bonus, 'rr-card', (e) => {
                        e.dataset.cardId = '1999';
                        e.style.setProperty('--rr-zoom', 0.04);
                    }, _('Number of suit bonus')],
                    null,
                    [gameui.counters[playerId].trophy[0], ['rr-icon', 'rr-icon-0-' + (gameui.isTrue(gameState.trophyTop0) ? 1 : 0)], null, _('Number of icons for the first Trophy')],
                    [gameui.counters[playerId].trophy[1], ['rr-icon', 'rr-icon-1-' + (gameui.isTrue(gameState.trophyTop1) ? 1 : 0)], null, _('Number of icons for the second Trophy')],
                    [gameui.counters[playerId].trophy[2], ['rr-icon', 'rr-icon-2-' + (gameui.isTrue(gameState.trophyTop2) ? 1 : 0)], null, _('Number of icons for the third Trophy')],
                    [gameui.counters[playerId].trophy[3], ['rr-icon', 'rr-icon-3-' + (gameui.isTrue(gameState.trophyTop3) ? 1 : 0)], null, _('Number of icons for the fourth Trophy')],
                ];
            },

            setupScrollShortcuts() {
                const areaElem = document.getElementById('rr-shortcut-area');
                if (gameui.getLocalPreference(gameui.RR_PREF_SHORTCUTS_ID)) {
                    document.body.classList.remove('rr-shortcuts-hidden');
                } else {
                    document.body.classList.add('rr-shortcuts-hidden');
                }
                areaElem.innerHTML = '';
                areaElem.appendChild(this.createScrollShortcut(
                    _('Top'),
                    document.body
                ));

                let searchPlayerId = null;
                if (gameui.player_id in gameui.gamedatas.players) {
                    searchPlayerId = gameui.player_id;
                    areaElem.appendChild(this.createScrollShortcut(
                        _('Hand'),
                        document.getElementById('rr-area-card-hand-container')
                    ));
                    areaElem.appendChild(this.createScrollShortcut(
                        gameui.gamedatas.players[searchPlayerId].player_name,
                        document.getElementById('rr-area-player-' + searchPlayerId)
                    ));
                }
                for (const playerId in gameui.gamedatas.players) {
                    if (playerId == searchPlayerId) {
                        continue;
                    }
                    areaElem.appendChild(this.createScrollShortcut(
                        gameui.gamedatas.players[playerId].player_name,
                        document.getElementById('rr-area-player-' + playerId),
                        true
                    ));
                }

                if (this.shortcutsScrollListener) {
                    dojo.disconnect(this.shortcutsScrollListener);
                }
                this.shortcutsScrollListener = dojo.connect(window, 'scroll', () => {
                    const shortcutRect = areaElem.getBoundingClientRect();

                    const pageContentElem = document.getElementById('page-content');
                    const pageContentRect = pageContentElem.getBoundingClientRect();
                    if (shortcutRect.bottom < pageContentRect.bottom) {
                        areaElem.classList.remove('bx-invisible');
                    } else {
                        areaElem.classList.add('bx-invisible');
                    }
                });
            },

            createScrollShortcut(title, scrollToElem, isPlayer = false) {
                const elem = document.createElement('div');
                if (isPlayer) {
                    elem.classList.add('rr-shortcut-is-player');
                }
                elem.addEventListener('click', () => {
                    scrollToElem.scrollIntoView();
                    window.scrollBy(0, -1 * document.getElementById('page-title').offsetHeight);
                });
                elem.innerText = title;
                return elem;
            },

            createPlayerPanelRow(parentElem) {
                const rowElem = document.createElement('div');
                rowElem.classList.add('rr-player-panel-row');
                parentElem.appendChild(rowElem);
                return rowElem;
            },

            createZoomSlider(id) {
                const sliderElem = document.createElement('input');
                sliderElem.id = id;
                sliderElem.type = 'range';
                sliderElem.min = 20;
                sliderElem.max = 100;
                sliderElem.step = 5;
                sliderElem.value = 40;
                return sliderElem;
            },

            setJokerUsed(playerId, isUsed) {
                const star = gameui.getPlayerPanelBoardElem(playerId).querySelector('.rr-token-star');
                if (gameui.isTrue(isUsed)) {
                    star.classList.add('rr-token-used');
                } else {
                    star.classList.remove('rr-token-used');
                }
            },
        });
    });
