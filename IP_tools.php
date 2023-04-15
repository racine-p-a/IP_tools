<?php

class IP_tools
{
    /**
     * Checks if IP is valid IPV4.
     * @param string $ip
     * @return bool
     */
    public function isValidIPv4(string $ip=''): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * Checks if IP is valid IPV6.
     * @param string $ip
     * @return bool
     */
    public function isValidIPv6(string $ip=''): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * Checks if the IP is valid IPv4 or IPv6.
     * @param string $ip
     * @return bool
     */
    public function isValidIP(string $ip=''): bool
    {
        return $this->isValidIPv4($ip) OR $this->isValidIPv6($ip);
    }

    /**
     * Generates a random IPv4 address.
     * @return bool|string
     */
    public function generateRandomIPv4(): bool|string
    {
        return long2ip(rand(0, 4294967295));
    }

    /**
     * Generates a random IPv6 address.
     * @return bool|string
     */
    public function generateRandomIPv6(): bool|string
    {
        // Very dumb way to create it. Smarter way to do it should exist (random hex number maybe ?)
        $hex = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'];

        // A public IPv6 (Global Unicast) shall have this aspect : 2000::/3
        // All adresses from 2000:0:0:0:0:0:0:0 to 3fff:ffff:ffff:ffff:ffff:ffff:ffff:ffff
        $generatedIP = strval(rand(2, 3)).$hex[array_rand($hex, 1)].$hex[array_rand($hex, 1)].$hex[array_rand($hex, 1)];
        for ($i=0; $i<7; $i++){
            $generatedIP .= ':'.$hex[array_rand($hex, 1)].$hex[array_rand($hex, 1)].$hex[array_rand($hex, 1)].$hex[array_rand($hex, 1)];
        }
        return $generatedIP;
    }

    /**
     * Converts an IP address to a number. The result is truncated for IPv6 because the system can not represent such
     * big numbers.
     * @param string $ip
     * @return int
     */
    public function ip2long(string $ip=''): int
    {
        if ($this->isValidIPv4($ip)){
            // For IPv4, wa can use the intern function.
            return ip2long($ip);
        } else if ($this->isValidIPv6($ip)){
            // For IPv6, we use either the gmp library if it is installed or do it by ourselves.
            // Both solutions below are incorrect because of a lack of proper representation of numbers so huge.
            // Many methods tested lead to the same truncated result.
            if (extension_loaded('gmp', )){
                return (int) gmp_import(inet_pton($ip));
            } else {
                // https://stackoverflow.com/a/29055822/6044950
                $str = '';
                foreach (unpack('C*', inet_pton($ip)) as $byte) {
                    $str .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
                }
                $str = ltrim($str, '0');
                return intval(base_convert($str, 2, 10));
            }
        }
        return 0;
    }

    /**
     * Converts a number to its equivalent IP address. The result is truncated for IPv6 because the system can not
     * represent such huge numbers.
     * @param int $long
     * @return string
     */
    public function long2ip(int $long=0): string{
        // IPv4
        if($long<= 4294967295){
            return long2ip($long);
        }
        // IPv6 (result is mostly truncated dur to misrepresentation of huge numbers)
        if (extension_loaded('gmp', )){
            $bin = gmp_strval(gmp_init($long, 10), 2);
            $bin = str_pad($bin, 128, '0', STR_PAD_LEFT);
            $ip = array();
            for ($bit = 0; $bit <= 7; $bit++) {
                $bin_part = substr($bin, $bit * 16, 16);
                $ip[] = dechex(bindec($bin_part));
            }
            $ip = implode(':', $ip);
            return inet_ntop(inet_pton($ip));
        }
        // IPv6 (without gmp)
        // https://openclassrooms.com/forum/sujet/ipv4-et-ipv6-avec-php-14034
        // https://stackoverflow.com/questions/18276757/php-convert-ipv6-to-number
        $ipv6='';
        $bin = gmp_strval(gmp_init($long,10),2);
        if (strlen($bin) < 128) {
            $pad = 128 - strlen($bin);
            for ($i = 1; $i <= $pad; $i++) {
                $bin = "0".$bin;
            }
        }
        $bits = 0;
        while ($bits <= 7) {
            $bin_part = substr($bin,($bits*16),16);
            $ipv6 .= dechex(bindec($bin_part)).":";
            $bits++;
        }
        // compress
        return inet_ntop(inet_pton(substr($ipv6,0,-1)));
    }

    /**
     * @param string $ip
     * @param string $fileIP2locate You can get these files here legally and for free here https://download.ip2location.com/lite/
     * @return string
     * @throws Exception
     */
    public function getCountryIPUsingIP2locateFile(string $ip, string $fileIP2locate=''): string
    {
        $longIP = $this->ip2long($ip);
        $largeFile = new BigFileIterator($fileIP2locate);
        $iterator = $largeFile->iterate("Text");
        foreach ($iterator as $line) {
            $ligne_csv = str_getcsv($line, ',', '"');
            if (intval($ligne_csv[0])<= $longIP && $longIP <= intval($ligne_csv[1])){
                return $ligne_csv[3];
            }
        }
        return 'Unknown';
    }


    /**
     * Retrieve the IP requesting the called page.
     * @return string
     */
    public function getUserIP(): string {
        // https://stackoverflow.com/a/72866863/6044950
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}

class BigFileIterator
{
    protected SplFileObject $file;

    /**
     * BigFileIterator constructor.
     * @param $filename
     * @param string $mode
     * @throws Exception
     */
    public function __construct($filename, string $mode = "r")
    {
        $pathToFile = getcwd().'/'.$filename;
        if (!file_exists($pathToFile)) {

            throw new Exception("File to iterate not found");

        }
        $this->file = new SplFileObject($pathToFile, $mode);
    }

    protected function iterateText(): int|Generator
    {
        $count = 0;

        while (!$this->file->eof()) {

            yield $this->file->fgets();

            $count++;
        }
        return $count;
    }

    protected function iterateBinary($bytes): Generator
    {
        $count = 0;

        while (!$this->file->eof()) {

            yield $this->file->fread($bytes);

            $count++;
        }
    }

    public function iterate($type = "Text", $bytes = NULL): NoRewindIterator
    {
        if ($type == "Text") {

            return new NoRewindIterator($this->iterateText());

        } else {

            return new NoRewindIterator($this->iterateBinary($bytes));
        }

    }
}