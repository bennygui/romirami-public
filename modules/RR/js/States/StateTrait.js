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
        return declare("rr.StateTrait", null, {
            onButtonsStateChooseNumberFromMarket(args) {
                debug('onButtonsStateChooseNumberFromMarket');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }

                const BUTTON_ID_SELECT = 'rr-button-select';
                const SELECT_TITLE = _('Select Number Cards');
                const selectedCardIds = new Set();
                const setTitle = (title) => {
                    const button = document.getElementById(BUTTON_ID_SELECT);
                    button.innerHTML = title;
                };
                const reset = () => {
                    this.removeAllSelected();
                    selectedCardIds.clear();
                    this.setTopButtonValid(BUTTON_ID_SELECT, false);
                    setTitle(SELECT_TITLE);
                };
                this.addTopButtonPrimaryWithValid(
                    BUTTON_ID_SELECT,
                    SELECT_TITLE,
                    _('You must select 1 to 3 Number Cards of the same Kind or the same Suit'),
                    () => {
                        this.serverAction('numberChoose', { cardIds: selectedCardIds.join(',') })
                    }
                );
                for (const id of args.cardIds) {
                    const e = this.cardMgr.getCardById(id);
                    this.addClickable(e, () => {
                        if (selectedCardIds.has(id)) {
                            this.removeSelected(e);
                            selectedCardIds.delete(id);
                        } else {
                            this.addSelected(e);
                            selectedCardIds.add(id);
                        }
                        const isValid = this.cardDefMgr.isNumberChoiceFromMarketValid(selectedCardIds);
                        this.setTopButtonValid(BUTTON_ID_SELECT, isValid);
                        if (isValid) {
                            setTitle(
                                this.format_string_recursive(
                                    _('Choose ${numbers} of ${suits}'),
                                    {
                                        numbers: '<span class="rr-number">' + this.cardDefMgr.uniqueNumbers(selectedCardIds).join(' ') + '</span>',
                                        suits: this.cardDefMgr.uniqueSuits(selectedCardIds).map((suit) => this.createSuitElement(suit).outerHTML).join(' '),
                                    }
                                )
                            );
                        } else {
                            setTitle(SELECT_TITLE);
                        }
                    });
                }
                this.setTopButtonValid(BUTTON_ID_SELECT, false);

                this.addTopButtonSecondary('rr-button-reset', _('Reset'), () => reset());
            },

            onButtonsStateChooseContractFromMarket(args) {
                debug('onButtonsStateChooseContractFromMarket');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }

                const BUTTON_ID_SELECT = 'rr-button-select';
                const SELECT_TITLE = _('Select Number Cards and Contract');
                const selectedHandCardIds = new Set();
                let selectedContractId = null;
                const setTitle = (title) => {
                    const button = document.getElementById(BUTTON_ID_SELECT);
                    button.innerHTML = title;
                };
                const reset = () => {
                    this.removeAllSelected();
                    for (const id of args.contractCardIds) {
                        const e = this.cardMgr.getCardById(id);
                        e.classList.remove('rr-valid-contract');
                        e.classList.remove('rr-valid-contract-star');
                    }
                    const handStar = this.tokenMgr.getHandStar();
                    this.removeSelected(handStar);
                    selectedHandCardIds.clear();
                    selectedContractId = null
                    this.setTopButtonValid(BUTTON_ID_SELECT, false);
                    setTitle(SELECT_TITLE);
                };
                const updateValid = () => {
                    for (const id of args.contractCardIds) {
                        const e = this.cardMgr.getCardById(id);
                        e.classList.remove('rr-valid-contract');
                        e.classList.remove('rr-valid-contract-star');
                        if (this.isTrue(args.hasJoker) && this.cardDefMgr.isContractFilled(id, selectedHandCardIds, true)) {
                            e.classList.add('rr-valid-contract');
                            e.classList.add('rr-valid-contract-star');
                        }
                        if (this.cardDefMgr.isContractFilled(id, selectedHandCardIds, false)) {
                            e.classList.add('rr-valid-contract');
                        }
                    }
                    let isValid = false;
                    let usesJoker = false;
                    if (selectedContractId !== null) {
                        if (this.cardDefMgr.isContractFilled(selectedContractId, selectedHandCardIds, false)) {
                            isValid = true;
                            usesJoker = false;
                        }
                        if (!isValid && this.isTrue(args.hasJoker)) {
                            if (this.cardDefMgr.isContractFilled(selectedContractId, selectedHandCardIds, true)) {
                                isValid = true;
                                usesJoker = true;
                            }
                        }
                    }
                    this.setTopButtonValid(BUTTON_ID_SELECT, isValid);
                    const handStar = this.tokenMgr.getHandStar();
                    this.removeSelected(handStar);
                    if (isValid) {
                        if (usesJoker) {
                            setTitle(
                                this.format_string_recursive(
                                    _('Complete Contract using ${starImage}'),
                                    {
                                        'starImage': _('Star'),
                                    }
                                )
                            );
                            this.addSelected(handStar);
                        } else {
                            setTitle(_('Complete Contract'));
                        }
                    } else {
                        setTitle(SELECT_TITLE);
                    }
                };
                this.addTopButtonPrimaryWithValid(
                    BUTTON_ID_SELECT,
                    SELECT_TITLE,
                    _('You must select cards from your hand and a contract from the market'),
                    () => {
                        this.serverAction('contractChoose', { cardIds: selectedHandCardIds.join(','), contractCardId: selectedContractId });
                    }
                );
                for (const id of args.handCardIds) {
                    const e = this.cardMgr.getCardById(id);
                    this.addClickable(e, () => {
                        if (selectedHandCardIds.has(id)) {
                            this.removeSelected(e);
                            selectedHandCardIds.delete(id);
                        } else {
                            this.addSelected(e);
                            selectedHandCardIds.add(id);
                        }
                        updateValid();
                    });
                }
                for (const id of args.contractCardIds) {
                    const e = this.cardMgr.getCardById(id);
                    this.addClickable(e, () => {
                        if (selectedContractId !== null) {
                            const prevElem = this.cardMgr.getCardById(selectedContractId);
                            this.removeSelected(prevElem);
                            if (selectedContractId != id) {
                                this.addSelected(e);
                                selectedContractId = id;
                            } else {
                                selectedContractId = null;
                            }
                        } else {
                            this.addSelected(e);
                            selectedContractId = id;
                        }
                        updateValid();
                    });
                }
                this.setTopButtonValid(BUTTON_ID_SELECT, false);

                this.addTopButtonSecondary('rr-button-reset', _('Reset'), () => reset());

                this.addTopButtonImportant('rr-button-pass', _('Pass'), () => {
                    this.showConfirmDialogCondition(
                        _('Are you sure you want to pass? This cannot be undone.'),
                        selectedHandCardIds.size > 0 || selectedContractId !== null || this.mustConfirmActions()
                    ).then(() => {
                        this.serverAction('contractPass');
                    });
                });
            },

            onButtonsStateChooseNumberToDiscard(args) {
                debug('onButtonsStateChooseNumberToDiscard');
                debug(args);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }

                this.addTopButtonSelection(
                    this.format_string_recursive(
                        _('Discard ${nbCards} card(s)'),
                        {
                            nbCards: args.nbCards,
                        }
                    ),
                    this.format_string_recursive(
                        _('You must choose ${nbCards} card(s) to discard'),
                        {
                            nbCards: args.nbCards,
                        }
                    ),
                    {
                        ids: args.handCardIds,
                        onElement: (id) => this.cardMgr.getCardById(id),
                        onClick: (ids) => {
                            if (ids instanceof Array) {
                                ids = ids.join(',');
                            }
                            this.showConfirmDialogCondition(
                                _('Are you sure? This cannot be undone.'),
                                this.mustConfirmActions()
                            ).then(() => {
                                this.serverAction('numberDiscard', { cardIds: ids })
                            });
                        },
                    },
                    args.nbCards
                );
            },
        });
    });
