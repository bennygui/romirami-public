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
        return declare("rr.TokenMgr", null, {
            setup(gamedatas) {
                for (const ps of Object.values(gameui.gamedatas.playerStates)) {
                    const container = this.getStarContainer(ps.playerId);
                    const star = this.createStar();
                    container.appendChild(star);
                    gameui.addBasicTooltipToElement(star, this.getStarDescription());
                    this.setJokerUsed(ps.playerId, ps.jokerUsed);
                    if (gameui.isTrue(ps.isFirstPlayer)) {
                        const container = this.getFirstPlayerContainer(ps.playerId);
                        const firstPlayer = this.createFirstPlayer();
                        container.appendChild(firstPlayer);
                        gameui.addBasicTooltipToElement(firstPlayer, _('Indicates who is the first player. Does not score any points.'));
                    }
                }
                const handStar = this.getHandStar();
                if (handStar !== null) {
                    gameui.addBasicTooltipToElement(handStar, this.getStarDescription());
                }
            },

            getStarContainer(playerId) {
                return document.querySelector('#rr-area-player-' + playerId + ' .rr-star-container');
            },

            getHandStar() {
                return document.getElementById('rr-hand-star');
            },

            getFirstPlayerContainer(playerId) {
                return document.querySelector('#rr-area-player-' + playerId + ' .rr-first-player-container');
            },

            setJokerUsed(playerId, isUsed) {
                const boardStar = this.getStarContainer(playerId).querySelector('.rr-token-star');
                const handStar = (playerId == gameui.player_id)
                    ? this.getHandStar()
                    : null;
                if (gameui.isTrue(isUsed)) {
                    boardStar.classList.add('rr-token-used');
                    if (handStar !== null) {
                        handStar.classList.add('rr-token-used');
                    }
                } else {
                    boardStar.classList.remove('rr-token-used');
                    if (handStar !== null) {
                        handStar.classList.remove('rr-token-used');
                    }
                }
            },

            createStar() {
                const elem = document.createElement('div');
                elem.classList.add('rr-token', 'rr-token-star');
                return elem;
            },

            getStarDescription() {
                return _('The Star can be used once to replace one Number card in the game. Scores 1 point if not used. You do not click on the Star to play it, it is automatically used when you select one less card than required by the contract.');
            },

            createFirstPlayer() {
                const elem = document.createElement('div');
                elem.classList.add('rr-token', 'rr-token-first-player');
                return elem;
            },
        });
    });
