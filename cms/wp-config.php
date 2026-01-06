<?php
# BEGIN fs_active_plugins workaround
if ( ! isset( $GLOBALS['fs_active_plugins'] ) || ! is_object( $GLOBALS['fs_active_plugins'] ) ) {
    $GLOBALS['fs_active_plugins'] = (object) ['plugins' => []];
}
# END fs_active_plugins workaround
/**
 * Grundeinstellungen für WordPress
 *
 * Zu diesen Einstellungen gehören:
 *
 * * MySQL-Zugangsdaten,
 * * Tabellenpräfix,
 * * Sicherheitsschlüssel
 * * und ABSPATH.
 *
 * Mehr Informationen zur wp-config.php gibt es auf der
 * {@link https://codex.wordpress.org/Editing_wp-config.php wp-config.php editieren}
 * Seite im Codex. Die Zugangsdaten für die MySQL-Datenbank
 * bekommst du von deinem Webhoster.
 *
 * Diese Datei wird zur Erstellung der wp-config.php verwendet.
 * Du musst aber dafür nicht das Installationsskript verwenden.
 * Stattdessen kannst du auch diese Datei als wp-config.php mit
 * deinen Zugangsdaten für die Datenbank abspeichern.
 *
 * @package WordPress
 */

// ** MySQL-Einstellungen ** //
/**   Diese Zugangsdaten bekommst du von deinem Webhoster. **/

/**
 * Ersetze datenbankname_hier_einfuegen
 * mit dem Namen der Datenbank, die du verwenden möchtest.
 */
define( 'DB_NAME', 'v7gsbfj_asv' );

/**
 * Ersetze benutzername_hier_einfuegen
 * mit deinem MySQL-Datenbank-Benutzernamen.
 */
define( 'DB_USER', 'v7gsbfj_asv' );

/**
 * Ersetze passwort_hier_einfuegen mit deinem MySQL-Passwort.
 */
define( 'DB_PASSWORD', '!^u288i0p70Q@kic');

/**
 * Ersetze localhost mit der MySQL-Serveradresse.
 */
define( 'DB_HOST', 'localhost' );

/**
 * Der Datenbankzeichensatz, der beim Erstellen der
 * Datenbanktabellen verwendet werden soll
 */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Der Collate-Type sollte nicht geändert werden.
 */
define('DB_COLLATE', '');

/**#@+
 * Sicherheitsschlüssel
 *
 * Ändere jeden untenstehenden Platzhaltertext in eine beliebige,
 * möglichst einmalig genutzte Zeichenkette.
 * Auf der Seite {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * kannst du dir alle Schlüssel generieren lassen.
 * Du kannst die Schlüssel jederzeit wieder ändern, alle angemeldeten
 * Benutzer müssen sich danach erneut anmelden.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'f)o1XVuh;Do~sW+eR|=&Awf?n 8_i~-*-/)~Q6l5o[:(y7w7_D9]untBYLu@q$,s' );
define( 'SECURE_AUTH_KEY',  'i.?h=C.MB~#:ST~ )gG8~DF=;6L|4`dboc7uP-zTkc+)v=Hv5ES |+{84A@);i@z' );
define( 'LOGGED_IN_KEY',    '<*^WeH3YS6+3VnVUA??j]<]_n9:(L{%v&G>+zsAUR%VFCfj^]%]4-?v03qVb >H9' );
define( 'NONCE_KEY',        'zvLmUa=d[!b:H;hgKQQ+c>Un}_H5Hv2NH%Vu-ZdvaAcL_ZH^B^cD.M6n,?5_Qc0c' );
define( 'AUTH_SALT',        'k*g m0rM{A%65GQ9[v&A<49R-2lnax6&Oj277&V#Fvdm_>|R^}N[[c$V!QdvC5w;' );
define( 'SECURE_AUTH_SALT', 'q:&(+#i!JN2E?@#gFz~w<O_,rLzM&)nzpv;6U(N-bLjM<.}X#zM.[o<dX#ypFg8Y' );
define( 'LOGGED_IN_SALT',   'ipnBR-,,yTL2Te_6S655 u7,66/iHsvBBT~rLw[muco6wv^rREv%DC-_O6u$BRB`' );
define( 'NONCE_SALT',       '-$>S,uTShaBQ%X!@7+9xr^$<*=Ei9$!I]`x{gtrq4-{uI]vI>,-;59(7U-qewX(!' );

/**#@-*/

/**
 * WordPress Datenbanktabellen-Präfix
 *
 * Wenn du verschiedene Präfixe benutzt, kannst du innerhalb einer Datenbank
 * verschiedene WordPress-Installationen betreiben.
 * Bitte verwende nur Zahlen, Buchstaben und Unterstriche!
 */
$table_prefix = 'wp_';

/**
 * Für Entwickler: Der WordPress-Debug-Modus.
 *
 * Setze den Wert auf „true“, um bei der Entwicklung Warnungen und Fehler-Meldungen angezeigt zu bekommen.
 * Plugin- und Theme-Entwicklern wird nachdrücklich empfohlen, WP_DEBUG
 * in ihrer Entwicklungsumgebung zu verwenden.
 *
 * Besuche den Codex, um mehr Informationen über andere Konstanten zu finden,
 * die zum Debuggen genutzt werden können.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* Das war’s, Schluss mit dem Bearbeiten! Viel Spaß. */
/* That's all, stop editing! Happy publishing. */

/** Der absolute Pfad zum WordPress-Verzeichnis. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Definiert WordPress-Variablen und fügt Dateien ein.  */
require_once( ABSPATH . 'wp-settings.php' );
