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

    protected $devType;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ModelHelper
     */
    protected $modelHelper;
    protected $macArray;

    public function __construct($h = "", $m = "", $p = 80, $d)
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

        $this->devType = is_string($d) ? hexdec($d) : $d;

        $this->modelHelper  = new ModelHelper($this->devType);
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
     * @return string
     */
    public function getModel()
    {
        return $this->modelHelper->getModel();
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
    public function getMacArray()
    {
        return $this->mac;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getDeviceType(){
        return sprintf("0x%x", $this->devType);
    }

    public function getDevModel(){
        return $this->modelHelper->getModel($this->devType);
    }
}