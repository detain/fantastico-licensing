# Fantastico Licensing API

## Installation

Install with composer like

```sh
composer require detain/fantastico
```

## Basic Usage

### Initialization

Initialize passing the API credentials like

```php
use detain\Fantastico;

$fantastico = new Fantastico('API Username', 'API Password');
```

### List Licensed IPs

```php
$details = $fantastico->getIpListDetailed(Fantastico::ALL_TYPES);
```;

***Note*** Returns an array of license entries, each entry being an array like 

```php
[
	'ipAddress' => '194.116.187.120',
	'addedOn' => '2009-05-05 19:39:32',
	'isVPS' => 'No',
	'status' => 'Active'
]
```

### Create a new License

Add a license for a given IP.

***Note*** Type 1 = Server, Type 2 = VPS

```php
$result = $fantastico->addIp('66.45.228.200', 1);
```

### Change The IP for a License

***Note*** In this example '192.168.1.1' is the original (old) ip and '192.168.1.2' is the updated (new) IP

```php
$result = $fantastico->editIp('192.168.1.1', '192.168.1.2')
```

## License

Fantastico Licensing class is licensed under the LGPL-v2 license.

