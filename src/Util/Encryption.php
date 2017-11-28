<?php
declare(strict_types=1);

namespace Broadlink\Util;

final class Encryption
{
    public static function encrypt($key, $data, $iv) {
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
    }

    public static function decrypt($key, $data, $iv) {
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
    }
}