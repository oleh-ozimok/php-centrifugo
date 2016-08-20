# php-centrifugo - version 1

PHP client for [Centrifugo](https://github.com/centrifugal/centrifugo) real-time messaging server

## Features

* Support publishing messages via Redis engine API listener (publish, broadcast, unsubscribe, disconnect methods only)
* Support transport chain (Redis + HTTP) as failover. If Redis down (or method not supported by Redis transport) client try send message via HTTP transport
* Support batch requests

## Quick Examples

### Create Centrifugo client

```php
<?php
// Require the Composer autoloader.
require 'vendor/autoload.php';

use Centrifugo\Centrifugo;

// Instantiate Centrifugo client.
$centrifugo = new Centrifugo('http://example.com/api/', 'secret api key', [
    'redis' => [
        'host'         => 'localhost',
        // additional params
        'port'         => 6379
        'db'           => 0,
        'timeout'      => 0.0,
        'shardsNumber' => 0,
    ],
    'http' => [] // Curl options,
]);
```

### Send request to Centrifugo

```php
<?php

use Centrifugo\Centrifugo\Exceptions\CentrifugoException;

try {
    $response = $centrifugo->publish('channel', ['foo' => 'bar']);
    $response = $centrifugo->broadcast('channel', ['foo' => 'bar']);
    $response = $centrifugo->unsubscribe('channel', 'userID');
    $response = $centrifugo->disconnect('userID');
    $response = $centrifugo->presence('channel');
    $response = $centrifugo->history('channel', ['foo' => 'bar']);
    $response = $centrifugo->channels();
    $response = $centrifugo->stats();
    $response = $centrifugo->node('http://example.com/api/');
    
} catch (CentrifugoException $e){
    // invalid response
}
```

### Send batch request

```php
<?php

use Centrifugo\Centrifugo\Exceptions\CentrifugoException;

try {
    $requests[] = $centrifugo->request('publish', ['channel' => $channel, 'data' => $data]);
    $requests[] = $centrifugo->request('broadcast', ['channel' => $channel, 'data' => $data]);
    $requests[] = $centrifugo->request('unsubscribe', ['channel' => $channel, 'data' => $data]);
    $requests[] = $centrifugo->request('disconnect', ['channel' => $channel, 'data' => $data]);
    
    $batchResponse = $centrifugo->sendBatchRequest($requests);
    
    foreach($batchResponse as $response){
        if($response->isError()){
            // get error info
            $error = $response->getError();
        } else {
            // get response data as array
            $responseData = $response->decodedBody();
        }
    }

} catch (CentrifugoException $e){
    // invalid response
}
```
