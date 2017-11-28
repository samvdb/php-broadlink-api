<?php
declare(strict_types=1);

namespace Broadlink\Helper;

use Broadlink\Util\ByteUtil;

class SocketHelper
{

    protected $host;

    protected $port;

    protected $socket;

    /**
     * SocketHelper constructor.
     * @param        $host
     * @param        $port
     */
    public function __construct($host, $port)
    {
        $this->host    = $host;
        $this->port    = $port;
    }

    protected function createSocket()
    {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if ($this->socket) {
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, 1);
            socket_bind($this->socket, '0.0.0.0', 10001);
        }
    }

    protected function closeSocket()
    {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }

    /**
     * @param     $packet
     * @param     $host
     * @param     $port
     * @param int $timeout
     * @return array
     */
    public function send($packet, $timeout = 100)
    {
        $this->createSocket();

        $from = '';
        socket_sendto($this->socket, ByteUtil::byte($packet), sizeof($packet), 0, $this->host, $this->port);
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => 0]);

        $ret = socket_recvfrom($this->socket, $response, 1024, 0, $from, $port);


        $this->closeSocket();

        return ByteUtil::byte2array($response);
    }


}
