# php-centrifugo - v2, [v1](https://github.com/oleh-ozimok/php-centrifugo/tree/v1.0)

[![Latest Stable Version](https://poser.pugx.org/oleh-ozimok/php-centrifugo/v/stable)](https://packagist.org/packages/oleh-ozimok/php-centrifugo) 
[![Total Downloads](https://poser.pugx.org/oleh-ozimok/php-centrifugo/downloads)](https://packagist.org/packages/oleh-ozimok/php-centrifugo) 
[![License](https://poser.pugx.org/oleh-ozimok/php-centrifugo/license)](https://packagist.org/packages/oleh-ozimok/php-centrifugo)

PHP client for [Centrifugo](https://github.com/centrifugal/centrifugo) real-time messaging server

## Features

* Support publishing messages via Redis engine API listener (publish, broadcast, unsubscribe, disconnect methods only)
* Support client chain (Redis -> HTTP -> ...) as failover. If Redis down or method not supported by Redis engine API, client try send message via HTTP
* Support batch requests
* Support Predis

## Quick Examples

### Create Centrifugo client

```php
<?php

use Centrifugo\Centrifugo;
use Centrifugo\Clients\HttpClient;

$centrifugo = new Centrifugo('http://example.com/api/', 'secret api key', new HttpClient());
```

### Create Centrifugo client with Redis API support

```php
<?php

use Centrifugo\Centrifugo;
use Centrifugo\Clients\RedisClient;
use Centrifugo\Clients\HttpClient;
use Centrifugo\Clients\Redis\RedisTransport;
use Centrifugo\Clients\Redis\PredisTransport;

// Create Redis transport

$redis = new \Redis();
$redis->connect('localhost');
$redisTransport = new RedisTransport($redis);

// Or Predis transport

$predis = new Predis\Client(['host'   => 'localhost']);
$redisTransport = new PredisTransport($predis);

// Create Centrifugo RedisClient

$centrifugoRedisClient = new RedisClient($redisTransport);
$centrifugoRedisClient->setShardsNumber(12);

// Add Centrifugo HttpClient as failover

$centrifugoHttpClient = new HttpClient();
$centrifugoRedisClient->setFailover($centrifugoHttpClient);

$centrifugo = new Centrifugo('http://example.com/api/', 'secret api key', $centrifugoRedisClient);
```
### Send request to Centrifugo

```php
<?php

use Centrifugo\Centrifugo;
use Centrifugo\Exceptions\CentrifugoException;

$userId = 1;
$channel = '#chan_1';
$messageData = ['message' => 'Hello, world!'];

try {
    //Send message into channel.
    $response = $centrifugo->publish($channel, $messageData);
    
    //Very similar to publish but allows to send the same data into many channels.
    $response = $centrifugo->broadcast([$channel], $messageData);
    
    //Unsubscribe user from channel.
    $response = $centrifugo->unsubscribe($channel, $userId);
    
    //Disconnect user by user ID.
    $response = $centrifugo->disconnect($userId);
    
    //Get channel presence information (all clients currently subscribed on this channel).
    $response = $centrifugo->presence($channel);
    
    //Get channel history information (list of last messages sent into channel).
    $response = $centrifugo->history($channel);
    
    //Get channels information (list of currently active channels).
    $response = $centrifugo->channels();
    
    //Get stats information about running server nodes.
    $response = $centrifugo->stats();
    
    //Get information about single Centrifugo node.
    $response = $centrifugo->node('http://node1.example.com/api/');
} catch (CentrifugoException $e) {
    // invalid response
}
```

### Send batch request

```php
<?php

use Centrifugo\Centrifugo;
use Centrifugo\Exceptions\CentrifugoException;

$userId = '1'; //must be a string
$channel = '#chan_1';
$messageData = ['message' => 'Hello, world!'];

try {
    $requests[] = $centrifugo->request('publish', ['channel' => $channel, 'data' => $messageData]);
    $requests[] = $centrifugo->request('broadcast', ['channel' => $channel, 'data' => $messageData]);
    $requests[] = $centrifugo->request('unsubscribe', ['channel' => $channel, 'user' => $userId]);
    $requests[] = $centrifugo->request('disconnect', ['user' => $userId]);
    
    $batchResponse = $centrifugo->sendBatchRequest($requests);
    
    foreach ($batchResponse as $response) {
        if ($response->isError()) {
            // get error info
            $error = $response->getError();
        } else {
            // get response data as array
            $responseData = $response->getDecodedBody();
        }
    }
} catch (CentrifugoException $e) {
    // invalid response
}
```

## Related Projects
[centrifugo-bundle](https://github.com/kismia/centrifugo-bundle) (under development)
