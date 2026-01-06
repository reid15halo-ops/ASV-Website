<?php
/**
 * ASV Calendar Proxy
 * Fetches Google Calendar ICS and serves it with CORS headers
 * to allow fetching from static HTML/JS.
 */

// Allow access from any origin (or restrict to your domain)
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/calendar; charset=utf-8");

// Google Calendar ICS URL
// Account: asvgrossostheimjugend@gmail.com
$ics_url = 'https://calendar.google.com/calendar/ical/1ccfad68a0dff3c20173ba00986bc6d4327b8ddb71011dd1e93238aab311c9dc%40group.calendar.google.com/public/basic.ics';

// Fetch the content
$content = file_get_contents($ics_url);

if ($content === false) {
    http_response_code(500);
    echo "Error fetching calendar";
    exit;
}

// Output the content
echo $content;
