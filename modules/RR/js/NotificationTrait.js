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
        return declare("rr.NotificationTrait", null, {
            UPDATE_CARD_DELAY: 50,

            constructor() {
                // Format: ['notif', delay]
                if (this.notificationsToRegister === undefined) {
                    this.notificationsToRegister = [];
                }
                this.notificationsToRegister.push(['NTF_UPDATE_CARDS', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_PUBLIC_COUNTS', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_PRIVATE_COUNTS', 1]);
                this.notificationsToRegister.push(['NTF_UPDATE_JOKER', 1]);
            },

            notif_UpdateCards(args) {
                debug('notif_UpdateCards');
                debug(args);
                const movements = [];
                for (const c of args.args.cards) {
                    const fct = () => this.updateCard(c, args.args.from).then(() => this.cardMgr.adaptPlayerArea());
                    const move = (movements.length == 0)
                        ? fct
                        : () => movements[movements.length - 1].then(() => this.wait(this.UPDATE_CARD_DELAY).then(fct));
                    movements.push(move());
                }
                Promise.all(movements).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },
            updateCard(c, from) {
                let elem = this.cardMgr.getCardById(c.cardId);
                if (elem === null) {
                    if (!from) {
                        return Promise.resolve();
                    }
                    elem = this.cardMgr.createCardElem(c.cardId, true);
                    const elemCreationElem = this.getElementCreationElement();
                    elemCreationElem.appendChild(elem);
                    switch (parseInt(from)) {
                        case this.CARD_LOCATION_ID_DECK:
                        case this.CARD_LOCATION_ID_DISCARD:
                            this.cardMgr.moveCardToDiscard(c.cardId, true);
                            break;
                        case this.CARD_LOCATION_ID_PLAYER:
                            this.cardMgr.moveCardToPlayer(c.cardId, c.playerId, c.locationOrder, true);
                            break;
                        default:
                            console.error('BUG! Unknown from ' + from);
                            break;
                    }
                }
                switch (parseInt(c.locationId)) {
                    case this.CARD_LOCATION_ID_MARKET:
                        return this.cardMgr.moveCardToMarket(c.cardId, c.locationOrder);
                    case this.CARD_LOCATION_ID_HAND:
                        if (c.playerId == this.player_id) {
                            return this.cardMgr.moveCardToHand(c.cardId, c.locationOrder);
                        } else {
                            return this.cardMgr.moveToOtherPlayerHandAndDestroy(c.cardId, c.playerId);
                        }
                    case this.CARD_LOCATION_ID_DISCARD:
                        return this.cardMgr.moveCardToDiscardAndDestroy(c.cardId);
                    case this.CARD_LOCATION_ID_PLAYER:
                        return this.cardMgr.moveCardToPlayerAndDestroy(c.cardId, c.playerId, c.locationOrder);
                }
            },

            notif_UpdatePublicCounts(args) {
                debug('notif_UpdatePublicCounts');
                debug(args);
                const movements = this.cardMgr.updatePublicCounts(args.args.publicCounts, false, this.isTrue(args.args.isEndGame));
                Promise.all(movements).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_UpdatePrivateCounts(args) {
                debug('notif_UpdatePrivateCounts');
                debug(args);
                this.cardMgr.updatePrivateCounts(args.args.privateCounts);
            },

            notif_UpdateJoker(args) {
                debug('notif_UpdateJoker');
                debug(args);
                this.tokenMgr.setJokerUsed(args.args.playerId, args.args.used);
                this.playerPanelMgr.setJokerUsed(args.args.playerId, args.args.used);
            },

            notif_UpdatePlayerScore(args) {
                this.inherited(arguments);
                const scoreElem = document.getElementById('rr-score-icon-' + args.args.playerId);
                if (scoreElem !== null) {
                    scoreElem.classList.toggle('rr-score-animate');
                }
            },
        });
    })
