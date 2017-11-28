<?php
declare(strict_types=1);

namespace Broadlink\Factory;

final class BroadlinkFactory
{
    public static function create($host, $mac, $port, $type)
    {
        $class = null;
        switch (self::getType($type)) {
            case 0:
                return new SP1($h, $m, $p, $d);
                break;
            case 1:
                return new SP2($h, $m, $p, $d);
                break;
            case 2:
                return new RM($h, $m, $p, $d);
                break;
            case 3:
                return new A1($h, $m, $p, $d);
                break;
            case 4:
                return new MP1($h, $m, $p, $d);
                break;
            default:
        }

        if ($class !== null) {
            return new $class($host, $mac, $port, $type);
        }

        return NULL;
    }


    /**
     * @param $device
     * @return int
     */
    private static function getType($device){

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