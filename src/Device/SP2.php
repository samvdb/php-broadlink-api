<?php
declare(strict_types=1);

namespace Broadlink\Device;

use Broadlink\Util\ByteUtil;
use Broadlink\Util\Encryption;

class SP2 extends AbstractDevice
{
    public function Set_Power($state){

        $packet = ByteUtil::bytearray(16);
        $packet[0] = 0x02;
        $packet[4] = $state ? 1 : 0;

        $this->getClient()->send(0x6a, $packet);
    }

    public function Check_Power(){

        $packet = ByteUtil::bytearray(16);
        $packet[0] = 0x01;

        $response = $this->getClient()->send(0x6a, $packet);
        $err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));


        if($err == 0){
            $enc_payload = array_slice($response, 0x38);

            if(count($enc_payload) > 0){

                $payload = ByteUtil::byte2array(Encryption::decrypt($this->getClient()->getKey(), ByteUtil::byte($enc_payload), $this->getClient()->getIv()));
                return $payload[0x4] ? true : false;
            }

        }

        return false;


    }

    /**
     * @return int
     */
    public function getType()
    {
        return 0x2712;
    }
}