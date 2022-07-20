<?php
namespace Volantus\Pigpio\Tests\Network;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Network\Socket;

/**
 * Class SocketTest
 *
 * @package Volantus\Pigpio\Tests\Network
 */
class SocketTest extends TestCase
{
    public function test_construct_connectFailed()
    {
        $this->expectExceptionMessage("socket_connect() failed");
        $this->expectException(\Volantus\Pigpio\Network\SocketException::class);
        new Socket('256.0.0.1', 80);
    }
}