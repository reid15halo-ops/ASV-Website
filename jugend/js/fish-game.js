/* ===================================
   FISCH FANG FRENZY - Mini Game
   ASV Petri Heil Jugend
   Global Leaderboard Edition
   =================================== */

// ===== GLOBAL LEADERBOARD =====
// Using localStorage + simulated global sync
// For true global storage, connect to Firebase/Supabase
let globalLeaderboard = [];
let isOnline = true;

// Load leaderboard from localStorage and simulate global
async function loadLeaderboard() {
    try {
        // Load from localStorage (works offline)
        const local = JSON.parse(localStorage.getItem('fishGameLeaderboard')) || [];

        // Pre-populate with some example scores if empty
        if (local.length === 0) {
            const exampleScores = [
                { name: 'ProAngler', score: 2500, fish: 45, combo: 12, date: '01.12.2024' },
                { name: 'FischKönig', score: 1800, fish: 35, combo: 8, date: '02.12.2024' },
                { name: 'Neptun', score: 1500, fish: 30, combo: 6, date: '03.12.2024' },
                { name: 'SeeMeister', score: 1200, fish: 25, combo: 5, date: '04.12.2024' },
                { name: 'WasserNinja', score: 900, fish: 20, combo: 4, date: '05.12.2024' }
            ];
            localStorage.setItem('fishGameLeaderboard', JSON.stringify(exampleScores));
            globalLeaderboard = exampleScores;
        } else {
            globalLeaderboard = local;
        }

        return globalLeaderboard;
    } catch (error) {
        console.error('Error loading leaderboard:', error);
        return [];
    }
}

// Save to leaderboard
async function saveToLeaderboard(entry) {
    globalLeaderboard.push(entry);
    globalLeaderboard.sort((a, b) => b.score - a.score);
    globalLeaderboard = globalLeaderboard.slice(0, 50); // Keep top 50

    // Persist to localStorage
    localStorage.setItem('fishGameLeaderboard', JSON.stringify(globalLeaderboard));

    return globalLeaderboard;
}

// Initialize
loadLeaderboard();

// ===== PROFANITY FILTER =====
const BANNED_WORDS = [
    // German profanity
    'arsch', 'scheiße', 'scheisse', 'fick', 'ficken', 'hurensohn', 'wichser',
    'schwanz', 'fotze', 'nutte', 'hure', 'bastard', 'idiot', 'dumm', 'behindert',
    'nazi', 'hitler', 'jude', 'neger', 'schwuchtel', 'missgeburt', 'spast',
    // English profanity  
    'fuck', 'shit', 'ass', 'bitch', 'damn', 'cunt', 'dick', 'pussy', 'nigger',
    'faggot', 'retard', 'whore', 'slut',
    // Illegal/extremist terms
    'bomb', 'terror', 'heil', 'sieg', 'kill', 'murder', 'rape'
];

function containsBannedWord(text) {
    const lowerText = text.toLowerCase().replace(/[0-9@$!%*?&]/g, '');
    return BANNED_WORDS.some(word => lowerText.includes(word));
}

