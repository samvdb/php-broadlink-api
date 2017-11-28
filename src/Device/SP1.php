<?php
declare(strict_types=1);

namespace Broadlink\Device;

use Broadlink\Util\ByteUtil;

class SP1 extends AbstractDevice
{
    /**
     * @return int
     */
    public function getType()
    {
        return 0x2712;
    }

    public function Set_Power($state){

        $packet = ByteUtil::bytearray(4);
        $packet[0] = $state;

        $this->getClient()->send(0x66, $packet);
    }
}