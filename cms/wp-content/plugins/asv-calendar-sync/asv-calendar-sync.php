<?php
/**
 * Plugin Name: ASV Calendar Sync
 * Description: Synchronisiert automatisch den ASV Google Kalender mit "The Events Calendar" und bietet einen Download-Button.
 * Version: 1.0
 * Author: ASV Petri Heil
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class ASV_Calendar_Sync {
    // Konfiguration
    const GOOGLE_ICS_URL = 'https://calendar.google.com/calendar/ical/1ccfad68a0dff3c20173ba00986bc6d4327b8ddb71011dd1e93238aab311c9dc@group.calendar.google.com/public/basic.ics';
    const SYNC_INTERVAL = 'hourly';
    
    public function __construct() {
        // Scheduler Hooks
        add_action( 'asv_calendar_sync_cron', array( $this, 'run_sync' ) );
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        
        // Manual Sync Button (Admin Bar)
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_sync_button' ), 100 );
        add_action( 'admin_init', array( $this, 'handle_manual_sync' ) );
        
        // Shortcode
        add_shortcode( 'asv_ics_button', array( $this, 'render_download_button' ) );
        
        // Cron Schedule hinzufügen, falls noch nicht existiert
        add_filter( 'cron_schedules', function ( $schedules ) {
            $schedules['hourly'] = array(
                'interval' => 3600,
                'display'  => __( 'Stündlich' ),
            );
            return $schedules;
        } );
    }
    /**
     * Aktivierung: Setzt den Cronjob
     */
    public function activate() {
        if ( ! wp_next_scheduled( 'asv_calendar_sync_cron' ) ) {
            wp_schedule_event( time(), self::SYNC_INTERVAL, 'asv_calendar_sync_cron' );
        }
    }
    /**
     * Deaktivierung: Löscht den Cronjob
     */
    public function deactivate() {
        wp_clear_scheduled_hook( 'asv_calendar_sync_cron' );
    }
    /**
     * Shortcode: [asv_ics_button]
     */
    public function render_download_button() {
        return sprintf(
            '<a href="%s" class="button asv-download-btn" target="_blank" style="background-color: #3B82F6; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; display: inline-block;">📅 Kalender abonnieren (ICS)</a>',
            self::GOOGLE_ICS_URL
        );
    }
    /**
     * Fügt "Jetzt synchronisieren" zur Admin Bar hinzu
     */
    public function admin_bar_sync_button( $admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) return;
        
        $admin_bar->add_menu( array(
            'id'    => 'asv-sync',
            'title' => '🔄 ASV Sync',
            'href'  => admin_url( '?asv_manual_sync=1' ),
            'meta'  => array( 'title' => 'Google Kalender jetzt synchronisieren' ),
        ));
    }
    /**
     * Handle manuellen Sync-Klick
     */
    public function handle_manual_sync() {
        if ( isset( $_GET['asv_manual_sync'] ) && current_user_can( 'manage_options' ) ) {
            $this->run_sync();
            wp_redirect( admin_url( 'edit.php?post_type=tribe_events&message=sync_complete' ) );
            exit;
        }
    }
    /**
     * Hauptfunktion: Synchronisation
     */
    public function run_sync() {
        if ( ! function_exists( 'tribe_create_event' ) ) {
            error_log( 'ASV Sync Fehler: The Events Calendar Plugin ist nicht aktiv.' );
            return;
        }
        $ics_content = $this->fetch_ics();
        if ( ! $ics_content ) return;
        $events = $this->parse_ics( $ics_content );
        
        foreach ( $events as $event ) {
            $this->upsert_event( $event );
        }
        
        error_log( 'ASV Sync: ' . count( $events ) . ' Events verarbeitet.' );
    }
    /**
     * Lädt den ICS Feed herunter
     */
    private function fetch_ics() {
        $response = wp_remote_get( self::GOOGLE_ICS_URL );
        if ( is_wp_error( $response ) ) {
            error_log( 'ASV Sync Fehler: ' . $response->get_error_message() );
            return false;
        }
        return wp_remote_retrieve_body( $response );
    }
    /**
     * Erstellt oder aktualisiert einen Event in WordPress
     */
    private function upsert_event( $event_data ) {
        // Prüfen, ob Event schon existiert (anhand der UID)
        $existing = tribe_get_events( array(
            'meta_key'   => '_asv_google_uid',
            'meta_value' => $event_data['uid'],
            'posts_per_page' => 1,
        ));
        $args = array(
            'post_title'   => $event_data['summary'],
            'post_content' => $event_data['description'],
            'post_status'  => 'publish',
            'EventStartDate' => $event_data['start'],
            'EventEndDate'   => $event_data['end'],
            'EventShowMap' => true,
            'EventShowCoordinates' => true,
        );
        
        // Location Logik (einfach gehalten)
        if ( ! empty( $event_data['location'] ) ) {
             // Hier könnte man noch Venue-Logik einbauen, um Venues nicht doppelt zu erstellen
             // Für dieses Beispiel speichern wir die Location einfach als Venue-Namen beim Erstellen
             $venue_id = $this->get_create_venue( $event_data['location'] );
             if ( $venue_id ) {
                 $args['Venue'] = array( 'VenueID' => $venue_id );
             }
        }
        if ( ! empty( $existing ) ) {
            // Update
            $post_id = $existing[0]->ID;
            tribe_update_event( $post_id, $args );
        } else {
            // Neu erstellen
            $post_id = tribe_create_event( $args );
            update_post_meta( $post_id, '_asv_google_uid', $event_data['uid'] );
        }
    }
    
    /**
     * Hilfsfunktion: Venue finden oder erstellen
     */
    private function get_create_venue( $venue_name ) {
        $venue = tribe_get_venues( array( 's' => $venue_name, 'posts_per_page' => 1 ) );
        if ( ! empty( $venue ) ) {
            return $venue[0]->ID;
        }
        return tribe_create_venue( array( 'Venue' => $venue_name ) );
    }
    /**
     * Sehr einfacher ICS Parser
     * (Für robustere Lösungen wäre eine Library besser, aber wir wollen ja "Single File")
     */
    private function parse_ics( $content ) {
        $events = array();
        
        // Normalisieren der Zeilenumbrüche (unfolding)
        $content = str_replace( "\r\n ", "", $content ); 
        
        $lines = explode( "\n", $content );
        $current_event = null;
        
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( empty( $line ) ) continue;
            if ( $line === 'BEGIN:VEVENT' ) {
                $current_event = array( 'description' => '', 'location' => '' );
            } elseif ( $line === 'END:VEVENT' ) {
                if ( $current_event ) {
                    $events[] = $current_event;
                }
                $current_event = null;
            } elseif ( $current_event !== null ) {
                $parts = explode( ':', $line, 2 );
                if ( count( $parts ) < 2 ) continue;
                
                $key = $parts[0];
                $value = $parts[1];
                
                // Key Parameter entfernen (z.B. DTSTART;TZID=Europe/Berlin)
                $key_parts = explode( ';', $key );
                $clean_key = $key_parts[0];
                
                switch ( $clean_key ) {
                    case 'UID':
                        $current_event['uid'] = $value;
                        break;
                    case 'SUMMARY':
                        $current_event['summary'] = $this->ics_unescape($value);
                        break;
                    case 'DESCRIPTION':
                        $current_event['description'] = $this->ics_unescape($value);
                        break;
                    case 'LOCATION':
                        $current_event['location'] = $this->ics_unescape($value);
                        break;
                    case 'DTSTART':
                        $current_event['start'] = $this->parse_ics_date($value);
                        break;
                    case 'DTEND':
                        $current_event['end'] = $this->parse_ics_date($value);
                        break;
                }
            }
        }
        return $events;
    }
    
    private function ics_unescape( $str ) {
        return str_replace( array('\,', '\;', '\\n', '\\N'), array(',', ';', "\n", "\n"), $str );
    }
    
    private function parse_ics_date( $value ) {
        // Format YYYYMMDDTHHMMSSZ oder YYYYMMDD
        return date( 'Y-m-d H:i:s', strtotime( $value ) );
    }
}
new ASV_Calendar_Sync();