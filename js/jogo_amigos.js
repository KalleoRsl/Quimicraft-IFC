(function () {
    'use strict';

    var DESAFIO_ID = window.DESAFIO_ID || 0;
    var OPONENTE_NOME = window.OPONENTE_NOME || 'Amigo';

    var LEVELS = [
        {
            category: 'ÓXIDO',
            categoryKey: 'oxido',
            name: 'AGUA',
            label: 'Água',
            result: 'H₂O',
            reactants: ['H₂', 'O'],
            inventory: ['H₂', 'O', 'H', 'C'],
            time: 35,
            points: 80
        },
        {
            category: 'ÓXIDO',
            categoryKey: 'oxido',
            name: 'GAS CARBONICO',
            label: 'Gás carbônico (dióxido de carbono)',
            result: 'CO₂',
            reactants: ['C', 'O₂'],
            inventory: ['C', 'O₂', 'O', 'H₂', 'H'],
            time: 33,
            points: 85
        },
        {
            category: 'ÁCIDO',
            categoryKey: 'acido',
            name: 'ACIDO CLORIDRICO',
            label: 'Ácido clorídrico',
            result: 'HCl',
            reactants: ['H', 'Cl'],
            inventory: ['H', 'Cl', 'Na', 'OH', 'O'],
            time: 32,
            points: 90
        },
        {
            category: 'BASE',
            categoryKey: 'base',
            name: 'HIDROXIDO DE SODIO',
            label: 'Hidróxido de sódio',
            result: 'NaOH',
            reactants: ['Na', 'OH'],
            inventory: ['Na', 'OH', 'H', 'Cl', 'O', 'H₂'],
            time: 30,
            points: 95
        },
        {
            category: 'SAL',
            categoryKey: 'sal',
            name: 'CLORETO DE SODIO',
            label: 'Cloreto de sódio (sal de cozinha)',
            result: 'NaCl',
            reactants: ['Na', 'Cl'],
            inventory: ['Na', 'Cl', 'K', 'OH', 'H', 'O'],
            time: 28,
            points: 100
        },
        {
            category: 'ÓXIDO',
            categoryKey: 'oxido',
            name: 'MONOXIDO DE CARBONO',
            label: 'Monóxido de carbono',
            result: 'CO',
            reactants: ['C', 'O'],
            inventory: ['C', 'O', 'O₂', 'CO₂', 'H₂', 'Na', 'Cl'],
            time: 27,
            points: 105
        },
        {
            category: 'ÁCIDO',
            categoryKey: 'acido',
            name: 'ACIDO NITRICO',
            label: 'Ácido nítrico',
            result: 'HNO₃',
            reactants: ['H', 'NO₃'],
            inventory: ['H', 'NO₃', 'Cl', 'OH', 'Na', 'SO₄'],
            time: 26,
            points: 110
        },
        {
            category: 'BASE',
            categoryKey: 'base',
            name: 'HIDROXIDO DE POTASSIO',
            label: 'Hidróxido de potássio',
            result: 'KOH',
            reactants: ['K', 'OH'],
            inventory: ['K', 'OH', 'Na', 'Cl', 'H', 'NO₃', 'Ca'],
            time: 25,
            points: 115
        },
        {
            category: 'SAL',
            categoryKey: 'sal',
            name: 'CLORETO DE POTASSIO',
            label: 'Cloreto de potássio',
            result: 'KCl',
            reactants: ['K', 'Cl'],
            inventory: ['K', 'Cl', 'Na', 'OH', 'NO₃', 'Ca', 'Br'],
            time: 24,
            points: 120
        },
        {
            category: 'ÓXIDO',
            categoryKey: 'oxido',
            name: 'OXIDO DE CALCIO',
            label: 'Óxido de cálcio (cal viva)',
            result: 'CaO',
            reactants: ['Ca', 'O'],
            inventory: ['Ca', 'O', 'O₂', 'OH', 'Cl', 'Na', 'K', 'CO₂'],
            time: 22,
            points: 125
        }
    ];

    var currentLevel = 0;
    var timerInterval = null;
    var timeLeft = 30;
    var gameLocked = false;
    var gameEnded = false;
    var score = 0;
    var niveisCompletados = 0;
    var scoreSaved = false;

    var slots = {
        1: null,
        2: null
    };

    var elTimer = document.getElementById('timer');
    var elLevel = document.getElementById('level-label');
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
    var elFinalResult = document.getElementById('final-result');

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
        if (timeLeft <= 10) {
            elTimer.classList.add('solo-timer-warning');
        } else {
            elTimer.classList.remove('solo-timer-warning');
        }
    }

    function updateScoreDisplay() {
        elScore.textContent = String(score);
    }

    function startTimer(seconds) {
        clearTimer();
        timeLeft = seconds;
        updateTimerDisplay();
        timerInterval = setInterval(function () {
            timeLeft -= 1;
            updateTimerDisplay();
            if (timeLeft <= 0) {
                clearTimer();
                onTimeUp();
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
            e.dataTransfer.setData('application/x-amigos-source', options.source || 'inventory');
            if (options.slot) {
                e.dataTransfer.setData('application/x-amigos-slot', String(options.slot));
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
            var source = e.dataTransfer.getData('application/x-amigos-source');
            var fromSlot = e.dataTransfer.getData('application/x-amigos-slot');

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
        clearTimer();
        niveisCompletados += 1;
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
            advanceLevel();
        }, 2200);
    }

    function advanceLevel() {
        if (gameEnded) return;

        currentLevel += 1;

        if (currentLevel >= LEVELS.length) {
            onBattleEnd();
            return;
        }

        loadLevel(currentLevel);
    }

    function saveScore(callback) {
        if (scoreSaved) {
            if (callback) callback(null);
            return;
        }

        scoreSaved = true;

        fetch('salvar_pontuacao_amigos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_desafio: DESAFIO_ID,
                pontuacao: score,
                niveis: niveisCompletados
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

    function showEndOverlay(title, text, resultText, resultClass) {
        gameEnded = true;
        gameLocked = true;
        clearTimer();

        elOverlayTitle.textContent = title;
        elOverlayText.textContent = text;
        elFinalScore.textContent = String(score);
        elFinalDetail.textContent = niveisCompletados + (niveisCompletados === 1 ? ' nível completado' : ' níveis completados');

        elFinalResult.classList.remove('hidden', 'amigos-result-win', 'amigos-result-lose', 'amigos-result-wait', 'amigos-result-draw');
        if (resultClass) {
            elFinalResult.classList.add(resultClass);
        }
        elFinalResult.textContent = resultText;

        elOverlayActions.innerHTML = '';
        elOverlay.classList.remove('hidden');
    }

    function addEndButtons() {
        var btnLobby = document.createElement('a');
        btnLobby.href = 'jogo_amigos.php';
        btnLobby.className = 'solo-modal-btn';
        btnLobby.textContent = 'Voltar aos desafios';

        var btnMenu = document.createElement('a');
        btnMenu.href = 'principal.php';
        btnMenu.className = 'solo-modal-btn solo-modal-btn-secondary';
        btnMenu.textContent = 'Menu principal';

        elOverlayActions.appendChild(btnLobby);
        elOverlayActions.appendChild(btnMenu);
    }

    function handleSaveResult(data) {
        if (!data || !data.ok) {
            elFinalResult.textContent = 'Erro ao salvar. Tente novamente.';
            elOverlayActions.innerHTML = '';
            var btnRetry = document.createElement('button');
            btnRetry.type = 'button';
            btnRetry.className = 'solo-modal-btn';
            btnRetry.textContent = 'Tentar salvar';
            btnRetry.addEventListener('click', function () {
                scoreSaved = false;
                saveScore(handleSaveResult);
            });
            elOverlayActions.appendChild(btnRetry);
            return;
        }

        var resultText = '';
        var resultClass = '';

        if (data.resultado === 'vitoria') {
            resultText = 'Vitória! Você venceu ' + OPONENTE_NOME + ' (' + score + ' x ' + data.pontuacao_oponente + ')';
            resultClass = 'amigos-result-win';
        } else if (data.resultado === 'derrota') {
            resultText = 'Derrota! ' + OPONENTE_NOME + ' venceu (' + data.pontuacao_oponente + ' x ' + score + ')';
            resultClass = 'amigos-result-lose';
        } else if (data.resultado === 'empate') {
            resultText = 'Empate com ' + OPONENTE_NOME + '! (' + score + ' x ' + data.pontuacao_oponente + ')';
            resultClass = 'amigos-result-draw';
        } else {
            resultText = 'Aguardando ' + OPONENTE_NOME + ' jogar...';
            resultClass = 'amigos-result-wait';
        }

        elFinalResult.classList.remove('amigos-result-win', 'amigos-result-lose', 'amigos-result-wait', 'amigos-result-draw');
        elFinalResult.classList.add(resultClass);
        elFinalResult.textContent = resultText;

        elOverlayActions.innerHTML = '';
        addEndButtons();
    }

    function onBattleEnd() {
        showEndOverlay(
            'Batalha encerrada!',
            'Você completou os 10 níveis. Confira o resultado:',
            'Salvando pontuação...',
            'amigos-result-wait'
        );

        saveScore(handleSaveResult);
    }

    function onTimeUp() {
        gameLocked = true;
        elOverlayTitle.textContent = 'Tempo esgotado!';
        elOverlayText.textContent = 'O tempo acabou antes de formar ' + LEVELS[currentLevel].label + '.';
        elFinalScore.textContent = String(score);
        elFinalDetail.textContent = niveisCompletados + (niveisCompletados === 1 ? ' nível completado' : ' níveis completados');
        elFinalResult.classList.add('hidden');
        elOverlayActions.innerHTML = '';

        var btnRetry = document.createElement('button');
        btnRetry.type = 'button';
        btnRetry.className = 'solo-modal-btn';
        btnRetry.textContent = 'Tentar novamente';
        btnRetry.addEventListener('click', retryLevel);

        var btnRestart = document.createElement('button');
        btnRestart.type = 'button';
        btnRestart.className = 'solo-modal-btn solo-modal-btn-secondary';
        btnRestart.textContent = 'Reiniciar fase';
        btnRestart.addEventListener('click', restartLevel);

        var btnEnd = document.createElement('button');
        btnEnd.type = 'button';
        btnEnd.className = 'solo-modal-btn solo-modal-btn-secondary';
        btnEnd.textContent = 'Encerrar batalha';
        btnEnd.addEventListener('click', onBattleEnd);

        elOverlayActions.appendChild(btnRetry);
        elOverlayActions.appendChild(btnRestart);
        elOverlayActions.appendChild(btnEnd);
        elOverlay.classList.remove('hidden');
    }

    function hideOverlay() {
        elOverlay.classList.add('hidden');
    }

    function retryLevel() {
        hideOverlay();
        loadLevel(currentLevel);
    }

    function restartLevel() {
        hideOverlay();
        resetSlots();
        gameLocked = false;
        startTimer(LEVELS[currentLevel].time);
        renderInventory(LEVELS[currentLevel].inventory.slice());
    }

    function loadLevel(index) {
        gameLocked = false;
        var level = LEVELS[index];

        elLevel.textContent = 'NIVEL: ' + (index + 1);
        elTargetCategory.textContent = level.category;
        elTargetCategory.className = 'solo-target-category solo-cat-' + level.categoryKey;
        elTarget.textContent = level.name;
        elTargetHint.textContent = 'Forme: ' + level.label + ' (+' + level.points + ' pts)';
        resetSlots();
        renderInventory(level.inventory.slice());
        startTimer(level.time);
    }

    setupDropZone(document.getElementById('slot-1'), 1);
    setupDropZone(document.getElementById('slot-2'), 2);
    updateScoreDisplay();
    loadLevel(0);
})();
