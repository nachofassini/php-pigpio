<?php
namespace Volantus\Pigpio\Tests\SPI;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volantus\BerrySpi\SpiInterface;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\ExtensionRequest;
use Volantus\Pigpio\Protocol\ExtensionResponseStructure;
use Volantus\Pigpio\Protocol\Response;
use Volantus\Pigpio\SPI\RegularSpiDevice;
use Volantus\Pigpio\SPI\SpiFlags;

/**
 * Class RegularSpiDeviceTest
 *
 * @package Volantus\Pigpio\Tests\SPI
 */
class RegularSpiDeviceTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var RegularSpiDevice
     */
    private $device;

    protected function setUp(): void
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->device = new RegularSpiDevice($this->client, 1, 32000, new SpiFlags(['notReserved' => [0]]));
    }

    public function test_construct_flagsNull()
    {
        $this->device = new RegularSpiDevice($this->client, 1, 32000, null);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new ExtensionRequest(Commands::SPIO, 1, 32000, 'L', [0])))
            ->willReturn(new Response(4));

        $this->device->open();
    }

    public function test_open_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new ExtensionRequest(Commands::SPIO, 1, 32000, 'L', [32])))
            ->willReturn(new Response(4));

        $this->device->open();
        self::assertTrue($this->device->isOpen());
    }

    public function test_open_calledTwice_idempotent()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new ExtensionRequest(Commands::SPIO, 1, 32000, 'L', [32])))
            ->willReturn(new Response(4));

        $this->device->open();
        $this->device->open();
        self::assertTrue($this->device->isOpen());
    }

    public function test_open_failed_badChannel()
    {
        $this->expectExceptionMessage("Opening device failed => bad SPI channel given (PI_BAD_SPI_CHANNEL)");
        $this->expectException(\Volantus\Pigpio\SPI\OpeningDeviceFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_SPI_CHANNEL);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_SPI_CHANNEL));

        $this->device->open();
    }

    public function test_open_failed_badSpeed()
    {
        $this->expectExceptionMessage("Opening device failed => bad speed given (PI_BAD_SPI_SPEED)");
        $this->expectException(\Volantus\Pigpio\SPI\OpeningDeviceFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_SPI_SPEED);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_SPI_SPEED));

        $this->device->open();
    }

    public function test_open_failed_badFlags()
    {
        $this->expectExceptionMessage("Opening device failed => bad flags given (PI_BAD_FLAGS)");
        $this->expectException(\Volantus\Pigpio\SPI\OpeningDeviceFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_FLAGS);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_FLAGS));

        $this->device->open();
    }

    public function test_open_failed_noAux()
    {
        $this->expectExceptionMessage("Opening device failed => no AUX available (PI_NO_AUX_SPI)");
        $this->expectException(\Volantus\Pigpio\SPI\OpeningDeviceFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_NO_AUX_SPI);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_NO_AUX_SPI));

        $this->device->open();
    }

    public function test_open_failed_openingFailed()
    {
        $this->expectExceptionMessage("Opening device failed (PI_SPI_OPEN_FAILED)");
        $this->expectException(\Volantus\Pigpio\SPI\OpeningDeviceFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_SPI_OPEN_FAILED);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_SPI_OPEN_FAILED));

        $this->device->open();
    }

    public function test_open_failed_unknownError()
    {
        $this->expectExceptionMessage("Opening device failed => unknown error");
        $this->expectException(\Volantus\Pigpio\SPI\OpeningDeviceFailedException::class);
        $this->expectExceptionCode(-512);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(-512));

        $this->device->open();
    }

    public function test_close_correctRequest()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::SPIC, 49, 0)))
            ->willReturn(new Response(0));

        $this->device->open();
        $this->device->close();

        self::assertFalse($this->device->isOpen());
    }

    public function test_close_notOpen_idempotent()
    {
        $this->device->close();
        self::assertFalse($this->device->isOpen());
    }

    public function test_close_failed_wrongHandle()
    {
        $this->expectExceptionMessage("Closing SPI device failed => daemon responded that wrong handle was given (PI_BAD_HANDLE)");
        $this->expectException(\Volantus\Pigpio\SPI\ClosingDeviceFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_HANDLE);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_HANDLE));

        $this->device->open();
        $this->device->close();
    }

    public function test_close_failed_unknownError()
    {
        $this->expectExceptionMessage("Closing SPI device failed => unknown error");
        $this->expectException(\Volantus\Pigpio\SPI\ClosingDeviceFailedException::class);
        $this->expectExceptionCode(-512);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(-512));

        $this->device->open();
        $this->device->close();
    }

    public function test_read_correctRequest()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::SPIR, 49, 2, new ExtensionResponseStructure('C*'))))
            ->willReturn(new Response(0, [1 => 64, 2 => 128]));

        $this->device->open();
        $result = $this->device->read(2);

        self::assertEquals([64, 128], $result);
    }

    public function test_read_notOpen()
    {
        $this->expectExceptionMessage("Device needs to be opened first for reading");
        $this->expectException(\Volantus\Pigpio\SPI\DeviceNotOpenException::class);
        $this->device->read(4);
    }

    public function test_read_badHandle()
    {
        $this->expectExceptionMessage("Reading from SPI device failed => bad handle (PI_BAD_HANDLE)");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_HANDLE);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_HANDLE));

        $this->device->open();
        $this->device->read(2);
    }

    public function test_read_badCount()
    {
        $this->expectExceptionMessage("Reading from SPI device failed => bad count given (PI_BAD_SPI_COUNT)");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_SPI_COUNT);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_SPI_COUNT));

        $this->device->open();
        $this->device->read(-1);
    }

    public function test_read_transferFailed()
    {
        $this->expectExceptionMessage("Reading from SPI device failed => data transfer failed (PI_SPI_XFER_FAILED)");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_SPI_XFER_FAILED);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_SPI_XFER_FAILED));

        $this->device->open();
        $this->device->read(2);
    }

    public function test_read_unknownError()
    {
        $this->expectExceptionMessage("Reading from SPI device failed => unknown error");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(-512);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(-512));

        $this->device->open();
        $this->device->read(2);
    }


    public function test_write_correctRequest()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->with(self::equalTo(new ExtensionRequest(Commands::SPIW, 49, 0, 'C*', [32, 64])))
            ->willReturn(new Response(0));

        $this->device->open();
        $this->device->write([32, 64]);
    }

    public function test_write_notOpen()
    {
        $this->expectExceptionMessage("Device needs to be opened first for writing");
        $this->expectException(\Volantus\Pigpio\SPI\DeviceNotOpenException::class);
        $this->device->write([32]);
    }

    public function test_write_badHandle()
    {
        $this->expectExceptionMessage("Writing to SPI device failed => bad handle (PI_BAD_HANDLE)");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_HANDLE);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_HANDLE));

        $this->device->open();
        $this->device->write([32]);
    }

    public function test_write_badCount()
    {
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionMessage("Writing to SPI device failed => bad count given (PI_BAD_SPI_COUNT)");
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_SPI_COUNT);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_SPI_COUNT));

        $this->device->open();
        $this->device->write([]);
    }

    public function test_write_transferFailed()
    {
        $this->expectExceptionMessage("Writing to SPI device failed => data transfer failed (PI_SPI_XFER_FAILED)");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_SPI_XFER_FAILED);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_SPI_XFER_FAILED));

        $this->device->open();
        $this->device->write([32]);
    }

    public function test_write_unknownError()
    {
        $this->expectExceptionMessage("Writing to SPI device failed => unknown error");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(-512);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(-512));

        $this->device->open();
        $this->device->write([32]);
    }

    public function test_crossTransfer_correctRequest()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->with(self::equalTo(new ExtensionRequest(Commands::SPIX, 49, 0, 'C*', [32, 64], new ExtensionResponseStructure('C*'))))
            ->willReturn(new Response(0, [16, 18, 19]));

        $this->device->open();
        $result = $this->device->crossTransfer([32, 64]);

        self::assertEquals([16, 18, 19], $result);
    }


    public function test_crossTransfer_notOpen()
    {
        $this->expectExceptionMessage("Device needs to be opened first for cross transfer");
        $this->expectException(\Volantus\Pigpio\SPI\DeviceNotOpenException::class);
        $this->device->crossTransfer([32]);
    }

    public function test_crossTransfer_badHandle()
    {
        $this->expectExceptionMessage("SPI cross transfer failed => bad handle (PI_BAD_HANDLE)");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_HANDLE);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_HANDLE));

        $this->device->open();
        $this->device->crossTransfer([32]);
    }

    public function test_crossTransfer_badCount()
    {
        $this->expectExceptionMessage("SPI cross transfer failed => bad count given (PI_BAD_SPI_COUNT)");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_SPI_COUNT);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(49));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_SPI_COUNT));

        $this->device->open();
        $this->device->crossTransfer([]);
    }

    public function test_crossTransfer_transferFailed()
    {
        $this->expectExceptionMessage("SPI cross transfer failed => data transfer failed (PI_SPI_XFER_FAILED)");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(RegularSpiDevice::PI_SPI_XFER_FAILED);

        $this->client->expects($this->exactly(2))
            ->method('sendRaw')
            ->willReturnOnConsecutiveCalls(
                new Response(49),
                new Response(RegularSpiDevice::PI_SPI_XFER_FAILED),
            );

        $this->device->open();
        $this->device->crossTransfer([32]);
    }

    public function test_crossTransfer_unknownError()
    {
        $this->expectExceptionMessage("SPI cross transfer failed => unknown error");
        $this->expectException(\Volantus\Pigpio\SPI\TransferFailedException::class);
        $this->expectExceptionCode(-512);

        $this->client->expects($this->exactly(2))
            ->method('sendRaw')
            ->willReturnOnConsecutiveCalls(
                new Response(49),
                new Response(-512),
            );

        $this->device->open();
        $this->device->crossTransfer([32]);
    }

    public function test_implementsInterface()
    {
        self::assertInstanceOf(SpiInterface::class, $this->device);
    }
}