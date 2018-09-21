<?php
//feel free to edit these as desired. They're just suggestions.

////////////////////////////////////////////////////////////////////////////////

// Security Headers can be scanned using https://securityheaders.io/

/*
1. Content Security Policy

The content-security-policy HTTP header provides an additional layer of security. This policy helps prevent attacks such as Cross Site Scripting (XSS) and other code injection attacks by defining content sources which are approved and thus allowing the browser to load them.

** Not specified because you cannot predict what content sources will be required by the users of UserSpice **
*/


/*
2. HTTP Strict Transport Security (HSTS)

The strict-transport-security header is a security enhancement that restricts web browsers to access web servers solely over HTTPS. This ensures the connection cannot be establish through an insecure HTTP connection which could be susceptible to attacks.
*/

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

if ($protocol === "https://") {
header("Strict-Transport-Security:max-age=31536000; includeSubdomains; preload");
}


/*
3. X-Frame-Options

The x-frame-options header provides clickjacking protection by not allowing iframes to load on your site.
helps prevent clickjacking by indicating to a browser that it should not render the page in a frame (or an iframe or object).
*/

header("X-Frame-Options: SAMEORIGIN");


/*
4. X-XSS-Protection

The x-xss-protection header is designed to enable the cross-site scripting (XSS) filter built into modern web browsers. This is usually enabled by default, but using it will enforce it.

The reflected-xss directive configures the built in heuristics a user agent has to filter or block reflected XSS attacks.

    Allow - Allows reflected XSS attacks.
    Block - Block reflected XSS attacks.
    Filter - Filter the reflected XSS attack.
*/

header("X-XSS-Protection: 1; mode=block");


/*
5. X-Content-Type-Options

The X-content-type header prevents Internet Explorer and Google Chrome from sniffing a response away from the declared content-type. This helps reduce the danger of drive-by downloads and helps treat the content the right way.
X-Content-Type-Options header instructs IE not to sniff mime types, preventing attacks related to mime-sniffing.
*/

header("X-Content-Type-Options: nosniff");


/*
6. The referrer directive specifies information for the referrer header in links away from the page.

    No Referrer - Prevents the UA sending a referrer header.
    No Referrer When Downgrade - Prevents the UA sending a referrer header when navigating from https to http.
    Origin Only - Allows the UA to only send the origin in the referrer header.
    Origin When Cross Origin - Allows the UA to only send the origin in the referrer header when making cross-origin requests.
    Unsafe URL - Allows the UA to send the full URL in the referrer header with same-origin and cross-origin requests. This is unsafe.
*/

header("Referrer-Policy: no-referrer-when-downgrade");


// 7. There is no direct security risk, but exposing an outdated (and possibly vulnerable) version of PHP may be an invitation for people to try and attack it.

header_remove("X-Powered-By");

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 ?>
