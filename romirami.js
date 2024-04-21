/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * romirami implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * romirami.js
 *
 * romirami user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () { };

define([
    "dojo",
    "dojo/_base/declare",
    "ebg/counter",
    "ebg/core/gamegui",
    g_gamethemeurl + "modules/BX/js/GameBase.js",
    g_gamethemeurl + "modules/BX/js/Numbers.js",
    g_gamethemeurl + "modules/BX/js/PlayerScoreTrait.js",
    g_gamethemeurl + "modules/BX/js/LastRoundTrait.js",
    g_gamethemeurl + "modules/RR/js/Mgr/CardDefMgr.js",
    g_gamethemeurl + "modules/RR/js/Mgr/CardMgr.js",
    g_gamethemeurl + "modules/RR/js/Mgr/TrophyMgr.js",
    g_gamethemeurl + "modules/RR/js/Mgr/TokenMgr.js",
    g_gamethemeurl + "modules/RR/js/Mgr/PlayerPanelMgr.js",
    g_gamethemeurl + "modules/RR/js/States/StateTrait.js",
    g_gamethemeurl + "modules/RR/js/NotificationTrait.js",
],
    function (dojo, declare) {
        return declare("bgagame.romirami", [
            bx.GameBase,
            bx.PlayerScoreTrait,
            bx.LastRoundTrait,
            rr.StateTrait,
            rr.NotificationTrait,
        ], {
            CARD_LOCATION_ID_DECK: 1,
            CARD_LOCATION_ID_MARKET: 2,
            CARD_LOCATION_ID_HAND: 3,
            CARD_LOCATION_ID_DISCARD: 4,
            CARD_LOCATION_ID_PLAYER: 5,

            RR_PREF_COMPACT_CONTRACT_ID: 'RR_PREF_COMPACT_CONTRACT_ID',
            RR_PREF_COMPACT_CONTRACT_DEFAULT_VALUE: true,

            RR_PREF_ZOOM_ID: 'RR_PREF_ZOOM_ID',
            RR_PREF_ZOOM_DEFAULT_VALUE: -1,

            RR_PREF_SHORTCUTS_ID: 'RR_PREF_SHORTCUTS_ID',
            RR_PREF_SHORTCUTS_DEFAULT_VALUE: true,

            RR_PREF_DARK_BACKGROUND_ID: 'RR_PREF_DARK_BACKGROUND_ID',
            RR_PREF_DARK_BACKGROUND_DEFAULT_VALUE: true,

            RR_PREF_ALWAYS_CONFIRM_ID: 'RR_PREF_ALWAYS_CONFIRM_ID',
            RR_PREF_ALWAYS_CONFIRM_DEFAULT_VALUE: false,

            RR_PREF_SHOW_CONTRACT_HELP_ID: 'RR_PREF_SHOW_CONTRACT_HELP_ID',
            RR_PREF_SHOW_CONTRACT_HELP_DEFAULT_VALUE: true,

            constructor() {
                this.counters = {};
                this.setAlwaysFixTopActions();

                this.cardDefMgr = new rr.CardDefMgr();
                this.trophyMgr = new rr.TrophyMgr();
                this.cardMgr = new rr.CardMgr();
                this.tokenMgr = new rr.TokenMgr();
                this.playerPanelMgr = new rr.PlayerPanelMgr();

                this.htmlTextForLogKeys.push('cardImage');
                this.htmlTextForLogKeys.push('numberImage1');
                this.htmlTextForLogKeys.push('numberImage2');
                this.htmlTextForLogKeys.push('numberImage3');
                this.htmlTextForLogKeys.push('numberImage4');
                this.htmlTextForLogKeys.push('numberImage5');
                this.htmlTextForLogKeys.push('suitImage1');
                this.htmlTextForLogKeys.push('suitImage2');
                this.htmlTextForLogKeys.push('suitImage3');
                this.htmlTextForLogKeys.push('suitImage4');
                this.htmlTextForLogKeys.push('starImage');

                this.localPreferenceToRegister.push([this.RR_PREF_COMPACT_CONTRACT_ID, this.RR_PREF_COMPACT_CONTRACT_DEFAULT_VALUE]);
                this.localPreferenceToRegister.push([this.RR_PREF_ZOOM_ID, this.RR_PREF_ZOOM_DEFAULT_VALUE]);
                this.localPreferenceToRegister.push([this.RR_PREF_SHORTCUTS_ID, this.RR_PREF_SHORTCUTS_DEFAULT_VALUE]);
                this.localPreferenceToRegister.push([this.RR_PREF_DARK_BACKGROUND_ID, this.RR_PREF_DARK_BACKGROUND_DEFAULT_VALUE]);
                this.localPreferenceToRegister.push([this.RR_PREF_ALWAYS_CONFIRM_ID, this.RR_PREF_ALWAYS_CONFIRM_DEFAULT_VALUE]);
                this.localPreferenceToRegister.push([this.RR_PREF_SHOW_CONTRACT_HELP_ID, this.RR_PREF_SHOW_CONTRACT_HELP_DEFAULT_VALUE]);
            },

            setup(gamedatas) {
                // Counters
                this.counters.deckNumber = new bx.Numbers();
                for (const playerId in gamedatas.players) {
                    this.counters[playerId] = {
                        hand: new bx.Numbers(),
                        contract: new bx.Numbers([0, this.getEndGameContractCount()]),
                        bonus: new bx.Numbers(null),
                        trophy: [
                            new bx.Numbers(),
                            new bx.Numbers(),
                            new bx.Numbers(),
                            new bx.Numbers(),
                        ],
                    };
                }

                // Adapt back color for yellow player
                this.setupBackColorForPlayerColor(gamedatas);

                // Preload images
                const preloadImageArray = [
                    'card_contract.jpg',
                    'card_number.jpg',
                    'light_background.jpg',
                    'dark_background.jpg',
                    'icon.png',
                    'token.png',
                    'score.png',
                    'trophy.png',
                    'ui.png',
                ];
                this.ensureSpecificGameImageLoading(preloadImageArray);

                this.cardDefMgr.setup(gamedatas);
                this.trophyMgr.setup(gamedatas);
                this.cardMgr.setup(gamedatas);
                this.tokenMgr.setup(gamedatas);
                this.playerPanelMgr.setup(gamedatas);

                this.inherited(arguments);
            },

            onLocalPreferenceChanged(prefId, value) {
                switch (prefId) {
                    case this.RR_PREF_COMPACT_CONTRACT_ID: {
                        const checkbox = document.querySelector('#rr-switch-compact-contract input');
                        checkbox.checked = value;
                        for (const e of document.querySelectorAll('.rr-area-player')) {
                            if (value) {
                                e.classList.remove('rr-card-row');
                            } else {
                                e.classList.add('rr-card-row');
                            }
                        }
                        break;
                    }
                    case this.RR_PREF_ZOOM_ID: {
                        const checkElem = document.querySelector('#rr-area-zoom .bx-checkbox-switch input');
                        const sliderElem = document.getElementById('rr-zoom-slider');
                        if (value < 0) {
                            checkElem.checked = false;
                            sliderElem.disabled = true;
                        } else {
                            checkElem.checked = true;
                            sliderElem.disabled = false;
                            sliderElem.value = value;
                        }
                        this.onScreenWidthChange();
                        break;
                    }
                    case this.RR_PREF_SHORTCUTS_ID: {
                        const checkbox = document.querySelector('#rr-area-pref-shortcut input');
                        checkbox.checked = value;
                        this.playerPanelMgr.setupScrollShortcuts();
                        break;
                    }
                    case this.RR_PREF_DARK_BACKGROUND_ID: {
                        const checkbox = document.querySelector('#rr-area-pref-background input');
                        checkbox.checked = value;
                        if (checkbox.checked) {
                            document.documentElement.classList.add('bx-background-dark');
                        } else {
                            document.documentElement.classList.remove('bx-background-dark');
                        }
                        break;
                    }
                    case this.RR_PREF_ALWAYS_CONFIRM_ID: {
                        const checkbox = document.querySelector('#rr-area-pref-always-confirm input');
                        checkbox.checked = value;
                        break;
                    }
                    case this.RR_PREF_SHOW_CONTRACT_HELP_ID: {
                        const checkbox = document.querySelector('#rr-switch-contract-help input');
                        checkbox.checked = value;
                        if (checkbox.checked) {
                            document.documentElement.classList.remove('rr-contract-help-invisible');
                        } else {
                            document.documentElement.classList.add('rr-contract-help-invisible');
                        }
                        break;
                    }
                }
            },

            getHtmlTextForLogArg(key, value) {
                switch (key) {
                    case 'cardImage': {
                        const element = this.cardMgr.createCardElem(value);
                        return element.outerHTML;
                    }
                    case 'numberImage1':
                    case 'numberImage2':
                    case 'numberImage3':
                    case 'numberImage4':
                    case 'numberImage5': {
                        return '<span class="rr-number">' + value + '</span>';
                    }
                    case 'suitImage1': {
                        const element = this.createSuitElement(1);
                        return element.outerHTML;
                    }
                    case 'suitImage2': {
                        const element = this.createSuitElement(2);
                        return element.outerHTML;
                    }
                    case 'suitImage3': {
                        const element = this.createSuitElement(3);
                        return element.outerHTML;
                    }
                    case 'suitImage4': {
                        const element = this.createSuitElement(4);
                        return element.outerHTML;
                    }
                    case 'starImage': {
                        const element = this.tokenMgr.createStar();
                        return element.outerHTML;
                    }
                }
                return this.inherited(arguments);
            },

            onStateChangedBefore(stateName, args) {
                this.inherited(arguments);
            },

            onStateChangedAfter(stateName, args) {
                this.inherited(arguments);
            },

            onUpdateActionButtonsBefore(stateName, args) {
                this.inherited(arguments);
                this.removeAllClickable();
                this.removeAllSelected();
            },

            onUpdateActionButtonsdAfter(stateName, args) {
                this.addTopUndoButton(args);
                this.inherited(arguments);
            },

            onUndoBegin() {
                this.inherited(arguments);
                this.removeAllClickable();
                this.removeAllSelected();
                this.clearSelectedBeforeRemoveAll();
                this.clearTopButtonTimer();
                this.clearStates();
            },

            onLeavingState(stateName) {
                this.inherited(arguments);
                this.removeAllClickable();
                this.removeAllSelected();
                this.clearSelectedBeforeRemoveAll();
                this.clearTopButtonTimer();
                this.clearStates();
            },

            onLoadingComplete() {
                this.inherited(arguments);
                this.showWelcomeMessage();
                this.trophyMgr.animateTrophyThrow(this.isTrue(this.gamedatas.isFirstMove));
            },

            onScreenWidthChange() {
                this.inherited(arguments);
                let zoom = window.innerHeight / 2500;
                zoom = Math.min(Math.max(zoom, 0.4), 1);
                const prefZoom = this.getLocalPreference(this.RR_PREF_ZOOM_ID);
                if (prefZoom >= 0) {
                    zoom = Math.min(Math.max(prefZoom / 100.0, 0.2), 1);
                }
                document.body.style.setProperty('--rr-zoom', zoom);
            },

            showWelcomeMessage() {
                if (this.isReadOnly()) {
                    return;
                }
            },

            getElementCreationElement() {
                return document.getElementById('rr-element-creation');
            },

            clearStates() {
                for (const e of document.querySelectorAll('.rr-valid-contract')) {
                    e.classList.remove('rr-valid-contract');
                }
                for (const e of document.querySelectorAll('.rr-valid-contract-star')) {
                    e.classList.remove('rr-valid-contract-star');
                }
            },

            mustConfirmActions() {
                return this.getLocalPreference(this.RR_PREF_ALWAYS_CONFIRM_ID);
            },

            getEndGameContractCount() {
                switch (Object.keys(this.gamedatas.players).length) {
                    case 2:
                        return 7;
                    case 3:
                        return 6;
                    default:
                        return 5;
                }
            },

            createSuitElement(suit) {
                switch (suit) {
                    case this.cardDefMgr.CARD_SUIT_CHERRY:
                        suit = 0;
                        break;
                    case this.cardDefMgr.CARD_SUIT_DIAMOND:
                        suit = 3;
                        break;
                    case this.cardDefMgr.CARD_SUIT_HEART:
                        suit = 1;
                        break;
                    case this.cardDefMgr.CARD_SUIT_CLOVER:
                        suit = 2;
                        break;
                }
                const e = document.createElement('div');
                e.classList.add('rr-icon', 'rr-icon-' + suit + '-1');
                return e;
            },
        });
    });
