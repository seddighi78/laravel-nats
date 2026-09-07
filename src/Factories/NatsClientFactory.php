<?php

namespace Seddighi78\LaravelNats\Factories;

use Basis\Nats\Client;
use Basis\Nats\Configuration;
use Exception;
use InvalidArgumentException;

class NatsClientFactory implements NatsClientFactoryInterface
{
    private array $clients = [];
    private array $nextClient = [];

    public function getClient($connection = 'default'): Client
    {
        $parameters = config("nats.connections.$connection");

        if ($parameters === null) {
            throw new Exception("NATS Connection [$connection] not configured");
        }

        $poolSize = $parameters['pool_size'] ?? 1;
        
        if (!is_int($poolSize) || $poolSize < 1) {
            throw new InvalidArgumentException("NATS Connection [$connection] pool_size must be a positive integer");
        }

        $index = ($this->nextClient[$connection] ?? 0) % $poolSize;
        if (isset($this->clients[$connection][$index])) {
            $this->nextClient[$connection] = ($index + 1) % $poolSize;
            return $this->clients[$connection][$index];
        }

        $configuration = new Configuration([
            'host' => $parameters['host'],
            'jwt' => $parameters['jwt'],
            'user' => $parameters['user'],
            'pass' => $parameters['pass'],
            'pedantic' => $parameters['pedantic'],
            'port' => $parameters['port'],
            'timeout' => $parameters['timeout'],
            'lang' => 'php',
            'reconnect' => true,
        ]);

        if (isset($parameters['delay'])) {
            $configuration->setDelay($parameters['delay']['seconds'], $parameters['delay']['mode']);
        }
        
        $this->clients[$connection][$index] = new Client($configuration);
        $this->nextClient[$connection] = ($index + 1) % $poolSize;

        return $this->clients[$connection][$index];
    }
}
