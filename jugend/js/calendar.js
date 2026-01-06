/**
 * ASV Calendar Logic
 * Parses ICS data and renders widgets/calendar views.
 */

const ASVCalendar = {
    proxyUrl: 'calendar-proxy.php',
    events: [],

    init() {
        this.fetchEvents();
    },

    async fetchEvents() {
        try {
            const response = await fetch(this.proxyUrl);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.text();
            this.events = this.parseICS(data);

            // Sort by date
            this.events.sort((a, b) => a.start - b.start);

            // Render Widget (if element exists)
            this.renderUpcomingWidget();

            // Render Full Calendar (if element exists)
            this.renderMonthlyCalendar();

        } catch (error) {
            console.error('Error loading calendar:', error);
            // Show error state in UI
            const widget = document.getElementById('upcoming-events-container');
            if (widget) widget.innerHTML = '<p class="error-msg">Termine konnten nicht geladen werden.</p>';

            const grid = document.getElementById('calendar-grid');
            if (grid) grid.innerHTML = '<div class="error-msg">Kalender konnte nicht geladen werden.</div>';
        }
    },

    parseICS(icsData) {
        const events = [];
        const lines = icsData.replace(/\r\n/g, '\n').split('\n');
        let currentEvent = null;

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];

            if (line.startsWith('BEGIN:VEVENT')) {
                currentEvent = {};
            } else if (line.startsWith('END:VEVENT')) {
                if (currentEvent && currentEvent.start) {
                    // Only add future events or events from last 30 days
                    const cutoff = new Date();
                    cutoff.setDate(cutoff.getDate() - 30);

                    if (currentEvent.end > cutoff) {
                        events.push(currentEvent);
                    }
                }
                currentEvent = null;
            } else if (currentEvent) {
                let [key, value] = [line.split(':')[0], line.substring(line.indexOf(':') + 1)];

                // Handle multi-line descriptions (simplified)
                if (!key || !value) continue;

                // Clean key (remove params)
                key = key.split(';')[0];

                switch (key) {
                    case 'SUMMARY':
                        currentEvent.title = this.cleanText(value);
                        break;
                    case 'DESCRIPTION':
                        currentEvent.description = this.cleanText(value);
                        break;
                    case 'LOCATION':
                        currentEvent.location = this.cleanText(value);
                        break;
                    case 'DTSTART':
                        currentEvent.start = this.parseDate(value);
                        break;
                    case 'DTEND':
                        currentEvent.end = this.parseDate(value);
                        break;
                }
            }
        }
        return events;
    },

    parseDate(value) {
        // Format: 20240501T120000Z or 20240501
        if (!value) return null;

        const year = parseInt(value.substring(0, 4));
        const month = parseInt(value.substring(4, 6)) - 1;
        const day = parseInt(value.substring(6, 8));

        let hour = 0, min = 0, sec = 0;

        if (value.includes('T')) {
            const timePart = value.split('T')[1];
            hour = parseInt(timePart.substring(0, 2));
            min = parseInt(timePart.substring(2, 4));
            sec = parseInt(timePart.substring(4, 6));
        }

        return new Date(year, month, day, hour, min, sec);
    },

    cleanText(text) {
        if (!text) return '';
        return text.replace(/\\,/g, ',').replace(/\\;/g, ';').replace(/\\n/g, '<br>').replace(/\\N/g, '<br>');
    },

    renderUpcomingWidget() {
        const container = document.getElementById('upcoming-events-container');
        if (!container) return; // Not on homepage

        container.innerHTML = '';

        const now = new Date();
        const upcoming = this.events.filter(e => e.end >= now).slice(0, 3);

        if (upcoming.length === 0) {
            container.innerHTML = '<p class="no-events">Aktuell keine Termine geplant 🏝️</p>';
            return;
        }

        upcoming.forEach(event => {
            const timeStr = event.start.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });

            const card = document.createElement('div');
            card.className = 'event-card'; // Removed reveal-on-scroll to prevent visibility issues
            card.innerHTML = `
                <div class="event-date-badge">
                    <span class="event-day">${event.start.getDate()}</span>
                    <span class="event-month">${event.start.toLocaleDateString('de-DE', { month: 'short' })}</span>
                </div>
                <div class="event-details">
                    <h3 class="event-title">${event.title}</h3>
                    <div class="event-meta">
                        <span>🕒 ${timeStr} Uhr</span>
                        ${event.location ? `<span>📍 ${event.location.split(',')[0]}</span>` : ''}
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    },

    // --- Monthly Calendar View Logic ---
    currentMonth: new Date(),

    renderMonthlyCalendar() {
        const container = document.getElementById('calendar-grid');
        if (!container) return; // Not on calendar page

        // Update header
        const monthYear = document.getElementById('calendar-month-year');
        if (monthYear) {
            monthYear.textContent = this.currentMonth.toLocaleDateString('de-DE', { month: 'long', year: 'numeric' });
        }

        container.innerHTML = '';

        const year = this.currentMonth.getFullYear();
        const month = this.currentMonth.getMonth();

        // First day of the month
        const firstDay = new Date(year, month, 1);
        // Correct for Monday start (0=Sun -> 6, 1=Mon -> 0)
        let startingDay = firstDay.getDay() - 1;
        if (startingDay === -1) startingDay = 6;

        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // Empty cells for previous month
        for (let i = 0; i < startingDay; i++) {
            const cell = document.createElement('div');
            cell.className = 'calendar-day empty';
            container.appendChild(cell);
        }

        // Days
        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('div');
            cell.className = 'calendar-day';

            const dateObj = new Date(year, month, day);
            const isToday = this.isSameDay(dateObj, new Date());
            if (isToday) cell.classList.add('today');

            // Find events for this day
            const dayEvents = this.events.filter(e => this.isSameDay(e.start, dateObj));

            let eventsHtml = '';
            dayEvents.forEach(ev => {
                const time = ev.start.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
                // Truncate title
                const title = ev.title.length > 20 ? ev.title.substring(0, 18) + '..' : ev.title;
                eventsHtml += `<div class="day-event" title="${ev.title}">${time} ${title}</div>`;
            });

            cell.innerHTML = `
                <span class="day-number">${day}</span>
                <div class="day-events-list">
                    ${eventsHtml}
                </div>
            `;

            // Click to show details (simple alert for now or modal if wanted)
            if (dayEvents.length > 0) {
                cell.classList.add('has-events');
                cell.addEventListener('click', () => this.showDayDetails(dayEvents));
            }

            container.appendChild(cell);
        }
    },

    isSameDay(d1, d2) {
        return d1.getFullYear() === d2.getFullYear() &&
            d1.getMonth() === d2.getMonth() &&
            d1.getDate() === d2.getDate();
    },

    changeMonth(delta) {
        this.currentMonth.setMonth(this.currentMonth.getMonth() + delta);
        this.renderMonthlyCalendar();
    },

    showDayDetails(events) {
        // Find existing modal or create one
        let modal = document.getElementById('event-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'event-modal';
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <button class="modal-close">&times;</button>
                    <h3 id="modal-date-title">Termine</h3>
                    <div id="modal-events-list"></div>
                </div>
            `;
            document.body.appendChild(modal);

            modal.querySelector('.modal-close').onclick = () => modal.classList.remove('active');
            modal.onclick = (e) => {
                if (e.target === modal) modal.classList.remove('active');
            };
        }

        const list = modal.querySelector('#modal-events-list');
        const title = modal.querySelector('#modal-date-title');

        title.textContent = "📅 " + events[0].start.toLocaleDateString('de-DE', { dateStyle: 'full' });

        list.innerHTML = events.map(e => `
            <div class="modal-event-item">
                <div class="modal-time">${e.start.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })} - ${e.end.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })}</div>
                <div class="modal-title">${e.title}</div>
                ${e.location ? `<div class="modal-location">📍 ${e.location}</div>` : ''}
                ${e.description ? `<div class="modal-desc">${e.description}</div>` : ''}
            </div>
        `).join('');

        modal.classList.add('active');
    }
};

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    ASVCalendar.init();

    // Bind buttons for monthly view
    const prevBtn = document.getElementById('cal-prev');
    const nextBtn = document.getElementById('cal-next');

    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', () => ASVCalendar.changeMonth(-1));
        nextBtn.addEventListener('click', () => ASVCalendar.changeMonth(1));
    }
});
