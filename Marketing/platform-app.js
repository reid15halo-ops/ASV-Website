// ========== DATABASE (IndexedDB) ==========
const DB_NAME = 'ASVMarketingDB';
const DB_VERSION = 1;
let db;

async function initDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => { db = request.result; resolve(db); };
        request.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('media')) db.createObjectStore('media', { keyPath: 'id', autoIncrement: true });
            if (!db.objectStoreNames.contains('accounts')) db.createObjectStore('accounts', { keyPath: 'id', autoIncrement: true });
            if (!db.objectStoreNames.contains('posts')) db.createObjectStore('posts', { keyPath: 'id', autoIncrement: true });
        };
    });
}

// ========== NAVIGATION ==========
function switchView(viewName) {
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(viewName + '-view').classList.add('active');
    document.querySelector(`[data-view="${viewName}"]`).classList.add('active');
}

document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => switchView(btn.dataset.view));
});

// ========== ACCOUNTS ==========
let accounts = [];

async function loadAccounts() {
    const saved = localStorage.getItem('asv_accounts');
    accounts = saved ? JSON.parse(saved) : [
        { id: 1, name: 'ASV Hauptverein', platform: 'instagram', handle: '@asvpetriheil' },
        { id: 2, name: 'ASV Jugend', platform: 'tiktok', handle: '@asvjugend' }
    ];
    renderAccounts();
}

function saveAccounts() {
    localStorage.setItem('asv_accounts', JSON.stringify(accounts));
    renderAccounts();
}

function renderAccounts() {
    const icons = { instagram: '📷', tiktok: '🎵' };
    
    // Sidebar
    document.getElementById('accountList').innerHTML = accounts.map(a => 
        `<div class="account-chip">${icons[a.platform]} ${a.name}</div>`
    ).join('');
    
    // Accounts page
    document.getElementById('accountsGrid').innerHTML = accounts.map(a => `
        <div class="account-card">
            <span class="account-icon">${icons[a.platform]}</span>
            <div><div class="account-name">${a.name}</div><div class="account-handle">${a.handle}</div></div>
            <button class="delete-btn" onclick="deleteAccount(${a.id})">🗑️</button>
        </div>
    `).join('');
    
    // Content dropdown
    const contentSelect = document.getElementById('contentAccount');
    if (contentSelect) {
        contentSelect.innerHTML = accounts.map(a => `<option value="${a.id}">${icons[a.platform]} ${a.name}</option>`).join('');
    }
    
    updateStats();
}

function addAccount() {
    const name = document.getElementById('newAccountName').value;
    const platform = document.getElementById('newAccountPlatform').value;
    const handle = document.getElementById('newAccountHandle').value;
    if (!name || !handle) return showToast('Bitte alle Felder ausfüllen');
    accounts.push({ id: Date.now(), name, platform, handle });
    saveAccounts();
    document.getElementById('newAccountName').value = '';
    document.getElementById('newAccountHandle').value = '';
    showToast('Account hinzugefügt!');
}

function deleteAccount(id) {
    accounts = accounts.filter(a => a.id !== id);
    saveAccounts();
    showToast('Account gelöscht');
}

// ========== CALENDAR ==========
const CALENDAR_ID = '1ccfad68a0dff3c20173ba00986bc6d4327b8ddb71011dd1e93238aab311c9dc@group.calendar.google.com';
let calendarEvents = [];

async function loadCalendarEvents() {
    try {
        // Using public Google Calendar JSON feed
        const now = new Date().toISOString();
        const url = `https://www.googleapis.com/calendar/v3/calendars/${encodeURIComponent(CALENDAR_ID)}/events?key=AIzaSyBNlYH01_9Hc5S1J9vuFmu2nUqBZJNAXxs&timeMin=${now}&maxResults=20&singleEvents=true&orderBy=startTime`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.items) {
            calendarEvents = data.items.map(e => ({
                id: e.id,
                title: e.summary || 'Ohne Titel',
                description: e.description || '',
                start: new Date(e.start.dateTime || e.start.date),
                location: e.location || ''
            }));
            renderCalendarEvents();
            updateStats();
        }
    } catch (error) {
        console.error('Calendar error:', error);
        // Fallback demo events
        calendarEvents = [
            { id: '1', title: 'Fischessen am Karfreitag', start: new Date('2026-04-18'), description: 'Traditionelles Fischessen' },
            { id: '2', title: 'Zeltlager 2026', start: new Date('2026-07-15'), description: 'Jugend-Zeltlager am See' },
            { id: '3', title: 'Anglerkönigsfeier', start: new Date('2026-11-14'), description: 'Ehrung der besten Angler' }
        ];
        renderCalendarEvents();
    }
}

