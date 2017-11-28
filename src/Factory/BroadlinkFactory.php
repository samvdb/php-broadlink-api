<?php
declare(strict_types = 1);

namespace Broadlink\Factory;

use Broadlink\Device\AbstractDevice;
use Broadlink\Device\SP1;
use Broadlink\Device\SP2;
use Broadlink\Util\ByteUtil;

final class BroadlinkFactory
{
    /**
     * @return AbstractDevice[]
     */
    public static function discover()
    {

        $devices = array();

        $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_connect($s, '8.8.8.8', 53);  // connecting to a UDP address doesn't send packets
        socket_getsockname($s, $local_ip_address, $port);
        socket_close($s);

        $cs = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if ($cs) {
            socket_set_option($cs, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_set_option($cs, SOL_SOCKET, SO_BROADCAST, 1);
            socket_set_option($cs, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 10, 'usec' => 0));
            socket_bind($cs, '0.0.0.0', 10001);
        }

        $address = explode('.', $local_ip_address);
        $packet = ByteUtil::bytearray(0x30);

        $timezone = (int)intval(date("Z")) / -3600;
        $year = date("Y");

        if ($timezone < 0) {
            $packet[0x08] = 0xff + $timezone - 1;
            $packet[0x09] = 0xff;
            $packet[0x0a] = 0xff;
            $packet[0x0b] = 0xff;
        } else {

            $packet[0x08] = $timezone;
            $packet[0x09] = 0;
            $packet[0x0a] = 0;
            $packet[0x0b] = 0;
        }

        $packet[0x0c] = $year & 0xff;
        $packet[0x0d] = $year >> 8;
        $packet[0x0e] = intval(date("i"));
        $packet[0x0f] = intval(date("H"));
        $subyear = substr($year, 2);
        $packet[0x10] = intval($subyear);
        $packet[0x11] = intval(date('N'));
        $packet[0x12] = intval(date("d"));
        $packet[0x13] = intval(date("m"));
        $packet[0x18] = intval($address[0]);
        $packet[0x19] = intval($address[1]);
        $packet[0x1a] = intval($address[2]);
        $packet[0x1b] = intval($address[3]);
        $packet[0x1c] = $port & 0xff;
        $packet[0x1d] = $port >> 8;
        $packet[0x26] = 6;

        $checksum = 0xbeaf;

        for ($i = 0; $i < sizeof($packet); $i++) {
            $checksum += $packet[$i];
        }

        $checksum = $checksum & 0xffff;

        $packet[0x20] = $checksum & 0xff;
        $packet[0x21] = $checksum >> 8;

        socket_sendto($cs, ByteUtil::byte($packet), sizeof($packet), 0, "255.255.255.255", 80);
        while (socket_recvfrom($cs, $response, 1024, 0, $from, $port)) {

            $host = '';

            $responsepacket = ByteUtil::byte2array($response);


            $devtype = hexdec(sprintf("%x%x", $responsepacket[0x35], $responsepacket[0x34]));
            $host_array = array_slice($responsepacket, 0x36, 4);
            $mac = array_slice($responsepacket, 0x3a, 6);

            $host = implode('.', $host_array);

//            Why strip last number???
//            $host = substr($host, 0, strlen($host) - 1);

            $device = BroadlinkFactory::create($host, $mac, 80, $devtype);

            if ($device != NULL) {
                $device->setName(str_replace("\0", '', ByteUtil::byte(array_slice($responsepacket, 0x40))));
                array_push($devices, $device);
            }


        }

        if ($cs) {
            socket_close($cs);
        }

        return $devices;

    }

    /**
     * @param $h
     * @param $m
     * @param $p
     * @param $d
     * @return AbstractDevice|null
     */
    public static function create($h, $m, $p, $d)
    {
        $class = null;
        var_dump($d);
        switch (self::getType($d)) {
            case 0:
                $class = SP1::class;
                break;
            case 1:
                $class = SP2::class;
                break;
//            case 2:
//                return new RM($h, $m, $p, $d);
//                break;
//            case 3:
//                return new A1($h, $m, $p, $d);
//                break;
//            case 4:
//                return new MP1($h, $m, $p, $d);
//                break;
            default:
        }

        if ($class !== null) {
            return new $class($h, $m, $p, $d);
        }

        return NULL;
    }


    /**
     * @param $device
     * @return int
     */
    private static function getType($device)
    {

        $type = -1;

        $device = is_string($device) ? hexdec($device) : $device;

        switch ($device) {
            case 0:
                $type = 0;
                break;
            case 0x2711:
                $type = 1;
                break;
            case 0x2719:
            case 0x7919:
            case 0x271a:
            case 0x791a:
                $type = 1;
                break;
            case 0x2720:
                $type = 1;
                break;
            case 0x753e:
                $type = 1;
                break;
            case 0x2728:
                $type = 1;
                break;
            case 0x2733:
            case 0x273e:
                $type = 1;
                break;
            case 0x7530:
            case 0x7918:
                $type = 1;
                break;
            case 0x2736:
                $type = 1;
                break;
            case 0x2712:
                $type = 2;
                break;
            case 0x2737:
                $type = 2;
                break;
            case 0x273d:
                $type = 2;
                break;
            case 0x2783:
                $type = 2;
                break;
            case 0x277c:
                $type = 2;
                break;
            case 0x272a:
                $type = 2;
                break;
            case 0x2787:
                $type = 2;
                break;
            case 0x278b:
                $type = 2;
                break;
            case 0x278f:
                $type = 2;
                break;
            case 0x2714:
                $type = 3;
                break;
            case 0x4EB5:
                $type = 4;
                break;
            default:
                break;
        }

        return $type;
    }
}