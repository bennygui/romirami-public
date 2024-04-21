{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- romirami implementation : © Guillaume Benny bennygui@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    romirami_romirami.tpl
-->
<div id='rr-area-full'>
    <div id='rr-area-trophy'></div>
    <div id='rr-area-market'>
        <div id='rr-market-number'>
            <div class='rr-card-container rr-for-deck'>
                <div class='rr-card' data-card-id='1999'></div>
                <div class='rr-card' data-card-id='1999'></div>
                <div class='rr-card' data-card-id='1999'></div>
                <div id='rr-market-number-counter' class='bx-counter'>0</div>
            </div>
            <div class='rr-card-container rr-for-card' data-location-id='0'></div>
            <div class='rr-card-container rr-for-card' data-location-id='1'></div>
            <div class='rr-card-container rr-for-card' data-location-id='2'></div>
            <div class='rr-card-container rr-for-card' data-location-id='3'></div>
            <div class='rr-card-container rr-for-card' data-location-id='4'></div>
            <div class='rr-card-container rr-for-card' data-location-id='5'></div>
        </div>
        <div id='rr-market-contract'>
            <div class='rr-card-container rr-for-deck'>
                <div class='rr-card' data-card-id='2999'></div>
                <div class='rr-card' data-card-id='2999'></div>
                <div class='rr-card' data-card-id='2999'></div>
            </div>
            <div class='rr-card-container rr-for-card rr-contract-help-visible' data-location-id='0'></div>
            <div class='rr-card-container rr-for-card rr-contract-help-visible' data-location-id='1'></div>
            <div class='rr-card-container rr-for-card rr-contract-help-visible' data-location-id='2'></div>
            <div class='rr-card-container rr-for-card rr-contract-help-visible' data-location-id='3'></div>
        </div>
    </div>
    <div id='rr-area-card-hand-container'>
        <div id='rr-area-hand-sort-contrainer'>
            <div class='rr-ui rr-ui-sort-draw'></div>
            <div class='rr-ui rr-ui-sort-kind'></div>
            <div class='rr-ui rr-ui-sort-suit'></div>
            <div class='rr-ui rr-ui-sort-sequence'></div>
        </div>
        <div id='rr-area-card-hand'>
            <div id='rr-hand-star' class="rr-token rr-token-star"></div>
        </div>
    </div>
    <div id='rr-area-player-container'>
        <!-- BEGIN player-area -->
        <div id='rr-area-player-{PLAYER_ID}' class='rr-area-player' data-player-id='{PLAYER_ID}'>
            <div class='rr-area-player-title'>
                <h3><span class='player-name' data-player-id='{PLAYER_ID}' style='color: #{PLAYER_COLOR};'>{PLAYER_NAME}</span></h3>
                <div class='bx-pill rr-counter-hand'>
                    <div class='rr-card-target'></div>
                    <div class='rr-ui rr-ui-number'></div>
                    <div class='bx-pill-counter'>0</div>
                </div>
                <div class='rr-star-container'></div>
                <div class='rr-first-player-container'></div>
            </div>
            <div class='rr-area-player-cards'>
                <div class='rr-player-container-number-wrap'>
                    <div class='rr-player-container-number'></div>
                    <div class='rr-player-number-count bx-counter'>0</div>
                </div>
                <div class='rr-player-container-contract'></div>
            </div>
            <div class='rr-area-player-trophy'></div>
        </div>
        <!-- END player-area -->
    </div>
    <div id='rr-area-pref'>
        <div id='rr-area-zoom'>
            <label class='bx-checkbox-switch'><span>{ZOOM_AUTO}</span><input type='checkbox'><i></i><span>{ZOOM_MANUAL}</span></label>
            <input id="rr-zoom-slider" type="range" min="20" max="100" step="5" disabled>
        </div>
        <div id='rr-area-pref-shortcut'>
            <label class='bx-checkbox-switch'><input type='checkbox' checked><i></i><span>{SHORTCUTS}</span></label>
        </div>
        <div id='rr-area-pref-background'>
            <label class='bx-checkbox-switch'><input type='checkbox'><i></i><span>{BACKGROUND_DARK}</span></label>
        </div>
        <div id='rr-area-pref-always-confirm'>
            <label class='bx-checkbox-switch'><input type='checkbox' checked='checked'><i></i><span>{CONFIRM}</span></label>
        </div>
        <div id='rr-switch-compact-contract'>
            <label class='bx-checkbox-switch'><input type='checkbox' checked='checked'><i></i><span>{COMPACT}</span></label>
        </div>
        <div id='rr-switch-contract-help'>
            <label class='bx-checkbox-switch'><input type='checkbox' checked='checked'><i></i><span>{CONTRACT_HELP}</span></label>
        </div>
    </div>
</div>

<div id='rr-shortcut-area'></div>
<div id='rr-element-creation' class='bx-hidden'></div>

{OVERALL_GAME_FOOTER}