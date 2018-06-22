<?php
namespace Volantus\Pigpio\Tests\PWM;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\Response;
use Volantus\Pigpio\PWM\PwmSender;

/**
 * Class PwmSenderTest
 *
 * @package Volantus\Pigpio\Tests\PWM
 */
class PwmSenderTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var PwmSender
     */
    private $sender;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->sender = new PwmSender($this->client);
    }

    public function test_setPulseWidth_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::SERVO, 14, 1700)))
            ->willReturn(new Response(0));

        $this->sender->setPulseWidth(14, 1700);
    }

    /**
     * @expectedException \Volantus\Pigpio\PWM\CommandFailedException
     * @expectedExceptionMessage SERVO command failed with status code -3
     */
    public function test_setPulseWidth_unknown_failure()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::SERVO, 14, 1700)))
            ->willReturn(new Response(-3));

        $this->sender->setPulseWidth(14, 1700);
    }

    public function test_setDutyCycle_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PWM, 14, 150)))
            ->willReturn(new Response(0));

        $this->sender->setDutyCycle(14, 150);
    }

    /**
     * @expectedException \Volantus\Pigpio\PWM\CommandFailedException
     * @expectedExceptionMessage PWM command failed => bad GPIO pin given (status code -2)
     */
    public function test_setDutyCycle_badGpiPin()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PWM, 50, 150)))
            ->willReturn(new Response(PwmSender::PI_BAD_USER_GPIO));

        $this->sender->setDutyCycle(50, 150);
    }

    /**
     * @expectedException \Volantus\Pigpio\PWM\CommandFailedException
     * @expectedExceptionMessage PWM command failed => given dutycycle is out of valid range (status code -8)
     */
    public function test_setDutyCycle_badDutyCycle()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PWM, 14, -1)))
            ->willReturn(new Response(PwmSender::PI_BAD_DUTYCYCLE));

        $this->sender->setDutyCycle(14, -1);
    }

    /**
     * @expectedException \Volantus\Pigpio\PWM\CommandFailedException
     * @expectedExceptionMessage PWM command failed => operation was not permitted (status code -41)
     */
    public function test_setDutyCycle_notPermitted()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PWM, 14, 170)))
            ->willReturn(new Response(PwmSender::PI_NOT_PERMITTED));

        $this->sender->setDutyCycle(14, 170);
    }

    /**
     * @expectedException \Volantus\Pigpio\PWM\CommandFailedException
     * @expectedExceptionMessage PWM command failed with status code -99
     */
    public function test_setDutyCycle_unknown_failure()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PWM, 14, 1700)))
            ->willReturn(new Response(-99));

        $this->sender->setDutyCycle(14, 1700);
    }
}