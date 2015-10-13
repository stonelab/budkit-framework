<?php


namespace Budkit\Datastore;

/**
 * Crypt_RSA class, derived from Crypt_RSA_ErrorHandler
 *
 * Provides the following functions:
 *
 *  1. setParams($params) - sets parameters of current object
 *  2. encrypt($plain_data, $key = null) - encrypts data
 *  3. decrypt($enc_data, $key = null) - decrypts data
 *  4. createSign($doc, $private_key = null) - signs document by private key
 *  5. validateSign($doc, $signature, $public_key = null) - validates signature of document
 *
 * Example usage:
 *
 *     // creating an error handler
 *     $error_handler = create_function('$obj', 'echo "error: ", $obj->getMessage(), "\n"');
 *
 *     // 1024-bit key pair generation
 *     $key_pair = new Crypt_RSA_KeyPair(1024);
 *
 *     // check consistence of Crypt_RSA_KeyPair object
 *     $error_handler($key_pair);
 *
 *     // creating Crypt_RSA object
 *     $rsa_obj = new Crypt_RSA;
 *
 *     // check consistence of Crypt_RSA object
 *     $error_handler($rsa_obj);
 *
 *     // set error handler on Crypt_RSA object ( see Crypt/RSA/ErrorHandler.php for details )
 *     $rsa_obj->setErrorHandler($error_handler);
 *
 *     // encryption (usually using public key)
 *     $enc_data = $rsa_obj->encrypt($plain_data, $key_pair->getPublicKey());
 *
 *     // decryption (usually using private key)
 *     $plain_data = $rsa_obj->decrypt($enc_data, $key_pair->getPrivateKey());
 *
 *     // signing
 *     $signature = $rsa_obj->createSign($document, $key_pair->getPrivateKey());
 *
 *     // signature checking
 *     $is_valid = $rsa_obj->validateSign($document, $signature, $key_pair->getPublicKey());
 *
 *     // signing many documents by one private key
 *     $rsa_obj = new Crypt_RSA(array('private_key' => $key_pair->getPrivateKey()));
 *     // check consistence of Crypt_RSA object
 *     $error_handler($rsa_obj);
 *     // set error handler ( see Crypt/RSA/ErrorHandler.php for details )
 *     $rsa_obj->setErrorHandler($error_handler);
 *     // sign many documents
 *     $sign_1 = $rsa_obj->sign($doc_1);
 *     $sign_2 = $rsa_obj->sign($doc_2);
 *     //...
 *     $sign_n = $rsa_obj->sign($doc_n);
 *
 *     // changing default hash function, which is used for sign
 *     // creating/validation
 *     $rsa_obj->setParams(array('hash_func' => 'md5'));
 *
 *     // using factory() method instead of constructor (it returns PEAR_Error object on failure)
 *     $rsa_obj = &Crypt_RSA::factory();
 *     if (PEAR::isError($rsa_obj)) {
 *         echo "error: ", $rsa_obj->getMessage(), "\n";
 *     }
 **/
final class Encrypt
{

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
    public function __construct($config = [])
    {

        //$config = Config::getParamSection("encrypt");

        if (is_array($config) && !empty($config)) {
            foreach ($config as $var => $value) {
                $this->$var = $value;
            }
        }
    }

    /**
     * Checks if we can use mcrypt for encryption
     *
     * @return boolean True or False if encrypt exists
     */
    public static function mcryptExists()
    {
    }

    /**
     * Encodes a given string
     *
     * @param string $string
     * @return string encoded string
     */
    public function encode($string)
    {

        if (empty($string) || empty($this->key)) {
            throw new \Exception("Can not encrypt with an empty key or no data");
            return false;
        }

        $publicKey = $this->generateKey($string);
        $privateKey = $this->key;


        //Get a SHA-1 hashKey 
        $hashKey = sha1($privateKey . "+" . (string)$publicKey);

        $stringArray = str_split($string);
        $hashArray = str_split($hashKey);
        $cipherNoise = str_split($publicKey, 2);

        $counter = 0;

        for ($i = 0; $i < sizeof($stringArray); $i++) {
            if ($counter > 40)
                $counter = 0;
            $cryptChar = ord((string)$stringArray[$i]) + ord((string)$hashArray[$counter]);
            $cryptChar -= floor($cryptChar / 127) * 127;
            $cipherStream[$i] = dechex($cryptChar);
            $counter++;
        }
        //print_R($cipherNoise);

        $cipherNoiseSize = count($cipherNoise);

        $cipher = implode("|x", $cipherStream);
        $cipher .= "|x::|x" . ord((string)$cipherNoiseSize) . "|x";
        $cipher .= implode("|x", $cipherNoise);


        //echo $cipher;

        return $cipher;
    }

    /**
     * Generates a random encryption key
     *
     * @param string $txt
     * @return string
     */
    public static function generateKey($txt, $length = null)
    {

        date_default_timezone_set('UTC');

        $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

        $maxlength = (empty($length) || (int)$length > strlen($possible)) ? strlen($possible) : (int)$length;
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
     * Returns the protected encryption key
     *
     * NOTE: This method is left public, because the session might need to know
     * what the encryption key is, to decipher session keys. @TODO fix this and
     * make this method protected or private
     *
     * @property-read string $key The encryption key property
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /*
     * Encrypts a given text
     * 
     * @return string
     */

    /**
     * Decodes a previously encode string.
     *
     * @param string $encrypted
     * @return string Decoded string
     */
    public function decode($encrypted)
    {

        //$cipher_all = explode("/", $cipher_in);
        //$cipher = $cipher_all[0];

        $blocks = explode("|x", $encrypted);
        $delimiter = array_search("::", $blocks);


        $cipherStream = array_slice($blocks, 0, (int)$delimiter);


        unset($blocks[(int)$delimiter]);
        unset($blocks[(int)$delimiter + 1]);

        $publicKeyArray = array_slice($blocks, (int)$delimiter);

        $publicKey = implode('', $publicKeyArray);
        $privateKey = $this->key;

        $hashKey = sha1($privateKey . "+" . (string)$publicKey);
        $hashArray = str_split($hashKey);

        $counter = 0;
        for ($i = 0; $i < sizeof($cipherStream); $i++) {
            if ($counter > 40)
                $counter = 0;
            $cryptChar = hexdec($cipherStream[$i]) - ord((string)$hashArray[$counter]);
            $cryptChar -= floor($cryptChar / 127) * 127;
            $cipherText[$i] = chr($cryptChar);
            $counter++;
        }

        $plaintext = implode("", $cipherText);

        return $plaintext;
    }

    public function mcryptEncode()
    {

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
    public function mcryptDecode($cipher, $key, $data, $mode)
    {

    }

    /**
     * Returns an sha1, MD5 and sha224 hash (in that order) of a given string
     *
     * @param string $string
     * @param string $key
     * @return string
     */
    public function hash($string, $key = null)
    {

        $publicKey = is_null($key) ? $this->generateKey($string) : $key;

        //echo $publicKey;
        $hashKey1 = sha1($string . $publicKey);
        $hashKey2 = md5($hashKey1);
        $hashKey3 = hash('sha224', $hashKey2);

        return $hashKey3 . ":" . $publicKey;
    }

}