function renderCalendarEvents() {
    const months = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
    
    const eventsHTML = calendarEvents.slice(0, 5).map(e => `
        <div class="event-item">
            <div class="event-date">
                <span class="event-day">${e.start.getDate()}</span>
                <span class="event-month">${months[e.start.getMonth()]}</span>
            </div>
            <div>
                <div class="event-title">${e.title}</div>
                <div class="event-desc">${e.description.substring(0, 50)}</div>
            </div>
        </div>
    `).join('');
    
    document.getElementById('calendarEvents').innerHTML = eventsHTML || 'Keine Events gefunden';
    document.getElementById('upcomingEvents').innerHTML = eventsHTML || 'Keine Events';
    
    // Event select dropdown
    document.getElementById('eventSelect').innerHTML = 
        '<option value="">Event auswählen...</option>' +
        calendarEvents.map(e => `<option value="${e.id}">${e.title} (${e.start.toLocaleDateString('de-DE')})</option>`).join('');
}

function generateEventPost() {
    const eventId = document.getElementById('eventSelect').value;
    const platform = document.getElementById('eventPlatform').value;
    if (!eventId) return showToast('Bitte Event auswählen');
    
    const event = calendarEvents.find(e => e.id === eventId);
    const isYouth = platform === 'tiktok';
    
    const templates = {
        instagram: `📅 SAVE THE DATE!\n\n${event.title}\n🗓️ ${event.start.toLocaleDateString('de-DE')}\n📍 Vereinsgelände Großostheim\n\n${event.description}\n\nWir freuen uns auf euch! 🎣\n\n#ASVPetriHeil #Angeln #Großostheim`,
        tiktok: `🔥 ${event.title.toUpperCase()} 🔥\n\n📅 ${event.start.toLocaleDateString('de-DE')}\n\n${event.description}\n\nSei dabei! 💪🎣\n\n#ASVJugend #Angeln #fyp`
    };
    
    document.getElementById('eventPostOutput').textContent = templates[platform];
}

// ========== CONTENT GENERATOR ==========
const contentTemplates = {
    youth: {
        post: "🎣 {text}\n\n#ASVJugend #Angeln #Großostheim #fyp",
        story: "🔥 {text}\n\n@asvjugend",
        reel: "POV: Du bist bei der ASV Jugend 🎣\n\n{text}\n\n#ASVJugend #AngelTok"
    },
    adults: {
        post: "🎣 {text}\n\nASV Petri Heil Großostheim\nSeit 1966\n\n#ASVPetriHeil #Angeln",
        story: "{text}\n\nASV Petri Heil 🎣",
        reel: "{text}\n\n#Angeln #PetriHeil #Natur"
    }
};

function generateContent() {
    const audience = document.getElementById('contentAudience').value;
    const type = document.getElementById('contentType').value;
    const text = document.getElementById('contentText').value || 'Am Wasser ist die Welt in Ordnung.';
    
    const template = contentTemplates[audience][type];
    const content = template.replace('{text}', text);
    
    document.getElementById('contentPreview').textContent = content;
    incrementPostCount();
}

function copyContent() {
    const content = document.getElementById('contentPreview').textContent;
    navigator.clipboard.writeText(content);
    showToast('Kopiert!');
}

// ========== MEDIA LIBRARY ==========
let mediaItems = [];
let currentEditImage = null;

function setupMediaUpload() {
    const zone = document.getElementById('uploadZone');
    const input = document.getElementById('fileInput');
    
    zone.addEventListener('click', () => input.click());
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = '#00b4d8'; });
    zone.addEventListener('dragleave', () => zone.style.borderColor = '');
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.style.borderColor = '';
        handleFiles(e.dataTransfer.files);
    });
    input.addEventListener('change', e => handleFiles(e.target.files));
}

async function handleFiles(files) {
    for (const file of files) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const item = {
                id: Date.now() + Math.random(),
                name: file.name,
                type: file.type.startsWith('video') ? 'video' : 'image',
                data: e.target.result,
                date: new Date()
            };
            mediaItems.push(item);
            saveMedia();
            renderMedia();
        };
        reader.readAsDataURL(file);
    }
    showToast(`${files.length} Datei(en) hochgeladen`);
}

