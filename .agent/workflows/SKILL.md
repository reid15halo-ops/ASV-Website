---
description: Workflow for managing and updating the ASV Petri Heil Großostheim fishing club website
---

# ASV Petri Heil Großostheim Website

## Projektübersicht

Website für den Angelsportverein "Petri Heil" Großostheim (seit 1966).

**Standort:** `c:\Users\122798\OneDrive\Documents\Sonstiges\ASV\Website\ASV-Website-1`

## Struktur

```
ASV-Website-1/
├── index.html          # Hauptseite mit Auswahl Jugend/Verein
├── Events.html         # Termine & Veranstaltungen
├── cms/                # CMS-System für Vereinsseite
├── jugend/             # Jugendgruppen-Seiten
│   ├── index.html      # Jugend-Startseite
│   ├── aktivitaeten.html
│   ├── eltern.html
│   ├── mitmachen.html
│   ├── kontakt.html
│   ├── kalender.html
│   ├── impressum.html
│   ├── datenschutz.html
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript
│   ├── Logo/           # Logo-Dateien
│   └── Bilder/         # Bilder
└── Logo/               # Vereins-Logos
    ├── Verein/         # Hauptverein-Logo
    └── Jugend/         # Jugend-Logo
```

## Wichtige Informationen

### Kontaktdaten
- **E-Mail:** asv-petriheil@web.de
- **Telefon:** 0177 7040857
- **Adresse:** Grabenstraße 7, 63762 Großostheim

### Mitgliedsbeiträge
- **Jugend (10-18 Jahre):** 38€/Jahr
- **Erwachsene:** ab 108€/Jahr

### Jugendgruppe
- Zielgruppe: 10-18 Jahre
- Hinweis: Kinder 7-9 Jahre nur mit Elternaufsicht
- Jugendleiter: Jonas, Sebastian, Lukas
- Highlights: Zeltlager, Lagerfeuer, Fischerprüfung

### Events
- Fischessen am Karfreitag
- Zeltlager (Juli)
- Unterfränkisches Ausbildungszeltlager
- Mainangeln
- Mömlingangeln
- Angeln am Vereinssee
- Jugendaktivitäten
- Grillfeiern
- Geburtstagsfeiern
- Jübiläumsfeiern
- Arbeitseinsätze
- Naturschutz (z.B. Nistkastenbau)
- Anglerkönigsfeier (November)
- Regelmäßige Gemeinschaftsangeln 
- Schafkopfrunde (jeden Freitag, 19 Uhr)
- Fischerprüfung

## Technologie

- **Sprache:** HTML, CSS, JavaScript (Vanilla)
- **Fonts:** Outfit, Inter (Google Fonts)
- **Design:** Dunkles Theme mit Blau/Cyan-Akzenten
- **Jugend-Design:** Lebhafter mit Emojis und Animationen
- **Responsive:** Mobile-optimiert

## Workflows

### Website lokal testen
```powershell
# Einfacher lokaler Server
npx -y serve .
```

### Encoding beachten
- Alle Dateien verwenden UTF-8
- Deutsche Umlaute (ä, ö, ü, ß) korrekt codieren
- HTML-Entities wenn nötig (&auml;, &ouml;, &uuml;, &szlig;)

### Bilder hinzufügen
1. Bilder nach `jugend/Bilder/` oder entsprechendem Ordner kopieren
2. Optimale Größe: max 1920px Breite
3. Komprimierte JPG oder WebP bevorzugen

## Hinweise

> ⚠️ **NIEMALS** "Wein am See" in Inhalten erwähnen!

### Google Kalender
Der Vereinskalender ist per Google Calendar eingebunden:
- Kalender-ID: `1ccfad68a0dff3c20173ba00986bc6d4327b8ddb71011dd1e93238aab311c9dc@group.calendar.google.com`

### CSS-Dateien (Jugend)
- `css/styles.css` - Basis-Styles
- `css/mobile.css` - Mobile Anpassungen
- `css/jugend.css` - Jugend-spezifische Styles

## Nützliche Befehle

```powershell
# Alle HTML-Dateien finden
Get-ChildItem -Path . -Filter *.html -Recurse

# Nach Text suchen
Select-String -Path .\*.html -Pattern "Suchbegriff"

# Git Status
git status
```

---

## 🎯 Marketing Platform

**Ordner:** `Marketing/`

### Dateien
| Datei | Beschreibung |
|-------|--------------|
| `asv-marketing-platform.html` | Hauptanwendung (6 Module) |
| `platform-styles.css` | CSS Styling |
| `platform-app.js` | JavaScript Funktionalität |
| `content-generator.html` | Standalone Meme/Bild Generator |
| `Marketing-Kampagne-2026.md` | Kampagnen-Dokument |

### Features
- 📊 **Dashboard** - Statistiken & Schnellaktionen
- 📅 **Kalender** - Google Calendar Integration
- ✨ **Content** - Multi-Account Post-Generator
- 🖼️ **Medien** - Upload mit Filter-Presets
- 👤 **Accounts** - TikTok & Instagram Verwaltung
- 🏆 **Jahresrückblick** - Automatischer Recap

### Starten
```powershell
# Im Browser öffnen
start Marketing/asv-marketing-platform.html

# Oder lokaler Server
npx -y serve ./Marketing
```

### Budget: 50€/Monat
- Buffer Free (0€) für Scheduling
- Canva Free (0€) für Design
- ~50€ für Instagram/TikTok Ads