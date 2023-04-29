# IP_tools

A compilation of the most compact, fast, efficient and precise tools in PHP 8 for managing IP addresses.

All in a single file !

## How to use

```php
require_once 'IP_tools.php';
$my_ip_tools = new IP_tools();

// GETTING
// Get IP from user.
$ip = $my_ip_tools->getUserIP();

// CHECKING
// Check if an IP address is valid.
$my_ip_tools->isValidIP($ip);
// Check if an IPv4 address is valid.
$my_ip_tools->isValidIPv4($ip);
// Check if an IPv6 address is valid.
$my_ip_tools->isValidIPv6($ip);

// GENERATING
// Generate random IPv4.
$new_ipv4 = $my_ip_tools->generateRandomIPv4();
// Generate random IPv6.
$new_ipv6 = $my_ip_tools->generateRandomIPv6();

// CONVERTING
// Convert an IP to an integer
$long_ipv4 = $my_ip_tools->ip2long($new_ipv4);
// For IPv6 addresses, the result is truncated because the system can not represent such big numbers.
$long_ipv6 = $my_ip_tools->ip2long($new_ipv6);
// Convert an integer to its equivalent IP.
$new_ipv4 == $my_ip_tools->long2ip($long_ipv4); // TRUE
$new_ipv6 == $my_ip_tools->long2ip($long_ipv6); // FALSE (due to misrepresentation of huge numbers). Still usable !
// Convert an IPv4 or IPv6 to its binary equivalent.
$binaryIPv4 = $my_ip_tools->ip2binary($new_ipv4);
$binaryIPv6 = $my_ip_tools->ip2binary($new_ipv6);
// Convert binary IP to its human-readable equivalent.
$new_ipv4 == $my_ip_tools->binary2ip($binaryIPv4); // TRUE
$new_ipv6 == $my_ip_tools->binary2ip($binaryIPv6); // TRUE

// INFERRING
// Get country of an IP address. You need an external file that you can grab here (for free) : https://download.ip2location.com/lite/
$ipv4_country = $my_ip_tools->getCountryIPUsingIP2locateFile($ip, 'path/to/your/locatefile_ipv4.csv');
// Works for IPv6 too ! Not perfect though...
$ipv6_country = $my_ip_tools->getCountryIPUsingIP2locateFile($ip, 'path/to/your/locatefile_ipv6.csv');
```

