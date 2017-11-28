<?php
declare(strict_types=1);

namespace Broadlink\Helper;

class ModelHelper
{
    /**
     * @var int
     */
    private $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getModel($devType = null){

        $type = "Unknown";

        $typeToCheck = $devType ? : $this->type;

        switch ($typeToCheck) {
            case 0:
                $type = "SP1";
                break;
            case 0x2711:
                $type = "SP2";
                break;
            case 0x2719:
            case 0x7919:
            case 0x271a:
            case 0x791a:
                $type = "Honeywell SP2";
                break;
            case 0x2720:
                $type = "SPMini";
                break;
            case 0x753e:
                $type = "SP3";
                break;
            case 0x2728:
                $type = "SPMini2";
                break;
            case 0x2733:
            case 0x273e:
                $type = "OEM branded SPMini";
                break;
            case 0x7530:
            case 0x7918:
                $type = "OEM branded SPMini2";
                break;
            case 0x2736:
                $type = "SPMiniPlus";
                break;
            case 0x2712:
                $type = "RM2";
                break;
            case 0x2737:
                $type = "RM Mini";
                break;
            case 0x273d:
                $type = "RM Pro Phicomm";
                break;
            case 0x2783:
                $type = "RM2 Home Plus";
                break;
            case 0x277c:
                $type = "RM2 Home Plus";
                break;
            case 0x277c:
                $type = "RM2 Home Plus GDT";
                break;
            case 0x272a:
                $type = "RM2 Pro Plus";
                break;
            case 0x2787:
                $type = "RM2 Pro Plus2";
                break;
            case 0x278b:
                $type = "RM2 Pro Plus BL";
                break;
            case 0x278f:
                $type = "RM Mini Shate";
                break;
            case 0x2714:
                $type = "A1";
                break;
            case 0x4EB5:
                $type = "MP1";
                break;
            default:
                break;
        }

        return $type;
    }
}