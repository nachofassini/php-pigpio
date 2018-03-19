<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Request;

/**
 * Class SpiDevice
 *
 * @package Volantus\Pigpio\SPI
 */
abstract class SpiDevice
{
    const PI_SPI_OPEN_FAILED = -73;
    const PI_BAD_SPI_SPEED   = -78;
    const PI_BAD_HANDLE      = -25;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $handle;

    /**
     * @var int
     */
    protected $channel;

    /**
     * @var int
     */
    protected $baudRate;

    /**
     * @var int
     */
    protected $flags;

    /**
     * @var ErrorHandler
     */
    protected $errorHandler;

    /**
     * @param Client       $client
     * @param int          $channel  SPI channel (0 or 1)
     * @param int          $baudRate Baud speed (32K-125M, values above 30M are unlikely to work)
     * @param int          $flags    Optional flags
     * @param ErrorHandler $errorHandler
     */
    public function __construct(Client $client, int $channel, int $baudRate, int $flags = 0, ErrorHandler $errorHandler)
    {
        $this->client = $client;
        $this->channel = $channel;
        $this->baudRate = $baudRate;
        $this->flags = $flags;
        $this->errorHandler = $errorHandler;
    }

    /**
     * Opens the SPI device (fetches a handle)
     */
    public function open()
    {
        // Already open?
        if ($this->handle !== null) {
            return;
        }

        $request = $this->getOpenRequest();
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw OpeningDeviceFailedException::create($response);
        }

        $this->handle = $response->getResponse();
    }

    /**
     * Writes the given data to SPI device and read simultaneously the same amount (byte count) of data
     * Returns one (unsigned) byte per array item
     *
     * @param array $data
     *
     * @return array
     */
    public function crossTransfer(array $data): array
    {
        if ($this->handle === null) {
            throw new DeviceNotOpenException('Device needs to be opened first for cross transfer');
        }

        $request = $this->getCrossTransferRequest($data);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            $this->errorHandler->handle($request, $response);
        }

        return array_values($response->getExtension());
    }

    /**
     * Closes the SPI device (frees the handle)
     */
    public function close()
    {
        if ($this->handle === null) {
            return;
        }

        $request = $this->getCloseRequest();
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw ClosingDeviceFailedException::create($response);
        }

        $this->handle = null;
    }

    /**
     * @return Request
     */
    protected abstract function getOpenRequest(): Request;

    /**
     * @return Request
     */
    protected abstract function getCloseRequest(): Request;

    /**
     * @param array $data
     *
     * @return Request
     */
    protected abstract function getCrossTransferRequest(array $data): Request;


    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->handle !== null;
    }

    /**
     * @return int
     */
    public function getHandle(): int
    {
        return $this->handle;
    }

    /**
     * @return int
     */
    public function getChannel(): int
    {
        return $this->channel;
    }

    /**
     * @return int
     */
    public function getBaudRate(): int
    {
        return $this->baudRate;
    }

    /**
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }
}