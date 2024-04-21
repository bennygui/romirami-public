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
        return declare("rr.CardMgr", null, {
            UPDATE_END_GAME_DELAY: 400,

            SUIT_TO_TROPHY: {
                1: 0,
                2: 3,
                3: 1,
                4: 2,
            },
            KIND_TO_TROPHY: {
                2: 0,
                3: 1,
                4: 2,
                5: 3,
            },
            CARD_ID_BACK_NUMBER: 1999,
            CARD_ID_BACK_CONTRACT: 2999,
            CARD_HEIGTH: 463,
            MAX_SUIT_BONUS_DECK: 5,

            setup(gamedatas) {
                if (gameui.isSpectator) {
                    this.getHandContainer().classList.add('bx-hidden');
                }

                const elemCreationElem = gameui.getElementCreationElement();
                for (const c of Object.values(gamedatas.cards)) {
                    const elem = this.createCardElem(c.cardId, true);
                    if (elem !== null) {
                        elemCreationElem.appendChild(elem);
                    }
                    if (c.locationId == gameui.CARD_LOCATION_ID_MARKET) {
                        this.moveCardToMarket(c.cardId, c.locationOrder, true);
                    } else if (c.locationId == gameui.CARD_LOCATION_ID_HAND) {
                        this.moveCardToHand(c.cardId, c.locationOrder, true);
                    } else if (c.locationId == gameui.CARD_LOCATION_ID_PLAYER) {
                        this.moveCardToPlayer(c.cardId, c.playerId, c.locationOrder, true);
                    }
                }

                for (const sort of this.getHandSorts()) {
                    const elem = document.querySelector('#rr-area-hand-sort-contrainer .rr-ui.rr-ui-sort-' + sort.id);
                    gameui.addBasicTooltipToElement(elem, sort.tooltip);
                    elem.addEventListener('click', () => {
                        this.sortHandBy(sort.id);
                    });
                }
                this.sortHandBy(this.getHandSorts()[1].id);

                const deckNumberElem = document.getElementById('rr-market-number-counter');
                gameui.addBasicTooltipToElement(deckNumberElem, _('Number of cards in Number deck'));
                gameui.counters.deckNumber.addTarget(deckNumberElem);
                for (const playerId in gamedatas.players) {
                    const counterHand = document.querySelector('#rr-area-player-' + playerId + ' .rr-counter-hand');
                    gameui.addBasicTooltipToElement(counterHand, _('Number of cards in hand'));
                    gameui.counters[playerId].hand.addTarget(
                        document.querySelector('#rr-area-player-' + playerId + ' .rr-counter-hand .bx-pill-counter')
                    );
                    const counterBonus = document.querySelector('#rr-area-player-' + playerId + ' .rr-player-number-count')
                    gameui.addBasicTooltipToElement(counterBonus, _('Number of Suit Bonus cards. This number is hidden to other players.'));
                    gameui.counters[playerId].bonus.addTarget(counterBonus);
                    gameui.counters[playerId].bonus.setNullIndicator('?');
                }

                this.isTrophySuit = [false, false, false, false];
                for (let i = 0; i < 4; ++i) {
                    if (gameui.isTrue(gamedatas.rrGameState['trophyTop' + i])) {
                        this.isTrophySuit[i] = true;
                    }
                }
                this.updatePublicCounts(gamedatas.publicCounts, true);
                this.updatePrivateCounts(gamedatas.privateCounts, true)

                // Compact switch
                const compactCheckElem = document.querySelector('#rr-switch-compact-contract input');
                compactCheckElem.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.RR_PREF_COMPACT_CONTRACT_ID, compactCheckElem.checked);
                });

                // Zoom switch and slider
                const zoomCheckElem = document.querySelector('#rr-area-zoom .bx-checkbox-switch input');
                zoomCheckElem.addEventListener('change', () => {
                    if (zoomCheckElem.checked) {
                        gameui.setLocalPreference(gameui.RR_PREF_ZOOM_ID, 50);
                    } else {
                        gameui.setLocalPreference(gameui.RR_PREF_ZOOM_ID, -1);
                    }
                });
                const zoomSliderElem = document.getElementById('rr-zoom-slider');
                zoomSliderElem.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.RR_PREF_ZOOM_ID, zoomSliderElem.value);
                });

                // Background
                const backgroundCheckElem = document.querySelector('#rr-area-pref-background input');
                backgroundCheckElem.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.RR_PREF_DARK_BACKGROUND_ID, backgroundCheckElem.checked);
                });

                // Confirm actions
                const confirmCheckElem = document.querySelector('#rr-area-pref-always-confirm input');
                confirmCheckElem.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.RR_PREF_ALWAYS_CONFIRM_ID, confirmCheckElem.checked);
                });

                // Contract Help
                const contractHelpCheckElem = document.querySelector('#rr-switch-contract-help input');
                contractHelpCheckElem.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.RR_PREF_SHOW_CONTRACT_HELP_ID, contractHelpCheckElem.checked);
                });

                this.adaptPlayerArea();
            },

            getHandSorts() {
                return [
                    { id: 'draw', tooltip: _('Sort cards in the order in they were drawn') },
                    { id: 'kind', tooltip: _('Sort cards by Kind: all numbers are grouped together') },
                    { id: 'suit', tooltip: _('Sort cards by Suit: all symbols are grouped together') },
                    { id: 'sequence', tooltip: _('Sort cards by Sequence: places cards in sequence from 1 to 5, grouped by suits when possible') },
                ];
            },

            getCardById(cardId) {
                return document.getElementById('rr-card-id-' + cardId);
            },

            getNumberMarket(order) {
                return document.querySelector('#rr-market-number .rr-card-container[data-location-id="' + order + '"]')
            },

            getContractMarket(order) {
                return document.querySelector('#rr-market-contract .rr-card-container[data-location-id="' + order + '"]')
            },

            getMarket(cardId, order) {
                if (gameui.cardDefMgr.cardIsNumber(cardId)) {
                    return this.getNumberMarket(order);
                } else {
                    return this.getContractMarket(order);
                }
            },

            getHand() {
                return document.getElementById('rr-area-card-hand');
            },

            getHandContainer() {
                return document.getElementById('rr-area-card-hand-container');
            },

            moveCardToMarket(cardId, order, isInstantaneous = false) {
                const cardElem = this.getCardById(cardId);
                const targetElem = this.getMarket(cardId, order);
                return gameui.slide(cardElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                }).then(() => {
                    this.applyHandSortToCards();
                });
            },

            moveCardToHand(cardId, order, isInstantaneous = false) {
                const cardElem = this.getCardById(cardId);
                cardElem.style.setProperty('--rr-card-draw-order', parseInt(order));
                this.applyHandSortToCards(cardId);
                const targetElem = this.getHand();
                return gameui.slide(cardElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveToOtherPlayerHand(cardId, playerId, isInstantaneous = false) {
                const cardElem = this.getCardById(cardId);
                const targetElem = document.querySelector('#rr-area-player-' + playerId + ' .rr-counter-hand .rr-card-target')
                return gameui.slide(cardElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveToOtherPlayerHandAndDestroy(cardId, playerId) {
                return this.moveToOtherPlayerHand(cardId, playerId).then(() => {
                    const cardElem = this.getCardById(cardId);
                    cardElem.remove();
                });
            },

            moveCardToDiscard(cardId, isInstantaneous = false) {
                const cardElem = this.getCardById(cardId);
                const targetElem =
                    gameui.cardDefMgr.cardIsNumber(cardId)
                        ? document.querySelector('#rr-market-number .rr-card-container.rr-for-deck')
                        : document.querySelector('#rr-market-contract .rr-card-container.rr-for-deck');
                return gameui.slide(cardElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveCardToDiscardAndDestroy(cardId) {
                return this.moveCardToDiscard(cardId).then(() => {
                    const cardElem = this.getCardById(cardId);
                    cardElem.remove();
                });
            },

            moveCardToPlayer(cardId, playerId, order, isInstantaneous = false) {
                const cardElem = this.getCardById(cardId);
                cardElem.dataset.cardOrder = order;
                cardElem.classList.remove('rr-valid-contract');
                cardElem.classList.remove('rr-valid-contract-star');
                const targetElem =
                    gameui.cardDefMgr.cardIsNumber(cardId)
                        ? document.querySelector('#rr-area-player-' + playerId + ' .rr-player-container-number')
                        : document.querySelector('#rr-area-player-' + playerId + ' .rr-player-container-contract');
                return gameui.slide(cardElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                }).then(() => this.adaptPlayerAreaForPlayer(playerId));
            },

            moveCardToPlayerAndDestroy(cardId, playerId, order) {
                return this.moveCardToPlayer(cardId, playerId, order).then(() => {
                    if (gameui.cardDefMgr.cardIsNumber(cardId)) {
                        const cardElem = this.getCardById(cardId);
                        cardElem.remove();
                    }
                });
            },

            createCardElem(cardId, setId = false) {
                const card = document.createElement('div');
                card.classList.add('rr-card');
                card.dataset.cardId = cardId;
                if (setId) {
                    card.id = 'rr-card-id-' + cardId;
                }

                const star = gameui.tokenMgr.createStar();
                card.appendChild(star);

                if (gameui.cardDefMgr.cardIsContract(cardId)) {
                    const help = document.createElement('div');
                    help.classList.add('rr-contract-help');
                    card.appendChild(help);
                    help.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.showContractHelp(cardId);
                    });
                }
                return card;
            },

            sortHandBy(newSort) {
                const handElem = this.getHand();
                for (const sort of this.getHandSorts()) {
                    const e = document.querySelector('#rr-area-hand-sort-contrainer .rr-ui.rr-ui-sort-' + sort.id);
                    e.classList.remove('rr-selected-sort');
                    handElem.classList.remove('rr-sort-by-' + sort.id);
                    if (sort.id == newSort) {
                        e.classList.add('rr-selected-sort');
                        handElem.classList.add('rr-sort-by-' + sort.id);
                    }
                }
            },

            applyHandSortToCards(newCardId = null) {
                const cardIds = [];
                if (newCardId !== null) {
                    cardIds.push(newCardId);
                }
                const cardElems = this.getHand().querySelectorAll('.rr-card[data-card-id]');
                for (const e of cardElems) {
                    if (e.dataset.cardId) {
                        cardIds.push(e.dataset.cardId);
                    }
                }

                const applySortToElem = (style) => {
                    for (const i in cardIds) {
                        const e = this.getCardById(cardIds[i]);
                        if (e !== null) {
                            e.style.setProperty(style, parseInt(i));
                        }
                    }
                };

                gameui.cardDefMgr.sortNumberCardByKind(cardIds);
                applySortToElem('--rr-card-kind-order');

                gameui.cardDefMgr.sortNumberCardBySuit(cardIds);
                applySortToElem('--rr-card-suit-order');

                gameui.cardDefMgr.sortNumberCardBySequence(cardIds);
                applySortToElem('--rr-card-sequence-order');
            },

            updatePublicCounts(publicCounts, isInstantaneous = false, isEndGame = false) {
                const movements = [];
                gameui.counters.deckNumber.toValue(publicCounts.deckNumberCount, isInstantaneous);
                for (const playerId in publicCounts.handCounts) {
                    gameui.counters[playerId].hand.toValue(publicCounts.handCounts[playerId], isInstantaneous);
                }
                for (const playerId in publicCounts.contractCounts) {
                    gameui.counters[playerId].contract.toValue([publicCounts.contractCounts[playerId], gameui.getEndGameContractCount()], isInstantaneous);
                }
                for (const playerId in publicCounts.suitBonusCounts) {
                    if (playerId == gameui.player_id && !isEndGame) {
                        continue;
                    }
                    gameui.counters[playerId].bonus.toValue(publicCounts.suitBonusCounts[playerId], isInstantaneous);
                    const fct = () => this.updateSuitBonusDeck(playerId, publicCounts.suitBonusCounts[playerId], isInstantaneous, isEndGame);
                    const move = (movements.length == 0)
                        ? fct
                        : () => movements[movements.length - 1].then(() => gameui.wait(this.UPDATE_END_GAME_DELAY * 3, !isEndGame || isInstantaneous).then(fct));
                    movements.push(move());
                }
                for (const playerId in publicCounts.trophySuitCounts) {
                    for (const id in publicCounts.trophySuitCounts[playerId]) {
                        const trophy = this.SUIT_TO_TROPHY[id];
                        if (this.isTrophySuit[trophy]) {
                            gameui.counters[playerId].trophy[trophy].toValue(publicCounts.trophySuitCounts[playerId][id], isInstantaneous);
                        }
                    }
                }
                for (const playerId in publicCounts.trophyKindCounts) {
                    for (const id in publicCounts.trophyKindCounts[playerId]) {
                        const trophy = this.KIND_TO_TROPHY[id];
                        if (!this.isTrophySuit[trophy]) {
                            gameui.counters[playerId].trophy[trophy].toValue(publicCounts.trophyKindCounts[playerId][id], isInstantaneous);
                        }
                    }
                }
                for (const trophyId in publicCounts.trophyEndGamePlayerId) {
                    const playerId = publicCounts.trophyEndGamePlayerId[trophyId];
                    const fct = () => gameui.trophyMgr.moveTrophyToPlayerId(trophyId, playerId, isInstantaneous);
                    const move = (movements.length == 0)
                        ? fct
                        : () => movements[movements.length - 1].then(() => gameui.wait(this.UPDATE_END_GAME_DELAY, isInstantaneous).then(fct));
                    movements.push(move());
                }
                return movements;
            },

            updatePrivateCounts(privateCounts, isInstantaneous = false) {
                if (!(privateCounts.playerId in gameui.counters)) {
                    return;
                }
                gameui.counters[privateCounts.playerId].bonus.toValue(privateCounts.suitBonus, isInstantaneous);
                this.updateSuitBonusDeck(privateCounts.playerId, privateCounts.suitBonus, isInstantaneous);
            },

            updateSuitBonusDeck(playerId, count, isInstantaneous = false, isEndGame = false) {
                const container = document.querySelector('#rr-area-player-' + playerId + ' .rr-player-container-number');
                container.innerHTML = '';
                if (count === null) {
                    const card = this.createCardElem(this.CARD_ID_BACK_NUMBER);
                    container.appendChild(card);
                } else if (count == 0) {
                    const card = this.createCardElem(this.CARD_ID_BACK_NUMBER);
                    card.classList.add('rr-suit-bonus-empty');
                    container.appendChild(card);
                } else {
                    for (let i = 0; i < Math.min(count, this.MAX_SUIT_BONUS_DECK); ++i) {
                        const card = this.createCardElem(this.CARD_ID_BACK_NUMBER);
                        container.appendChild(card);
                    }
                }
                if (isEndGame) {
                    gameui.displayBigNumberOnElement(
                        container,
                        count,
                        {
                            color: gameui.gamedatas.players[playerId].player_color,
                            displayDuration: 1000,
                        }
                    );
                }
                return Promise.resolve();
            },

            adaptPlayerArea() {
                this.applyHandSortToCards();
                for (const playerId in gameui.gamedatas.players) {
                    this.adaptPlayerAreaForPlayer(playerId);
                }
            },

            adaptPlayerAreaForPlayer(playerId) {
                // Resize contract area
                const containerElem = document.querySelector('#rr-area-player-' + playerId + ' .rr-area-player-cards')
                const cardContainerElem = containerElem.querySelector('.rr-player-container-contract');
                const cardElemArray = [];
                for (const cardElem of cardContainerElem.querySelectorAll('.rr-card')) {
                    if (cardElem.classList.contains('bx-moving')) {
                        continue;
                    }
                    cardElemArray.push(cardElem);
                    cardElem.remove();
                }
                cardElemArray.sort((a, b) => a.dataset.cardOrder - b.dataset.cardOrder);
                for (const cardElem of cardElemArray) {
                    cardContainerElem.appendChild(cardElem);
                }
                containerElem.style.minHeight = null;
                if (cardElemArray.length >= 1) {
                    containerElem.style.minHeight =
                        'calc('
                        + Math.max(
                            this.CARD_HEIGTH,
                            this.CARD_HEIGTH + 0.2 * this.CARD_HEIGTH * (cardElemArray.length - 1)
                        )
                        + 'px * var(--rr-zoom))';
                }
                // Show/Hide Compact contract switch
                const compactSwitchContainer = document.getElementById('rr-switch-compact-contract');
                let hasMultipleContracts = false;
                for (const container of document.querySelectorAll('.rr-player-container-contract')) {
                    if (container.querySelectorAll('.rr-card').length > 1) {
                        hasMultipleContracts = true;
                        break;
                    }
                }
                if (hasMultipleContracts) {
                    compactSwitchContainer.classList.remove('bx-hidden');
                } else {
                    compactSwitchContainer.classList.add('bx-hidden');
                }
            },

            showContractHelp(cardId) {
                const dialog = new bx.ModalDialog('rr-contract-help-dialog', {
                    title: _('Contract'),
                    contentsTpl: `<div class='rr-contract-detail-container'></div>`,
                    onShow: () => {
                        const fullContainerElem = document.getElementById('popin_rr-contract-help-dialog');
                        let zoom = fullContainerElem.offsetWidth / 400;
                        if (zoom > 1) {
                            zoom = 1;
                        }
                        const detailElem = fullContainerElem.querySelector('.rr-contract-detail-container');

                        const container = document.createElement('div');
                        container.classList.add('rr-card-and-desc');
                        detailElem.appendChild(container);

                        const cardElem = this.createCardElem(cardId);
                        cardElem.style.setProperty('--rr-zoom', zoom);
                        container.appendChild(cardElem);

                        const descElem = document.createElement('div');
                        descElem.classList.add('rr-card-detail-desc');
                        descElem.innerHTML = gameui.format_string_recursive(
                            '${log}',
                            {
                                'log': gameui.gamedatas.cardDefs[cardId].description
                            }
                        );
                        container.appendChild(descElem);
                    },
                });
                dialog.show();
            },
        });
    });
