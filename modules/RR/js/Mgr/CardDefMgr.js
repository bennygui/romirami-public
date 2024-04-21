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
        return declare("rr.CardDefMgr", null, {
            CARD_SUIT_CHERRY: 1,
            CARD_SUIT_DIAMOND: 2,
            CARD_SUIT_HEART: 3,
            CARD_SUIT_CLOVER: 4,

            MARKET_MAX_NUMBER_CHOICE: 3,
            CARD_ID_JOKER: -1,

            setup(gamedatas) {
                this.cardDefs = gamedatas.cardDefs;
            },

            cardIsNumber(cardId) {
                return (cardId in this.cardDefs && this.cardDefs[cardId].number !== null);
            },

            cardIsContract(cardId) {
                return (cardId in this.cardDefs && this.cardDefs[cardId].number === null);
            },

            cardIsJoker(cardId) {
                return (cardId == this.CARD_ID_JOKER);
            },

            cardHasMatchingNumber(firstId, otherId) {
                if (this.cardIsJoker(firstId) || this.cardIsJoker(otherId)) {
                    return true;
                }
                return (this.cardDefs[firstId].number == this.cardDefs[otherId].number);
            },

            cardHasMatchingSuit(firstId, otherId) {
                if (this.cardIsJoker(firstId) || this.cardIsJoker(otherId)) {
                    return true;
                }
                return (this.cardDefs[firstId].suit == this.cardDefs[otherId].suit);
            },

            contractHasMatchingNumber(contract, cardId) {
                if (this.cardIsJoker(cardId)) {
                    return true;
                }
                return (contract.number === null || contract.number == this.cardDefs[cardId].number);
            },

            cardHasNextNumber(firstId, otherId) {
                if (this.cardIsJoker(firstId) || this.cardIsJoker(otherId)) {
                    return true;
                }
                if (!(firstId in this.cardDefs)) {
                    console.error('cardHasNextNumber firstId is invalid: ' + firstId);
                }
                if (!(otherId in this.cardDefs)) {
                    console.error('cardHasNextNumber firstId is invalid: ' + otherId);
                }
                return (this.cardDefs[firstId].number == this.cardDefs[otherId].number + 1);
            },

            cardHasNextNextNumber(firstId, otherId) {
                if (this.cardIsJoker(firstId) || this.cardIsJoker(otherId)) {
                    return true;
                }
                return (this.cardDefs[firstId].number == this.cardDefs[otherId].number + 2);
            },

            totalCardsForContracts(cardId) {
                return this.cardDefs[cardId].contracts.map((c) => c.count).reduce((sum, c) => sum + c, 0);
            },

            sortNumberCardByKind(cardIds) {
                cardIds.sort((a, b) => {
                    let cmp = this.cardDefs[a].number - this.cardDefs[b].number;
                    if (cmp == 0) {
                        cmp = this.cardDefs[a].suit - this.cardDefs[b].suit;
                    }
                    return cmp;
                });
            },

            sortNumberCardBySuit(cardIds) {
                cardIds.sort((a, b) => {
                    let cmp = this.cardDefs[a].suit - this.cardDefs[b].suit;
                    if (cmp == 0) {
                        cmp = this.cardDefs[a].number - this.cardDefs[b].number;
                    }
                    return cmp;
                });
            },

            sortNumberCardBySequence(cardIds) {
                const createSequence = (cardDefs) => {
                    return {
                        cardDefs: cardDefs,
                        calcCost: null,
                        suitCount: {},
                        numbers: {},

                        clone() {
                            const s = createSequence();
                            s.cardDefs = this.cardDefs;
                            s.calcCost = this.calcCost;
                            s.suitCount = Object.assign({}, this.suitCount);
                            s.numbers = Object.assign({}, this.numbers);
                            return s;
                        },

                        resetCache() {
                            this.calcCost = null;
                            this.suitCount = {};
                        },

                        initCache() {
                            if (this.calcCost !== null) {
                                return;
                            }
                            this.suitCount = {};
                            for (const n in this.numbers) {
                                const suit = this.numbers[n].suit;
                                if (!(suit in this.suitCount)) {
                                    this.suitCount[this.numbers[n].suit] = 0;
                                }
                                this.suitCount[this.numbers[n].suit] += 1;
                            }
                            this.calcCost = 0;
                            for (let n = 1; n <= 5; ++n) {
                                if (!(n in this.numbers)) {
                                    continue;
                                }
                                const count = this.suitCount[this.numbers[n].suit];
                                this.calcCost += count * count;
                                if (
                                    (n - 1) in this.numbers
                                    && this.numbers[n].suit == this.numbers[n - 1].suit
                                ) {
                                    this.calcCost += count;
                                }
                                if (
                                    (n + 1) in this.numbers
                                    && this.numbers[n].suit == this.numbers[n + 1].suit
                                ) {
                                    this.calcCost += count;
                                }
                            }
                        },

                        setDef(def) {
                            this.resetCache();
                            this.numbers[def.number] = def;
                        },

                        getDef(n) {
                            if (n in this.numbers) {
                                return this.numbers[n];
                            }
                            return null;
                        },

                        maxSuitCount() {
                            this.initCache();
                            return Object.values(this.suitCount).reduce((a, b) => Math.max(a, b), 0);
                        },

                        mostFrequentSuit() {
                            this.initCache();
                            const entries = Object.entries(this.suitCount);
                            if (entries.length == 0) {
                                return 0;
                            }
                            entries.sort((a, b) => {
                                let cmp = b[1] - a[1];
                                if (cmp != 0) {
                                    return cmp;
                                }
                                return a[0] - b[0];
                            });
                            return entries[0][0];
                        },

                        cost() {
                            this.initCache();
                            return this.calcCost;
                        },

                        size() {
                            return Object.keys(this.numbers).length;
                        },
                    };
                };

                // Build sequences
                const sequences = [];
                while (cardIds.length > 0) {
                    const s = createSequence(this.cardDefs);
                    for (let n = 1; n <= 5; ++n) {
                        for (let i in cardIds) {
                            const id = cardIds[i];
                            if (this.cardDefs[id].number == n) {
                                cardIds.splice(i, 1);
                                s.setDef(this.cardDefs[id]);
                                break;
                            }
                        }
                    }
                    sequences.push(s);
                }

                // Improve sequences
                let costImproved = false;
                do {
                    costImproved = false;
                    for (let idx1 in sequences) {
                        let s1 = sequences[idx1];
                        if (s1.size() == 1) {
                            continue;
                        }
                        for (let idx2 in sequences) {
                            let s2 = sequences[idx2];
                            if (idx1 == idx2) {
                                continue;
                            }
                            for (let n = 1; n <= 5; ++n) {
                                const d1 = s1.getDef(n);
                                const d2 = s2.getDef(n);
                                if (d1 == null || d2 == null || d1.suit == d2.suit) {
                                    continue;
                                }
                                const prevCost = s1.cost() + s2.cost();
                                const s1c = s1.clone();
                                const s2c = s2.clone();
                                s1c.setDef(d2);
                                s2c.setDef(d1);
                                if (s1c.cost() + s2c.cost() <= prevCost) {
                                    continue;
                                }
                                s1 = s1c;
                                sequences[idx1] = s1c;
                                s2 = s2c;
                                sequences[idx2] = s2c;
                                costImproved = true;
                            }
                        }
                    }
                } while (costImproved);

                // Sort sequences by size, suit length and then by most frequent suit
                sequences.sort((s1, s2) => {
                    let cmp = s2.size() - s1.size();
                    if (cmp != 0) {
                        return cmp;
                    }
                    cmp = s2.maxSuitCount() - s1.maxSuitCount();
                    if (cmp != 0) {
                        return cmp;
                    }
                    return s1.mostFrequentSuit() - s2.mostFrequentSuit();
                });

                // Rebuild cardIds array
                for (const s of sequences) {
                    for (let n = 1; n <= 5; ++n) {
                        const d = s.getDef(n);
                        if (d !== null) {
                            cardIds.push(d.cardId);
                        }
                    }
                }
            },

            isNumberChoiceFromMarketValid(numberCardIds) {
                numberCardIds = Array.from(numberCardIds);
                const count = numberCardIds.length;
                if (count <= 0 || count > this.MARKET_MAX_NUMBER_CHOICE) {
                    return false;
                }
                if (count == 1) {
                    return true;
                }
                const firstId = numberCardIds.shift();

                let matchNumber = true;
                let matchSuit = true;
                for (const otherCardId of numberCardIds) {
                    if (!this.cardHasMatchingNumber(firstId, otherCardId)) {
                        matchNumber = false;
                    }
                    if (!this.cardHasMatchingSuit(firstId, otherCardId)) {
                        matchSuit = false;
                    }
                }
                return (matchNumber || matchSuit);
            },

            uniqueNumbers(numberCardIds) {
                const numbers = [];
                for (const cardId of numberCardIds) {
                    numbers.push(this.cardDefs[cardId].number);
                }
                if ((new Set(numbers)).size == 1) {
                    return [numbers[0]];
                }
                numbers.sort();
                return numbers;
            },

            uniqueSuits(numberCardIds) {
                const suits = [];
                for (const cardId of numberCardIds) {
                    suits.push(this.cardDefs[cardId].suit);
                }
                if ((new Set(suits)).size == 1) {
                    return [suits[0]];
                }
                suits.sort();
                return suits;
            },

            isContractFilled(contractCardId, numberCardIds, useJoker) {
                numberCardIds = Array.from(numberCardIds);
                const objCardIds = {};
                for (const cardId of numberCardIds) {
                    objCardIds[cardId] = true;
                }
                if (
                    !useJoker
                    && this.totalCardsForContracts(contractCardId) == numberCardIds.length
                    && this.isContractFilledImpl(this.cardDefs[contractCardId].contracts, 0, objCardIds, [])
                ) {
                    return true;
                }
                objCardIds[this.CARD_ID_JOKER] = true;
                if (
                    useJoker
                    && this.totalCardsForContracts(contractCardId) == numberCardIds.length + 1
                    && this.isContractFilledImpl(this.cardDefs[contractCardId].contracts, 0, objCardIds, [])
                ) {
                    return true;
                }
                return false;
            },

            isContractFilledImpl(contracts, contractIndex, objCardIds, selectedCardIds) {
                if (contractIndex >= contracts.length) {
                    return true;
                }
                const contract = contracts[contractIndex];
                if (selectedCardIds.length == contract.count) {
                    return this.isContractFilledImpl(contracts, contractIndex + 1, objCardIds, []);
                }
                for (const cardId in objCardIds) {
                    if (!this.contractHasMatchingNumber(contract, cardId)) {
                        continue;
                    }
                    if (selectedCardIds.length >= 1) {
                        const lastCardId = selectedCardIds[selectedCardIds.length - 1];
                        if (contract.isSequence) {
                            if (!this.cardHasNextNumber(cardId, lastCardId)) {
                                continue;
                            }
                        } else {
                            if (!this.cardHasMatchingNumber(cardId, lastCardId)) {
                                continue;
                            }
                        }
                    }
                    if (selectedCardIds.length >= 2) {
                        const lastLastCardId = selectedCardIds[selectedCardIds.length - 2];
                        if (contract.isSequence) {
                            if (!this.cardHasNextNextNumber(cardId, lastLastCardId)) {
                                continue;
                            }
                        } else {
                            if (!this.cardHasMatchingNumber(cardId, lastLastCardId)) {
                                continue;
                            }
                        }
                    }
                    const remainingObjCardIds = Object.assign({}, objCardIds)
                    delete remainingObjCardIds[cardId];
                    if (this.isContractFilledImpl(contracts, contractIndex, remainingObjCardIds, selectedCardIds.concat([cardId]))) {
                        return true;
                    }
                }
                return false;
            }
        });
    });
