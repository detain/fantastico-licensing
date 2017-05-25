# Fantastico Licensing API

Class to itnerface with the Fantastico Licensing API to manage Fantastico Server and VPS License Types.  More info at https://www.netenberg.com/fantastico.php

[![Latest Stable Version](https://poser.pugx.org/detain/fantastico/version)](https://packagist.org/packages/detain/fantastico)
[![Total Downloads](https://poser.pugx.org/detain/fantastico/downloads)](https://packagist.org/packages/detain/fantastico)
[![Latest Unstable Version](https://poser.pugx.org/detain/fantastico/v/unstable)](//packagist.org/packages/detain/fantastico)
[![License](https://poser.pugx.org/detain/fantastico/license)](https://packagist.org/packages/detain/fantastico)
[![Monthly Downloads](https://poser.pugx.org/detain/fantastico/d/monthly)](https://packagist.org/packages/detain/fantastico)
[![Daily Downloads](https://poser.pugx.org/detain/fantastico/d/daily)](https://packagist.org/packages/detain/fantastico)
[![Reference Status](https://www.versioneye.com/php/detain:fantastico/reference_badge.svg?style=flat)](https://www.versioneye.com/php/detain:fantastico/references)
[![Build Status](https://travis-ci.org/detain/fantastico.svg?branch=master)](https://travis-ci.org/detain/fantastico)
[![Code Climate](https://codeclimate.com/github/detain/fantastico/badges/gpa.svg)](https://codeclimate.com/github/detain/fantastico)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/detain/fantastico/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/detain/fantastico/?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/226251fc068f4fd5b4b4ef9a40011d06)](https://www.codacy.com/app/detain/fantastico)

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

