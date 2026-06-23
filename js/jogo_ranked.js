(function () {
    'use strict';

    var MATCH_TIME = 60;
    var SUCCESS_DELAY = 1200;

    var LEVELS = [
        {
            category: 'ÓXIDO',
            categoryKey: 'oxido',
            name: 'PEROXIDO DE HIDROGENIO',
            label: 'Peróxido de hidrogênio',
            result: 'H₂O₂',
            reactants: ['H₂', 'O₂'],
            inventory: ['H₂', 'O₂', 'O', 'H', 'N'],
            points: 80
        },
        {
            category: 'ÓXIDO',
            categoryKey: 'oxido',
            name: 'OXIDO DE MAGNESIO',
            label: 'Óxido de magnésio',
            result: 'MgO',
            reactants: ['Mg', 'O'],
            inventory: ['Mg', 'O', 'O₂', 'Ca', 'Na', 'H'],
            points: 85
        },
        {
            category: 'ÓXIDO',
            categoryKey: 'oxido',
            name: 'DIOXIDO DE ENXOFRE',
            label: 'Dióxido de enxofre',
            result: 'SO₂',
            reactants: ['S', 'O₂'],
            inventory: ['S', 'O₂', 'O', 'C', 'N', 'H₂'],
            points: 85
        },
        {
            category: 'ÓXIDO',
            categoryKey: 'oxido',
            name: 'OXIDO FERROSO',
            label: 'Óxido ferroso',
            result: 'FeO',
            reactants: ['Fe', 'O'],
            inventory: ['Fe', 'O', 'O₂', 'Cu', 'Mg', 'Cl'],
            points: 90
        },
        {
            category: 'ÁCIDO',
            categoryKey: 'acido',
            name: 'ACIDO SULFURICO',
            label: 'Ácido sulfúrico',
            result: 'H₂SO₄',
            reactants: ['H', 'SO₄'],
            inventory: ['H', 'SO₄', 'Cl', 'NO₃', 'OH', 'Na'],
            points: 95
        },
        {
            category: 'ÁCIDO',
            categoryKey: 'acido',
            name: 'ACIDO BROMIDRICO',
            label: 'Ácido bromídrico',
            result: 'HBr',
            reactants: ['H', 'Br'],
            inventory: ['H', 'Br', 'Cl', 'I', 'Na', 'K'],
            points: 95
        },
        {
            category: 'BASE',
            categoryKey: 'base',
            name: 'HIDROXIDO DE BARIO',
            label: 'Hidróxido de bário',
            result: 'Ba(OH)₂',
            reactants: ['Ba', 'OH'],
            inventory: ['Ba', 'OH', 'Na', 'K', 'Ca', 'Cl'],
            points: 100
        },
        {
            category: 'BASE',
            categoryKey: 'base',
            name: 'HIDROXIDO DE CALCIO',
            label: 'Hidróxido de cálcio',
            result: 'Ca(OH)₂',
            reactants: ['Ca', 'OH'],
            inventory: ['Ca', 'OH', 'O', 'Mg', 'Na', 'H'],
            points: 100
        },
        {
            category: 'BASE',
            categoryKey: 'base',
            name: 'HIDROXIDO DE LITIO',
            label: 'Hidróxido de lítio',
            result: 'LiOH',
            reactants: ['Li', 'OH'],
            inventory: ['Li', 'OH', 'Na', 'K', 'H', 'Cl'],
            points: 105
        },
        {
            category: 'SAL',
            categoryKey: 'sal',
            name: 'CLORETO DE PRATA',
            label: 'Cloreto de prata',
            result: 'AgCl',
            reactants: ['Ag', 'Cl'],
            inventory: ['Ag', 'Cl', 'Na', 'Br', 'K', 'I'],
            points: 110
        },
        {
            category: 'SAL',
            categoryKey: 'sal',
            name: 'CLORETO DE CALCIO',
            label: 'Cloreto de cálcio',
            result: 'CaCl₂',
            reactants: ['Ca', 'Cl'],
            inventory: ['Ca', 'Cl', 'Na', 'K', 'Mg', 'Br'],
            points: 110
        },
        {
            category: 'ÓXIDO',
            categoryKey: 'oxido',
            name: 'OXIDO DE CHUMBO',
            label: 'Óxido de chumbo (II)',
            result: 'PbO',
            reactants: ['Pb', 'O'],
            inventory: ['Pb', 'O', 'O₂', 'Fe', 'Cu', 'Zn'],
            points: 115
        },
        {
            category: 'ÓXIDO',
            categoryKey: 'oxido',
            name: 'OXIDO DE ZINCO',
            label: 'Óxido de zinco',
            result: 'ZnO',
            reactants: ['Zn', 'O'],
            inventory: ['Zn', 'O', 'O₂', 'Mg', 'Fe', 'S'],
            points: 115
        },
        {
            category: 'BASE',
            categoryKey: 'base',
            name: 'HIDROXIDO DE MAGNESIO',
            label: 'Hidróxido de magnésio',
            result: 'Mg(OH)₂',
            reactants: ['Mg', 'OH'],
            inventory: ['Mg', 'OH', 'Ca', 'Na', 'Ba', 'Cl'],
            points: 120
        },
        {
            category: 'SAL',
            categoryKey: 'sal',
            name: 'CLORETO DE AMONIO',
            label: 'Cloreto de amônio',
            result: 'NH₄Cl',
            reactants: ['NH₄', 'Cl'],
            inventory: ['NH₄', 'Cl', 'Na', 'K', 'Br', 'OH', 'H'],
            points: 125
        }
    ];

    var currentLevel = 0;
    var timerInterval = null;
    var timeLeft = MATCH_TIME;
    var gameLocked = false;
    var gameEnded = false;
    var score = 0;
    var compostosCompletados = 0;
    var scoreSaved = false;

    var slots = {
        1: null,
        2: null
    };

    var elTimer = document.getElementById('timer');
    var elPhase = document.getElementById('phase-label');
    var elScore = document.getElementById('score');
    var elTarget = document.getElementById('target-name');
    var elTargetCategory = document.getElementById('target-category');
    var elTargetHint = document.getElementById('target-hint');
    var elInventory = document.getElementById('inventory');
    var elOverlay = document.getElementById('overlay');
    var elOverlayTitle = document.getElementById('overlay-title');
    var elOverlayText = document.getElementById('overlay-text');
    var elOverlayActions = document.getElementById('overlay-actions');
    var elSuccess = document.getElementById('success-banner');
    var elSuccessText = document.getElementById('success-text');
    var elSlotResult = document.getElementById('slot-result');
    var elFinalScore = document.getElementById('final-score');
    var elFinalDetail = document.getElementById('final-detail');
    var elFinalRecord = document.getElementById('final-record');

    function pad(n) {
        return n < 10 ? '0' + n : String(n);
    }

    function formatTime(seconds) {
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return pad(m) + ':' + pad(s);
    }

    function normalizePair(a, b) {
        return [a, b].sort().join('|');
    }

    function getExpectedPair(level) {
        return normalizePair(level.reactants[0], level.reactants[1]);
    }

    function clearTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }

    function updateTimerDisplay() {
        elTimer.textContent = formatTime(timeLeft);
        if (timeLeft <= 15) {
            elTimer.classList.add('solo-timer-warning');
        } else {
            elTimer.classList.remove('solo-timer-warning');
        }
    }

    function updateScoreDisplay() {
        elScore.textContent = String(score);
    }

    function startMatchTimer() {
        clearTimer();
        timeLeft = MATCH_TIME;
        updateTimerDisplay();
        timerInterval = setInterval(function () {
            timeLeft -= 1;
            updateTimerDisplay();
            if (timeLeft <= 0) {
                clearTimer();
                onMatchEnd();
            }
        }, 1000);
    }

    function resetSlots() {
        slots[1] = null;
        slots[2] = null;
        [1, 2].forEach(function (n) {
            var zone = document.getElementById('slot-' + n);
            zone.innerHTML = '<span class="solo-drop-hint">?</span>';
            zone.classList.remove('solo-drop-zone-filled');
        });
        elSlotResult.innerHTML = '<span class="solo-drop-hint">?</span>';
        elSlotResult.classList.remove('solo-drop-zone-filled', 'solo-drop-zone-success');
    }

    function createAtomElement(symbol, options) {
        options = options || {};
        var el = document.createElement('div');
        el.className = 'solo-atom';
        el.textContent = symbol;
        el.draggable = true;
        el.dataset.symbol = symbol;

        if (options.inSlot) {
            el.classList.add('solo-atom-in-slot');
        }

        el.addEventListener('dragstart', function (e) {
            if (gameLocked || gameEnded) {
                e.preventDefault();
                return;
            }
            e.dataTransfer.setData('text/plain', symbol);
            e.dataTransfer.setData('application/x-ranked-source', options.source || 'inventory');
            if (options.slot) {
                e.dataTransfer.setData('application/x-ranked-slot', String(options.slot));
            }
            el.classList.add('solo-atom-dragging');
        });

        el.addEventListener('dragend', function () {
            el.classList.remove('solo-atom-dragging');
        });

        return el;
    }

    function renderInventory(symbols) {
        elInventory.innerHTML = '';
        symbols.forEach(function (symbol) {
            var atom = createAtomElement(symbol, { source: 'inventory' });
            elInventory.appendChild(atom);
        });
    }

    function fillSlot(slotNum, symbol) {
        var zone = document.getElementById('slot-' + slotNum);
        zone.innerHTML = '';
        zone.classList.add('solo-drop-zone-filled');
        var atom = createAtomElement(symbol, { source: 'slot', slot: slotNum, inSlot: true });
        zone.appendChild(atom);
        slots[slotNum] = symbol;
        checkAnswer();
    }

    function returnAtomToInventory(symbol) {
        var existing = Array.from(elInventory.querySelectorAll('.solo-atom')).map(function (a) {
            return a.dataset.symbol;
        });
        if (existing.indexOf(symbol) === -1) {
            elInventory.appendChild(createAtomElement(symbol, { source: 'inventory' }));
        }
    }

    function setupDropZone(zone, slotNum) {
        zone.addEventListener('dragover', function (e) {
            if (gameLocked || gameEnded) return;
            e.preventDefault();
            zone.classList.add('solo-drop-zone-hover');
        });

        zone.addEventListener('dragleave', function () {
            zone.classList.remove('solo-drop-zone-hover');
        });

        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('solo-drop-zone-hover');
            if (gameLocked || gameEnded) return;

            var symbol = e.dataTransfer.getData('text/plain');
            var source = e.dataTransfer.getData('application/x-ranked-source');
            var fromSlot = e.dataTransfer.getData('application/x-ranked-slot');

            if (!symbol) return;

            if (source === 'slot' && fromSlot) {
                if (Number(fromSlot) === slotNum) return;
                slots[Number(fromSlot)] = null;
                var oldZone = document.getElementById('slot-' + fromSlot);
                oldZone.innerHTML = '<span class="solo-drop-hint">?</span>';
                oldZone.classList.remove('solo-drop-zone-filled');
            } else if (source === 'inventory') {
                var invAtom = elInventory.querySelector('[data-symbol="' + symbol + '"]');
                if (invAtom) invAtom.remove();
            }

            if (slots[slotNum]) {
                returnAtomToInventory(slots[slotNum]);
            }

            fillSlot(slotNum, symbol);
        });
    }

    function checkAnswer() {
        if (!slots[1] || !slots[2] || gameEnded) return;

        var level = LEVELS[currentLevel];
        var playerPair = normalizePair(slots[1], slots[2]);

        if (playerPair === getExpectedPair(level)) {
            onLevelSuccess(level);
        }
    }

    function onLevelSuccess(level) {
        gameLocked = true;
        compostosCompletados += 1;
        score += level.points;
        updateScoreDisplay();

        elSlotResult.innerHTML = '';
        elSlotResult.classList.add('solo-drop-zone-filled', 'solo-drop-zone-success');
        var resultAtom = createAtomElement(level.result, { inSlot: true });
        resultAtom.draggable = false;
        elSlotResult.appendChild(resultAtom);

        elSuccessText.textContent = 'Sucesso! ' + level.result + ' — +' + level.points + ' pts';
        elSuccess.classList.remove('hidden');

        setTimeout(function () {
            elSuccess.classList.add('hidden');
            advancePhase();
        }, SUCCESS_DELAY);
    }

    function advancePhase() {
        if (gameEnded) return;

        currentLevel = (currentLevel + 1) % LEVELS.length;
        loadPhase(currentLevel);
    }

    function saveScore(callback) {
        if (scoreSaved) {
            if (callback) callback(null);
            return;
        }

        scoreSaved = true;

        fetch('salvar_pontuacao_ranked.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                pontuacao: score,
                compostos: compostosCompletados
            })
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (callback) callback(data);
            })
            .catch(function () {
                if (callback) callback({ ok: false });
            });
    }

    function onMatchEnd() {
        gameEnded = true;
        gameLocked = true;

        elOverlayTitle.textContent = 'Tempo esgotado!';
        elOverlayText.textContent = 'Partida encerrada. Confira sua pontuação abaixo.';
        elFinalScore.textContent = String(score);
        elFinalDetail.textContent = compostosCompletados + (compostosCompletados === 1 ? ' composto formado' : ' compostos formados');
        elFinalRecord.classList.add('hidden');

        elOverlayActions.innerHTML = '';
        elOverlay.classList.remove('hidden');

        saveScore(function (data) {
            if (data && data.ok && data.novo_recorde) {
                elFinalRecord.classList.remove('hidden');
            }

            elOverlayActions.innerHTML = '';

            var btnRanking = document.createElement('a');
            btnRanking.href = 'ranking.php';
            btnRanking.className = 'solo-modal-btn';
            btnRanking.textContent = 'Ver ranking';

            var btnReplay = document.createElement('button');
            btnReplay.type = 'button';
            btnReplay.className = 'solo-modal-btn solo-modal-btn-secondary';
            btnReplay.textContent = 'Jogar novamente';
            btnReplay.addEventListener('click', restartMatch);

            var btnMenu = document.createElement('a');
            btnMenu.href = 'principal.php';
            btnMenu.className = 'solo-modal-btn solo-modal-btn-secondary';
            btnMenu.textContent = 'Voltar ao menu';

            elOverlayActions.appendChild(btnRanking);
            elOverlayActions.appendChild(btnReplay);
            elOverlayActions.appendChild(btnMenu);
        });
    }

    function hideOverlay() {
        elOverlay.classList.add('hidden');
    }

    function restartMatch() {
        hideOverlay();
        currentLevel = 0;
        score = 0;
        compostosCompletados = 0;
        scoreSaved = false;
        gameEnded = false;
        gameLocked = false;
        updateScoreDisplay();
        elFinalRecord.classList.add('hidden');
        loadPhase(0);
        startMatchTimer();
    }

    function loadPhase(index) {
        gameLocked = false;
        var level = LEVELS[index];

        elPhase.textContent = 'FASE: ' + (compostosCompletados + 1);
        elTargetCategory.textContent = level.category;
        elTargetCategory.className = 'solo-target-category solo-cat-' + level.categoryKey;
        elTarget.textContent = level.name;
        elTargetHint.textContent = 'Forme: ' + level.label + ' (+' + level.points + ' pts)';
        resetSlots();
        renderInventory(level.inventory.slice());
    }

    function initGame() {
        setupDropZone(document.getElementById('slot-1'), 1);
        setupDropZone(document.getElementById('slot-2'), 2);
        updateScoreDisplay();
        loadPhase(0);
        startMatchTimer();
    }

    initGame();
})();
