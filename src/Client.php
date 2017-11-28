<?php
declare(strict_types=1);

namespace Broadlink;

use Broadlink\Device\AbstractDevice;
use Broadlink\Factory\BroadlinkFactory;
use Broadlink\Helper\SocketHelper;
use Broadlink\Util\ByteUtil;
use Broadlink\Util\Encryption;

class Client
{
    /**
     * @var AbstractDevice
     */
    protected $device;

    protected $timeout = 10;
    protected $count;
    protected $key = [0x09, 0x76, 0x28, 0x34, 0x3f, 0xe9, 0x9e, 0x23, 0x76, 0x5c, 0x15, 0x13, 0xac, 0xcf, 0x8b, 0x02];
    protected $iv = [0x56, 0x2e, 0x17, 0x99, 0x6d, 0x09, 0x3d, 0x28, 0xdd, 0xb3, 0xba, 0x69, 0x5a, 0x2e, 0x6f, 0x58];
    protected $id = [0, 0, 0, 0];

    /**
     * @var SocketHelper
     */
    protected $socketHelper;

    /**
     * Client constructor.
     * @param AbstractDevice $device
     */
    public function __construct(AbstractDevice $device )
    {
        $this->device = $device;

        $this->count = rand(0, 0xffff);

        $this->socketHelper = new SocketHelper($device->getHost(), $device->getPort());
    }

    /**
     * @param $command
     * @param $payload
     * @return array
     */
    public function send($command, $payload)
    {
        $this->count = ($this->count + 1) & 0xffff;

        $mac = $this->device->getMacArray();

        $packet = ByteUtil::bytearray(0x38);
        $packet[0x00] = 0x5a;
        $packet[0x01] = 0xa5;
        $packet[0x02] = 0xaa;
        $packet[0x03] = 0x55;
        $packet[0x04] = 0x5a;
        $packet[0x05] = 0xa5;
        $packet[0x06] = 0xaa;
        $packet[0x07] = 0x55;
        $packet[0x24] = 0x2a;
        $packet[0x25] = 0x27;
        $packet[0x26] = $command;
        $packet[0x28] = $this->count & 0xff;
        $packet[0x29] = $this->count >> 8;
        $packet[0x2a] = $mac[0];
        $packet[0x2b] = $mac[1];
        $packet[0x2c] = $mac[2];
        $packet[0x2d] = $mac[3];
        $packet[0x2e] = $mac[4];
        $packet[0x2f] = $mac[5];
        $packet[0x30] = $this->id[0];
        $packet[0x31] = $this->id[1];
        $packet[0x32] = $this->id[2];
        $packet[0x33] = $this->id[3];
        $checksum = 0xbeaf;
        for($i = 0 ; $i < sizeof($payload) ; $i++){
            $checksum += $payload[$i];
            $checksum = $checksum & 0xffff;
        }
        $aes = ByteUtil::byte2array(Encryption::encrypt($this->getKey(), ByteUtil::byte($payload), $this->getIv()));
        $packet[0x34] = $checksum & 0xff;
        $packet[0x35] = $checksum >> 8;
        for($i = 0 ; $i < sizeof($aes) ; $i++){
            array_push($packet, $aes[$i]);
        }
        $checksum = 0xbeaf;
        for($i = 0 ; $i < sizeof($packet) ; $i++){
            $checksum += $packet[$i];
            $checksum = $checksum & 0xffff;
        }
        $packet[0x20] = $checksum & 0xff;
        $packet[0x21] = $checksum >> 8;
//        $starttime = time();

        return $this->socketHelper->send($packet);
    }

    public function auth(){

        $payload = ByteUtil::bytearray(0x50);

        $payload[0x04] = 0x31;
        $payload[0x05] = 0x31;
        $payload[0x06] = 0x31;
        $payload[0x07] = 0x31;
        $payload[0x08] = 0x31;
        $payload[0x09] = 0x31;
        $payload[0x0a] = 0x31;
        $payload[0x0b] = 0x31;
        $payload[0x0c] = 0x31;
        $payload[0x0d] = 0x31;
        $payload[0x0e] = 0x31;
        $payload[0x0f] = 0x31;
        $payload[0x10] = 0x31;
        $payload[0x11] = 0x31;
        $payload[0x12] = 0x31;
        $payload[0x1e] = 0x01;
        $payload[0x2d] = 0x01;
        $payload[0x30] = ord('T');
        $payload[0x31] = ord('e');
        $payload[0x32] = ord('s');
        $payload[0x33] = ord('t');
        $payload[0x34] = ord(' ');
        $payload[0x35] = ord(' ');
        $payload[0x36] = ord('1');

        $response = $this->send(0x65, $payload);
        $enc_payload = array_slice($response, 0x38);

        $payload = ByteUtil::byte2array(Encryption::decrypt($this->getKey(), ByteUtil::byte($enc_payload), $this->getIv()));

        $this->id = array_slice($payload, 0x00, 4);
        $this->key = array_slice($payload, 0x04, 16);
    }


    /**
     * @return string
     */
    public function getKey()
    {
        return implode(array_map("chr", $this->key));
    }

    /**
     * @return string
     */
    public function getIv()
    {
        return implode(array_map("chr", $this->iv));
    }

}