function saveMedia() {
    // Store in localStorage (limited to ~5MB, for demo purposes)
    try {
        localStorage.setItem('asv_media', JSON.stringify(mediaItems.slice(-20))); // Keep last 20
    } catch (e) {
        console.warn('Storage limit reached');
    }
    updateStats();
}

function loadMedia() {
    const saved = localStorage.getItem('asv_media');
    if (saved) mediaItems = JSON.parse(saved);
    renderMedia();
}

function renderMedia() {
    document.getElementById('mediaGrid').innerHTML = mediaItems.map(m => `
        <div class="media-item" onclick="editMedia(${m.id})">
            ${m.type === 'video' 
                ? `<video src="${m.data}" muted></video>` 
                : `<img src="${m.data}" alt="${m.name}">`}
        </div>
    `).join('');
}

function editMedia(id) {
    const item = mediaItems.find(m => m.id === id);
    if (!item || item.type !== 'image') return;
    
    document.getElementById('mediaEditor').style.display = 'block';
    currentEditImage = new Image();
    currentEditImage.onload = () => {
        const canvas = document.getElementById('editCanvas');
        canvas.width = Math.min(currentEditImage.width, 600);
        canvas.height = (canvas.width / currentEditImage.width) * currentEditImage.height;
        applyFilters();
    };
    currentEditImage.src = item.data;
    
    // Reset filters
    document.getElementById('filterBrightness').value = 100;
    document.getElementById('filterContrast').value = 100;
    document.getElementById('filterSaturation').value = 100;
    document.getElementById('filterWarmth').value = 0;
}

function applyFilters() {
    if (!currentEditImage) return;
    const canvas = document.getElementById('editCanvas');
    const ctx = canvas.getContext('2d');
    
    const brightness = document.getElementById('filterBrightness').value;
    const contrast = document.getElementById('filterContrast').value;
    const saturation = document.getElementById('filterSaturation').value;
    const warmth = document.getElementById('filterWarmth').value;
    
    ctx.filter = `brightness(${brightness}%) contrast(${contrast}%) saturate(${saturation}%) sepia(${warmth}%)`;
    ctx.drawImage(currentEditImage, 0, 0, canvas.width, canvas.height);
}

function applyPreset(preset) {
    const presets = {
        vibrant: { brightness: 110, contrast: 115, saturation: 140, warmth: 0 },
        warm: { brightness: 105, contrast: 100, saturation: 110, warmth: 25 },
        cool: { brightness: 100, contrast: 110, saturation: 90, warmth: 0 },
        dramatic: { brightness: 95, contrast: 130, saturation: 80, warmth: 10 }
    };
    
    const p = presets[preset];
    document.getElementById('filterBrightness').value = p.brightness;
    document.getElementById('filterContrast').value = p.contrast;
    document.getElementById('filterSaturation').value = p.saturation;
    document.getElementById('filterWarmth').value = p.warmth;
    applyFilters();
}

// Filter event listeners
['filterBrightness', 'filterContrast', 'filterSaturation', 'filterWarmth', 'filterVignette'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', applyFilters);
});

function addTextOverlay() {
    const text = document.getElementById('overlayText').value;
    if (!text) return;
    
    const canvas = document.getElementById('editCanvas');
    const ctx = canvas.getContext('2d');
    
    ctx.filter = 'none';
    ctx.font = 'bold 32px Outfit, sans-serif';
    ctx.fillStyle = '#fff';
    ctx.textAlign = 'center';
    ctx.shadowColor = 'rgba(0,0,0,0.5)';
    ctx.shadowBlur = 10;
    ctx.fillText(text, canvas.width / 2, canvas.height - 40);
}

function saveEditedImage() {
    const canvas = document.getElementById('editCanvas');
    const link = document.createElement('a');
    link.download = 'asv-edited-image.png';
    link.href = canvas.toDataURL();
    link.click();
    showToast('Bild gespeichert!');
}