function sanitizeName(name) {
    return name.replace(/[<>\"']/g, '').trim().substring(0, 20);
}

// ===== GAME STATE =====
let gameState = {
    isPlaying: false,
    isPaused: false,
    playerName: '',
    score: 0,
    fishCaught: 0,
    timeLeft: 60,
    combo: 0,
    maxCombo: 0,
    powerUps: {
        doublePoints: false,
        frenzy: false,
        magnet: false
    },
    upgrades: {
        rodLevel: 1,
        netSize: 1,
        luck: 1
    },
    coins: 0
};

// ===== FISH TYPES (Minecraft-style rarity) =====
const FISH_TYPES = [
    { name: 'Rotauge', emoji: '🐟', points: 10, rarity: 'common', color: '#87CEEB', speed: 3 },
    { name: 'Karpfen', emoji: '🐠', points: 25, rarity: 'uncommon', color: '#FFD700', speed: 2.5 },
    { name: 'Hecht', emoji: '🦈', points: 50, rarity: 'rare', color: '#32CD32', speed: 4 },
    { name: 'Zander', emoji: '🐡', points: 100, rarity: 'epic', color: '#9932CC', speed: 5 },
    { name: 'Goldkarpfen', emoji: '✨', points: 250, rarity: 'legendary', color: '#FF6347', speed: 6 }
];

// ===== POWER-UPS (Clash of Clans style) =====
const POWER_UPS = [
    { id: 'double', name: '2x Punkte', emoji: '⚡', duration: 5000, effect: 'doublePoints' },
    { id: 'frenzy', name: 'Fisch Frenzy', emoji: '🔥', duration: 3000, effect: 'frenzy' },
    { id: 'time', name: '+10 Sekunden', emoji: '⏰', duration: 0, effect: 'addTime' },
    { id: 'bomb', name: 'Fisch-Bombe', emoji: '💣', duration: 0, effect: 'bomb' }
];

// ===== GAME ELEMENTS =====
let gameContainer, canvas, ctx;
let fishes = [];
let powerUpItems = [];
let particles = [];
let gameLoop;
let spawnInterval;

// ===== INITIALIZE GAME =====
function initGame() {
    const gameSection = document.getElementById('fish-game');
    if (!gameSection) return;

    gameContainer = document.getElementById('game-container');
    canvas = document.getElementById('game-canvas');

    if (!canvas) return;

    ctx = canvas.getContext('2d');
    resizeCanvas();

    window.addEventListener('resize', resizeCanvas);
    canvas.addEventListener('click', handleClick);
    canvas.addEventListener('touchstart', handleTouch, { passive: false });

    renderStartScreen();
    renderLeaderboardPreview();
}

function resizeCanvas() {
    if (!canvas || !gameContainer) return;
    const rect = gameContainer.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = Math.min(400, window.innerHeight * 0.5);
}

// ===== START SCREEN =====
function renderStartScreen() {
    ctx.fillStyle = '#0a1628';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    drawWaterBackground();

    // Title
    ctx.fillStyle = '#00ff94';
    ctx.font = 'bold 32px Outfit';
    ctx.textAlign = 'center';
    ctx.fillText('🎣 FISCH FANG FRENZY 🎣', canvas.width / 2, 60);

    ctx.fillStyle = '#fff';
    ctx.font = '16px Outfit';
    ctx.fillText('Fang so viele Fische wie möglich!', canvas.width / 2, 100);

    // Instructions
    ctx.font = '14px Outfit';
    ctx.fillStyle = 'rgba(255,255,255,0.7)';
    ctx.fillText('Klicke auf Fische zum Fangen • Sammle Power-Ups', canvas.width / 2, 130);
    ctx.fillText('⚡ = 2x Punkte  |  🔥 = Fisch Frenzy  |  ⏰ = +Zeit', canvas.width / 2, 155);

    // Start Button
    const btnX = canvas.width / 2 - 80;
    const btnY = canvas.height - 80;

    ctx.beginPath();
    ctx.roundRect(btnX, btnY, 160, 50, 25);
    ctx.fillStyle = '#00ff94';
    ctx.fill();

    ctx.fillStyle = '#000';
    ctx.font = 'bold 18px Outfit';
    ctx.fillText('▶ SPIELEN', canvas.width / 2, btnY + 32);
}

function drawWaterBackground() {
    const time = Date.now() / 1000;
    ctx.fillStyle = 'rgba(0, 100, 150, 0.3)';

    for (let i = 0; i < 3; i++) {
        ctx.beginPath();
        ctx.moveTo(0, canvas.height);
        for (let x = 0; x < canvas.width; x += 10) {
            const y = canvas.height - 50 - i * 30 + Math.sin(x / 50 + time + i) * 15;
            ctx.lineTo(x, y);
        }
        ctx.lineTo(canvas.width, canvas.height);
        ctx.closePath();
        ctx.fill();
    }
}

// ===== LEADERBOARD PREVIEW (Always visible) =====
function renderLeaderboardPreview() {
    const previewContainer = document.getElementById('leaderboard-preview');
    if (!previewContainer) return;

    const top5 = globalLeaderboard.slice(0, 5);
    previewContainer.innerHTML = top5.map((e, i) => `
        <div class="lb-preview-entry">
            <span class="lb-preview-rank">${i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `#${i + 1}`}</span>
            <span class="lb-preview-name">${e.name}</span>
            <span class="lb-preview-score">${e.score}</span>
        </div>
    `).join('');
}

// ===== NAME INPUT =====
function showNameInput() {
    const nameInput = document.getElementById('player-name-input');
    const overlay = document.getElementById('game-overlay');
    if (nameInput && overlay) {
        overlay.classList.add('active');
        nameInput.focus();
    }
}

function submitName() {
    const nameInput = document.getElementById('player-name-input');
    let name = sanitizeName(nameInput.value);

    if (name.length < 2) {
        showGameMessage('Name muss mindestens 2 Zeichen haben!', 'error');
        return;
    }

    if (containsBannedWord(name)) {
        showGameMessage('Dieser Name ist nicht erlaubt!', 'error');
        nameInput.value = '';
        return;
    }

    gameState.playerName = name;
    document.getElementById('game-overlay').classList.remove('active');
    startGame();
}

function showGameMessage(message, type) {
    const msgEl = document.getElementById('game-message');
    if (msgEl) {
        msgEl.textContent = message;
        msgEl.className = 'game-message ' + type;
        msgEl.classList.add('show');
        setTimeout(() => msgEl.classList.remove('show'), 2000);
    }
}

// ===== START GAME =====
function startGame() {
    gameState = {
        ...gameState,
        isPlaying: true,
        score: 0,
        fishCaught: 0,
        timeLeft: 60,
        combo: 0,
        maxCombo: 0,
        powerUps: { doublePoints: false, frenzy: false, magnet: false },
        coins: 0
    };

    fishes = [];
    powerUpItems = [];
    particles = [];

    updateUI();

    // Start game loop
    gameLoop = setInterval(update, 1000 / 60);
    spawnInterval = setInterval(spawnFish, gameState.powerUps.frenzy ? 300 : 800);

    // Timer
    const timerInterval = setInterval(() => {
        if (!gameState.isPlaying) {
            clearInterval(timerInterval);
            return;
        }
        gameState.timeLeft--;
        updateUI();

        // Fortnite-style storm warning
        if (gameState.timeLeft === 10) {
            showGameMessage('⚠️ NUR NOCH 10 SEKUNDEN! ⚠️', 'warning');
        }

        if (gameState.timeLeft <= 0) {
            endGame();
            clearInterval(timerInterval);
        }
    }, 1000);

    // Spawn power-ups periodically
    setInterval(() => {
        if (gameState.isPlaying && Math.random() < 0.3) {
            spawnPowerUp();
        }
    }, 5000);
}

// ===== SPAWN FISH =====
function spawnFish() {
    if (!gameState.isPlaying) return;

    const luckBonus = gameState.upgrades.luck * 0.05;
    let rand = Math.random() - luckBonus;

    let fishType;
    if (rand < 0.5) fishType = FISH_TYPES[0];
    else if (rand < 0.75) fishType = FISH_TYPES[1];
    else if (rand < 0.9) fishType = FISH_TYPES[2];
    else if (rand < 0.97) fishType = FISH_TYPES[3];
    else fishType = FISH_TYPES[4];

    const fish = {
        x: Math.random() < 0.5 ? -50 : canvas.width + 50,
        y: 80 + Math.random() * (canvas.height - 150),
        vx: 0,
        vy: 0,
        type: fishType,
        size: 30 + Math.random() * 20,
        angle: 0,
        wobble: Math.random() * Math.PI * 2
    };

    fish.vx = fish.x < 0 ? fishType.speed : -fishType.speed;
    fish.vx *= (0.8 + Math.random() * 0.4);
    fish.vy = (Math.random() - 0.5) * 2;

    fishes.push(fish);
}

// ===== SPAWN POWER-UP =====
function spawnPowerUp() {
    const powerUp = POWER_UPS[Math.floor(Math.random() * POWER_UPS.length)];
    powerUpItems.push({
        x: 50 + Math.random() * (canvas.width - 100),
        y: 80 + Math.random() * (canvas.height - 150),
        type: powerUp,
        lifetime: 5000,
        spawnTime: Date.now(),
        pulse: 0
    });
}

// ===== UPDATE GAME =====
function update() {
    if (!gameState.isPlaying) return;

    ctx.fillStyle = '#0a1628';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    drawWaterBackground();

    // Update and draw fishes
    fishes = fishes.filter(fish => {
        fish.x += fish.vx;
        fish.y += fish.vy;
        fish.wobble += 0.1;
        fish.y += Math.sin(fish.wobble) * 0.5;

        if (fish.y < 80) fish.vy = Math.abs(fish.vy);
        if (fish.y > canvas.height - 50) fish.vy = -Math.abs(fish.vy);

        ctx.save();
        ctx.translate(fish.x, fish.y);
        ctx.scale(fish.vx > 0 ? 1 : -1, 1);

        if (fish.type.rarity !== 'common') {
            ctx.shadowColor = fish.type.color;
            ctx.shadowBlur = 15;
        }

        ctx.font = `${fish.size}px Arial`;
        ctx.textAlign = 'center';
        ctx.fillText(fish.type.emoji, 0, 10);

        if (fish.type.rarity === 'legendary') {
            ctx.font = '12px Arial';
            ctx.fillText('✨', 15, -10);
        }

        ctx.restore();

        return fish.x > -100 && fish.x < canvas.width + 100;
    });

    // Update and draw power-ups
    powerUpItems = powerUpItems.filter(pu => {
        const age = Date.now() - pu.spawnTime;
        if (age > pu.lifetime) return false;

        pu.pulse += 0.1;

        ctx.save();
        ctx.translate(pu.x, pu.y);

        const scale = 1 + Math.sin(pu.pulse) * 0.1;
        ctx.scale(scale, scale);

        ctx.shadowColor = '#FFD700';
        ctx.shadowBlur = 20;

        ctx.font = '35px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(pu.type.emoji, 0, 10);

        ctx.restore();

        return true;
    });

    // Draw particles
    particles = particles.filter(p => {
        p.x += p.vx;
        p.y += p.vy;
        p.vy += 0.1;
        p.life -= 2;

        ctx.fillStyle = `rgba(${p.color}, ${p.life / 100})`;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
        ctx.fill();

        return p.life > 0;
    });

    drawGameUI();
}

function drawGameUI() {
    ctx.fillStyle = '#fff';
    ctx.font = 'bold 24px Outfit';
    ctx.textAlign = 'left';
    ctx.fillText(`🎯 ${gameState.score}`, 20, 40);

    ctx.textAlign = 'right';
    const timerColor = gameState.timeLeft <= 10 ? '#ff4444' : '#fff';
    ctx.fillStyle = timerColor;
    ctx.fillText(`⏱ ${gameState.timeLeft}s`, canvas.width - 20, 40);

    if (gameState.combo > 1) {
        ctx.textAlign = 'center';
        ctx.fillStyle = '#FFD700';
        ctx.font = 'bold 18px Outfit';
        ctx.fillText(`🔥 ${gameState.combo}x COMBO!`, canvas.width / 2, 35);
    }

    let puX = 20;
    if (gameState.powerUps.doublePoints) {
        ctx.fillStyle = '#FFD700';
        ctx.font = '20px Arial';
        ctx.fillText('⚡2x', puX, 70);
        puX += 50;
    }
    if (gameState.powerUps.frenzy) {
        ctx.fillText('🔥', puX, 70);
    }
}

// ===== HANDLE CLICK =====
function handleClick(e) {
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    if (!gameState.isPlaying) {
        const btnX = canvas.width / 2 - 80;
        const btnY = canvas.height - 80;
        if (x >= btnX && x <= btnX + 160 && y >= btnY && y <= btnY + 50) {
            showNameInput();
        }
        return;
    }

    powerUpItems = powerUpItems.filter(pu => {
        const dist = Math.hypot(x - pu.x, y - pu.y);
        if (dist < 30) {
            activatePowerUp(pu.type);
            createParticles(pu.x, pu.y, '255, 215, 0');
            return false;
        }
        return true;
    });

    let caught = false;
    fishes = fishes.filter(fish => {
        const dist = Math.hypot(x - fish.x, y - fish.y);
        const hitRadius = fish.size * 0.7;

        if (dist < hitRadius) {
            catchFish(fish);
            caught = true;
            return false;
        }
        return true;
    });

    if (!caught && gameState.isPlaying) {
        gameState.combo = 0;
    }
}

function handleTouch(e) {
    e.preventDefault();
    const touch = e.touches[0];
    handleClick({ clientX: touch.clientX, clientY: touch.clientY });
}

// ===== CATCH FISH =====
function catchFish(fish) {
    gameState.fishCaught++;
    gameState.combo++;
    gameState.maxCombo = Math.max(gameState.maxCombo, gameState.combo);

    let points = fish.type.points;
    points *= (1 + gameState.combo * 0.1);

    if (gameState.powerUps.doublePoints) {
        points *= 2;
    }

    points = Math.round(points);
    gameState.score += points;

    showFloatingText(fish.x, fish.y, `+${points}`, fish.type.color);
    createParticles(fish.x, fish.y, fish.type.rarity === 'legendary' ? '255, 215, 0' : '0, 255, 148');

    updateUI();
}

// ===== POWER-UPS =====
function activatePowerUp(powerUp) {
    showGameMessage(`${powerUp.emoji} ${powerUp.name}!`, 'success');

    switch (powerUp.effect) {
        case 'doublePoints':
            gameState.powerUps.doublePoints = true;
            setTimeout(() => gameState.powerUps.doublePoints = false, powerUp.duration);
            break;
        case 'frenzy':
            gameState.powerUps.frenzy = true;
            clearInterval(spawnInterval);
            spawnInterval = setInterval(spawnFish, 300);
            setTimeout(() => {
                gameState.powerUps.frenzy = false;
                clearInterval(spawnInterval);
                spawnInterval = setInterval(spawnFish, 800);
            }, powerUp.duration);
            break;
        case 'addTime':
            gameState.timeLeft += 10;
            break;
        case 'bomb':
            fishes.forEach(fish => catchFish(fish));
            fishes = [];
            break;
    }
}

// ===== VISUAL EFFECTS =====
function createParticles(x, y, color) {
    for (let i = 0; i < 10; i++) {
        particles.push({
            x, y,
            vx: (Math.random() - 0.5) * 8,
            vy: (Math.random() - 0.5) * 8 - 3,
            size: 3 + Math.random() * 4,
            color: color,
            life: 100
        });
    }
}

function showFloatingText(x, y, text, color) {
    const floater = document.createElement('div');
    floater.className = 'floating-score';
    floater.textContent = text;
    floater.style.left = x + 'px';
    floater.style.top = y + 'px';
    floater.style.color = color;

    const container = document.getElementById('game-container');
    if (container) {
        container.appendChild(floater);
        setTimeout(() => floater.remove(), 1000);
    }
}

// ===== UPDATE UI =====
function updateUI() {
    const scoreEl = document.getElementById('game-score');
    const timeEl = document.getElementById('game-time');
    const fishEl = document.getElementById('game-fish-count');

    if (scoreEl) scoreEl.textContent = gameState.score;
    if (timeEl) timeEl.textContent = gameState.timeLeft;
    if (fishEl) fishEl.textContent = gameState.fishCaught;
}

// ===== END GAME =====
async function endGame() {
    gameState.isPlaying = false;
    clearInterval(gameLoop);
    clearInterval(spawnInterval);

    // Save to leaderboard
    const entry = {
        name: gameState.playerName,
        score: gameState.score,
        fish: gameState.fishCaught,
        combo: gameState.maxCombo,
        date: new Date().toLocaleDateString('de-DE')
    };

    await saveToLeaderboard(entry);

    // Show results
    showResults(entry);
}

function showResults(entry) {
    const overlay = document.getElementById('results-overlay');
    const rank = globalLeaderboard.findIndex(e => e.name === entry.name && e.score === entry.score) + 1;

    document.getElementById('result-score').textContent = entry.score;
    document.getElementById('result-fish').textContent = entry.fish;
    document.getElementById('result-combo').textContent = entry.combo;
    document.getElementById('result-rank').textContent = `#${rank}`;

    // Render full leaderboard
    const lbContainer = document.getElementById('leaderboard-list');
    lbContainer.innerHTML = globalLeaderboard.slice(0, 10).map((e, i) => `
        <div class="lb-entry ${e.name === entry.name && e.score === entry.score ? 'lb-current' : ''}">
            <span class="lb-rank">${i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `#${i + 1}`}</span>
            <span class="lb-name">${e.name}</span>
            <span class="lb-score">${e.score}</span>
        </div>
    `).join('');

    overlay.classList.add('active');

    // Update preview as well
    renderLeaderboardPreview();
}

function playAgain() {
    document.getElementById('results-overlay').classList.remove('active');
    renderStartScreen();
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initGame);
