<?php


namespace Budkit\Datastore;

/**
 * What is the purpose of this class, in one sentence?
 *
 * How does this class achieve the desired purpose?
 *
 * @category   Library
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/encrypt
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
final class Encrypt{

    /**
     * The globally defined encryption Key
     * 
     * @var string 
     */
    protected $key;

    /**
     * Encryption clas constructor
     * Loads the encryption configuration
     * 
     * @return void
     */
    public function __construct($config = []){

        //$config = Config::getParamSection("encrypt");

        if (is_array($config) && !empty($config)) {
            foreach ($config as $var => $value) {
                $this->$var = $value;
            }
        }
    }

    /**
     * Generates a random encryption key
     * 
     * @param string $txt
     * @return string 
     */
    public static function generateKey($txt, $length=null) {

        date_default_timezone_set('UTC');

        $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

        $maxlength = ( empty($length)||(int)$length > strlen($possible)  ) ? strlen($possible) : (int)$length;
        $random = "";

        $i = 0;

        while ($i < ($maxlength / 5)) {
            // pick a random character from the possible ones
            $char = substr($possible, mt_rand(0, $maxlength - 1), 1);
            if (!strstr($random, $char)) {
                $random .= $char;
                $i++;
            }
        }

        $salt = time() . $random;
        $rand = mt_rand();
        $key = md5($rand . $txt . $salt);

        return $key;
    }

    /**
     * Encodes a given string
     * 
     * @param string $string
     * @return string encoded string
     */
    public function encode($string) {

        if (empty($string) || empty($this->key) ) {
            throw new \Exception("Can not encrypt with an empty key or no data");
            return false;
        }

        $publicKey = $this->generateKey($string);
        $privateKey = $this->key;


        //Get a SHA-1 hashKey 
        $hashKey = sha1($privateKey . "+" . (string) $publicKey);

        $stringArray = str_split($string);
        $hashArray = str_split($hashKey);
        $cipherNoise = str_split($publicKey, 2);

        $counter = 0;

        for ($i = 0; $i < sizeof($stringArray); $i++) {
            if ($counter > 40)
                $counter = 0;
            $cryptChar = ord((string) $stringArray[$i]) + ord((string) $hashArray[$counter]);
            $cryptChar -= floor($cryptChar / 127) * 127;
            $cipherStream[$i] = dechex($cryptChar);
            $counter++;
        }
        //print_R($cipherNoise);

        $cipherNoiseSize = count($cipherNoise);

        $cipher = implode("|x", $cipherStream);
        $cipher .= "|x::|x" . ord((string) $cipherNoiseSize) . "|x";
        $cipher .= implode("|x", $cipherNoise);


        //echo $cipher;

        return $cipher;
    }

    /**
     * Returns the protected encryption key
     * 
     * NOTE: This method is left public, because the session might need to know
     * what the encryption key is, to decipher session keys. @TODO fix this and
     * make this method protected or private
     * 
     * @property-read string $key The encryption key property
     * @return string 
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Decodes a previously encode string.
     * 
     * @param string $encrypted
     * @return string Decoded string 
     */
    public function decode($encrypted) {

        //$cipher_all = explode("/", $cipher_in);
        //$cipher = $cipher_all[0];

        $blocks = explode("|x", $encrypted);
        $delimiter = array_search("::", $blocks);


        $cipherStream = array_slice($blocks, 0, (int) $delimiter);


        unset($blocks[(int) $delimiter]);
        unset($blocks[(int) $delimiter + 1]);

        $publicKeyArray = array_slice($blocks, (int) $delimiter);

        $publicKey = implode('', $publicKeyArray);
        $privateKey = $this->key;

        $hashKey = sha1($privateKey . "+" . (string) $publicKey);
        $hashArray = str_split($hashKey);

        $counter = 0;
        for ($i = 0; $i < sizeof($cipherStream); $i++) {
            if ($counter > 40)
                $counter = 0;
            $cryptChar = hexdec($cipherStream[$i]) - ord((string) $hashArray[$counter]);
            $cryptChar -= floor($cryptChar / 127) * 127;
            $cipherText[$i] = chr($cryptChar);
            $counter++;
        }

        $plaintext = implode("", $cipherText);

        return $plaintext;
    }

    /*
     * Encrypts a given text
     * 
     * @return string
     */

    public function mcryptEncode() {
        
    }

    /**
     * Decrypts a previously encrypted text with given parameters
     * 
     * @param type $cipher
     * @param type $key
     * @param type $data
     * @param type $mode 
     * 
     * @return string
     */
    public function mcryptDecode($cipher, $key, $data, $mode) {
        
    }

    /**
     * Checks if we can use mcrypt for encryption
     * 
     * @return boolean True or False if encrypt exists
     */
    public static function mcryptExists() {}

    /**
     * Returns an sha1, MD5 and sha224 hash (in that order) of a given string
     * 
     * @param string $string
     * @param string $key
     * @return string 
     */
    public function hash($string, $key = null) {

        $publicKey = is_null($key) ? $this->generateKey($string) : $key;
        
        //echo $publicKey;
        $hashKey1 = sha1($string . $publicKey);
        $hashKey2 = md5($hashKey1);
        $hashKey3 = hash('sha224', $hashKey2);

        return $hashKey3 . ":" . $publicKey;
    }

}