// ========== YEAR RECAP ==========
async function generateYearRecap() {
    document.getElementById('recapOutput').style.display = 'block';
    
    // Stats
    const stats = {
        events: calendarEvents.length,
        media: mediaItems.length,
        posts: parseInt(localStorage.getItem('asv_postCount') || '0')
    };
    
    document.getElementById('recapStats').innerHTML = `
        <div class="stats-grid">
            <div class="stat-card"><span class="stat-num">${stats.events}</span><span class="stat-label">Events</span></div>
            <div class="stat-card"><span class="stat-num">${stats.media}</span><span class="stat-label">Medien</span></div>
            <div class="stat-card"><span class="stat-num">${stats.posts}</span><span class="stat-label">Posts</span></div>
        </div>
    `;
    
    // Collage Canvas
    const canvas = document.getElementById('recapCanvas');
    const ctx = canvas.getContext('2d');
    
    // Background
    const grad = ctx.createLinearGradient(0, 0, 1080, 1080);
    grad.addColorStop(0, '#0a1628');
    grad.addColorStop(1, '#1a2744');
    ctx.fillStyle = grad;
    ctx.fillRect(0, 0, 1080, 1080);
    
    // Title
    ctx.font = 'bold 72px Outfit, sans-serif';
    ctx.fillStyle = '#00b4d8';
    ctx.textAlign = 'center';
    ctx.fillText('JAHRESRÜCKBLICK 2026', 540, 120);
    
    ctx.font = '48px sans-serif';
    ctx.fillText('🎣', 540, 220);
    
    ctx.font = 'bold 36px Outfit, sans-serif';
    ctx.fillStyle = '#fff';
    ctx.fillText('ASV Petri Heil Großostheim', 540, 300);
    
    // Stats
    ctx.font = 'bold 120px Outfit, sans-serif';
    ctx.fillStyle = '#00ff94';
    ctx.fillText(stats.events, 270, 500);
    ctx.fillText(stats.media, 540, 500);
    ctx.fillText(stats.posts, 810, 500);
    
    ctx.font = '24px Outfit, sans-serif';
    ctx.fillStyle = 'rgba(255,255,255,0.7)';
    ctx.fillText('Events', 270, 550);
    ctx.fillText('Medien', 540, 550);
    ctx.fillText('Posts', 810, 550);
    
    // Highlights
    ctx.font = 'bold 32px Outfit, sans-serif';
    ctx.fillStyle = '#fff';
    ctx.fillText('✨ Highlights des Jahres', 540, 680);
    
    ctx.font = '24px Outfit, sans-serif';
    calendarEvents.slice(0, 3).forEach((e, i) => {
        ctx.fillText(`• ${e.title}`, 540, 740 + i * 50);
    });
    
    // Footer
    ctx.font = '18px Outfit, sans-serif';
    ctx.fillStyle = 'rgba(255,255,255,0.4)';
    ctx.fillText('Danke für ein großartiges Jahr! Petri Heil! 🎣', 540, 1020);
    
    // Recap texts
    document.getElementById('recapTexts').innerHTML = `
        <div class="output-box">
<strong>📷 Instagram:</strong>
✨ JAHRESRÜCKBLICK 2026 ✨

Was für ein Jahr beim ASV Petri Heil!

📊 ${stats.events} Events
🖼️ ${stats.media} Erinnerungen
📝 ${stats.posts} Posts

Danke an alle Mitglieder für ein tolles Jahr!
Petri Heil! 🎣

#ASVPetriHeil #Jahresrückblick #2026 #Angeln #Großostheim
        </div>
        <div class="output-box" style="margin-top:1rem;">
<strong>🎵 TikTok:</strong>
2026 RECAP 🔥

${stats.events} Events ✅
${stats.media} Memories ✅
${stats.posts} Posts ✅

Das war unser Jahr! 🎣💪

#ASVJugend #Recap2026 #fyp #Angeln
        </div>
    `;
    
    showToast('Jahresrückblick erstellt!');
}

// ========== STATS ==========
function updateStats() {
    document.getElementById('statEvents').textContent = calendarEvents.length;
    document.getElementById('statMedia').textContent = mediaItems.length;
    document.getElementById('statPosts').textContent = localStorage.getItem('asv_postCount') || '0';
}

function incrementPostCount() {
    const count = parseInt(localStorage.getItem('asv_postCount') || '0') + 1;
    localStorage.setItem('asv_postCount', count);
    updateStats();
}

// ========== TOAST ==========
function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = '✓ ' + msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}

// ========== INIT ==========
async function init() {
    await initDB();
    loadAccounts();
    loadMedia();
    loadCalendarEvents();
    setupMediaUpload();
    updateStats();
}

init();
