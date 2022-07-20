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

    protected function setUp(): void
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

    public function test_setPulseWidth_badGpiPin()
    {
        $this->expectExceptionMessage("SERVO command failed => bad GPIO pin given (status code -2)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::SERVO, 50, 1500)))
            ->willReturn(new Response(PwmSender::PI_BAD_USER_GPIO));

        $this->sender->setPulseWidth(50, 1500);
    }

    public function test_setPulseWidth_badPulseWidth()
    {
        $this->expectExceptionMessage("SERVO command failed => given pulse width is out of valid range (status code -7)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::SERVO, 14, -1)))
            ->willReturn(new Response(PwmSender::PI_BAD_PULSEWIDTH));

        $this->sender->setPulseWidth(14, -1);
    }

    public function test_setPulseWidth_notPermitted()
    {
        $this->expectExceptionMessage("SERVO command failed => operation was not permitted (status code -41)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::SERVO, 14, 1500)))
            ->willReturn(new Response(PwmSender::PI_NOT_PERMITTED));

        $this->sender->setPulseWidth(14, 1500);
    }

    public function test_setPulseWidth_unknown_failure()
    {
        $this->expectExceptionMessage("SERVO command failed with status code -3");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
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

    public function test_setDutyCycle_badGpiPin()
    {
        $this->expectExceptionMessage("PWM command failed => bad GPIO pin given (status code -2)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PWM, 50, 150)))
            ->willReturn(new Response(PwmSender::PI_BAD_USER_GPIO));

        $this->sender->setDutyCycle(50, 150);
    }

    public function test_setDutyCycle_badDutyCycle()
    {
        $this->expectExceptionMessage("PWM command failed => given dutycycle is out of valid range (status code -8)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PWM, 14, -1)))
            ->willReturn(new Response(PwmSender::PI_BAD_DUTYCYCLE));

        $this->sender->setDutyCycle(14, -1);
    }

    public function test_setDutyCycle_notPermitted()
    {
        $this->expectExceptionMessage("PWM command failed => operation was not permitted (status code -41)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PWM, 14, 170)))
            ->willReturn(new Response(PwmSender::PI_NOT_PERMITTED));

        $this->sender->setDutyCycle(14, 170);
    }

    public function test_setDutyCycle_unknown_failure()
    {
        $this->expectExceptionMessage("PWM command failed with status code -99");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PWM, 14, 1700)))
            ->willReturn(new Response(-99));

        $this->sender->setDutyCycle(14, 1700);
    }

    public function test_setRange_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PRS, 14, 1024)))
            ->willReturn(new Response(0));

        $this->sender->setRange(14, 1024);
    }

    public function test_setRange_badGpiPin()
    {
        $this->expectExceptionMessage("PRS command failed => bad GPIO pin given (status code -2)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PRS, 50, 1024)))
            ->willReturn(new Response(PwmSender::PI_BAD_USER_GPIO));

        $this->sender->setRange(50, 1024);
    }

    public function test_setRange_badRange()
    {
        $this->expectExceptionMessage("PRS command failed => given range is not valid (status code -21)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PRS, 14, -1)))
            ->willReturn(new Response(PwmSender::PI_BAD_DUTYRANGE));

        $this->sender->setRange(14, -1);
    }

    public function test_setRange_unknownFailure()
    {
        $this->expectExceptionMessage("PRS command failed with status code -99");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PRS, 14, 1024)))
            ->willReturn(new Response(-99));

        $this->sender->setRange(14, 1024);
    }

    public function test_setFrequency_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PFS, 14, 250)))
            ->willReturn(new Response(0));

        $this->sender->setFrequency(14, 250);
    }

    public function test_setFrequency_badGpiPin()
    {
        $this->expectExceptionMessage("PFS command failed => bad GPIO pin given (status code -2)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PFS, 50, 250)))
            ->willReturn(new Response(PwmSender::PI_BAD_USER_GPIO));

        $this->sender->setFrequency(50, 250);
    }

    public function test_setFrequency_notPermitted()
    {
        $this->expectExceptionMessage("PFS command failed => operation was not permitted (status code -41)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PFS, 14, 250)))
            ->willReturn(new Response(PwmSender::PI_NOT_PERMITTED));

        $this->sender->setFrequency(14, 250);
    }

    public function test_setFrequency_unknownFailure()
    {
        $this->expectExceptionMessage("PFS command failed with status code -99");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PFS, 14, 250)))
            ->willReturn(new Response(-99));

        $this->sender->setFrequency(14, 250);
    }

    public function test_getPulseWidth_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::GPW, 14, 0)))
            ->willReturn(new Response(1600));

        $result = $this->sender->getPulseWidth(14);
        self::assertEquals(1600, $result);
    }

    public function test_getPulseWidth_badGpioPin()
    {
        $this->expectExceptionMessage("GPW command failed => bad GPIO pin given (status code -2)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::GPW, 50, 0)))
            ->willReturn(new Response(PwmSender::PI_BAD_USER_GPIO));

        $this->sender->getPulseWidth(50);
    }

    public function test_getPulseWidth_notInUse()
    {
        $this->expectExceptionMessage("GPW command failed => GPIO is not in use for servo pulses (status code -93)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::GPW, 14, 0)))
            ->willReturn(new Response(PwmSender::PI_NOT_SERVO_GPIO));

        $this->sender->getPulseWidth(14);
    }

    public function test_getPulseWidth_unknownError()
    {
        $this->expectExceptionMessage("GPW command failed with status code -99");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::GPW, 14, 0)))
            ->willReturn(new Response(-99));

        $this->sender->getPulseWidth(14);
    }

    public function test_getRange_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PRG, 14, 0)))
            ->willReturn(new Response(255));

        $result = $this->sender->getRange(14);
        self::assertEquals(255, $result);
    }

    public function test_getRange_badGpioPin()
    {
        $this->expectExceptionMessage("PRG command failed => bad GPIO pin given (status code -2)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PRG, 50, 0)))
            ->willReturn(new Response(PwmSender::PI_BAD_USER_GPIO));

        $this->sender->getRange(50);
    }

    public function test_getRange_unknownError()
    {
        $this->expectExceptionMessage("PRG command failed with status code -99");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PRG, 14, 0)))
            ->willReturn(new Response(-99));

        $this->sender->getRange(14);
    }

    public function test_getDutyCycle_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::GDC, 14, 0)))
            ->willReturn(new Response(150));

        $result = $this->sender->getDutyCycle(14);
        self::assertEquals(150, $result);
    }

    public function test_getDutyCycle_badGpioPin()
    {
        $this->expectExceptionMessage("GDC command failed => bad GPIO pin given (status code -2)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::GDC, 50, 0)))
            ->willReturn(new Response(PwmSender::PI_BAD_USER_GPIO));

        $this->sender->getDutyCycle(50);
    }

    public function test_getDutyCycle_notInUse()
    {
        $this->expectExceptionMessage("GDC command failed => GPIO is not in use for PWM (status code -92)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::GDC, 14, 0)))
            ->willReturn(new Response(PwmSender::PI_NOT_PWM_GPIO));

        $this->sender->getDutyCycle(14);
    }

    public function test_getDutyCycle_unknownError()
    {
        $this->expectExceptionMessage("GDC command failed with status code -99");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::GDC, 14, 0)))
            ->willReturn(new Response(-99));

        $this->sender->getDutyCycle(14);
    }

    public function test_getFrequency_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PFG, 14, 0)))
            ->willReturn(new Response(250));

        $result = $this->sender->getFrequency(14);
        self::assertEquals(250, $result);
    }

    public function test_getFrequency_badGpioPin()
    {
        $this->expectExceptionMessage("PFG command failed => bad GPIO pin given (status code -2)");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PFG, 50, 0)))
            ->willReturn(new Response(PwmSender::PI_BAD_USER_GPIO));

        $this->sender->getFrequency(50);
    }

    public function test_getFrequency_unknownError()
    {
        $this->expectExceptionMessage("PFG command failed with status code -99");
        $this->expectException(\Volantus\Pigpio\PWM\CommandFailedException::class);
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::PFG, 14, 0)))
            ->willReturn(new Response(-99));

        $this->sender->getFrequency(14);
    }
}