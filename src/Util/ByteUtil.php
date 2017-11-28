<?php
declare(strict_types=1);

namespace Broadlink\Util;

final class ByteUtil
{
    /**
     * @param $size
     * @return array
     */
    public static function bytearray($size){

        $packet = array();

        for($i = 0 ; $i < $size ; $i++){
            $packet[$i] = 0;
        }

        return $packet;
    }

    /**
     * @param $data
     * @return array
     */
    public static function byte2array($data){

        return array_merge(unpack('C*', $data));
    }

    /**
     * @param $array
     * @return string
     */
    public static function byte($array){

        return implode(array_map("chr", $array));
    }

}