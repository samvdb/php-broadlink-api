<?php
declare(strict_types=1);

namespace Broadlink\Device;

use Broadlink\Client;
use Broadlink\Helper\ModelHelper;

abstract class AbstractDevice
{
    protected $name;
    protected $host;
    protected $port = 80;
    protected $mac;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ModelHelper
     */
    protected $modelHelper;
    protected $getMacAarray;

    public function __construct($h = "", $m = "", $p = 80)
    {

        $this->host = $h;
        $this->port = $p;

        if (is_array($m)) {

            $this->mac = $m;
        } else {

            $this->mac     = [];
            $mac_str_array = explode(':', $m);

            foreach (array_reverse($mac_str_array) as $value) {
                array_push($this->mac, $value);
            }

        }


        $devType = is_string($this->getType()) ? hexdec($this->getType()) : $this->getType();

        $this->modelHelper  = new ModelHelper($devType);
        $this->client = new Client($this);

    }

    /**
     * @return int
     */
    abstract public function getType();

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return array
     */
    public function getMac()
    {
        $mac = "";

        foreach ($this->mac as $value) {
            $mac = sprintf("%02x", $value) . ':' . $mac;
        }

        return substr($mac, 0, strlen($mac) - 1);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return array
     */
    public function getMacAarray()
    {
        return $this->mac;
    }
}