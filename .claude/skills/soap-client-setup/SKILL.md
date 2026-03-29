---
name: soap-client-setup
description: Scaffolds SOAP client initialization and credential handling as used in src/Fantastico.php. Use when user says 'new soap client', 'connect to API', or creates a new API wrapper class in src/. Key capabilities: ext-soap wiring with nusoap fallback, connected flag, constructor credential injection, md5 hash auth, response caching skeleton. Do NOT use for REST or non-SOAP integrations.
---
# soap-client-setup

## Critical

- Use tabs for indentation — never spaces (`.scrutinizer.yml` `use_tabs: true`)
- No short open tags (`<?php` only, never `<?`)
- All public methods require PHPDoc blocks with `@param` and `@return` tags
- Constants must be `UPPERCASE`; properties and methods must be `camelCase`
- `$this->connect()` must be called lazily inside each public API method — never in the constructor
- `$connected` flag must be `public`; credentials must be `private`

## Instructions

1. **Create the source class file in `src/`** with namespace `Detain\ClassName\` matching the PSR-4 entry in `composer.json`. Add the optional StatisticClient require block at the top:
   ```php
   <?php
   namespace Detain\YourClass;

   if (is_file(__DIR__.'/../../../workerman/statistics/Applications/Statistics/Clients/StatisticClient.php')) {
       require_once __DIR__.'/../../../workerman/statistics/Applications/Statistics/Clients/StatisticClient.php';
   }
   ```
   Verify the namespace matches `composer.json` `autoload.psr-4` before proceeding.

2. **Declare class properties** in this order: public constants → public `$wsdl` string → `public $connected = false` → `private $apiUsername` → `private $apiPassword` → `private $soapClient` → `private $cache`. Each needs a PHPDoc block.

3. **Write the constructor** — accepts `$username` and `$password`, initializes `$cache = []`, `$soapClient = null`, and assigns credentials to private properties:
   ```php
   public function __construct($username, $password)
   {
       $this->cache = [];
       $this->soapClient = null;
       $this->apiUsername = $username;
       $this->apiPassword = $password;
   }
   ```

4. **Write `connect()`** — guard with `null === $this->soapClient`, disable WSDL cache, set timeouts, try `\SoapClient` with `SOAP_1_1` first, fall back to `nusoap_client` on exception, set `$this->connected = true` in the nusoap branch:
   ```php
   public function connect()
   {
       $nusoap = false;
       if (null === $this->soapClient) {
           ini_set('soap.wsdl_cache_enabled', '0');
           ini_set('max_execution_time', 1000);
           ini_set('default_socket_timeout', 1000);
           try {
               $this->soapClient = new \SoapClient($this->wsdl, ['soap_version' => SOAP_1_1, 'connection_timeout' => 1000, 'trace' => 1, 'exception' => 1]);
           } catch (\Exception $e) {
               $nusoap = true;
           }
       }
       if (true === $nusoap) {
           require_once INCLUDE_ROOT.'/../vendor/detain/nusoap/lib/nusoap.php';
           $this->soapClient = new \nusoap_client($this->wsdl);
           $this->connected = true;
       }
   }
   ```

5. **Write a private `getHash()`** that returns `md5($this->apiUsername.$this->apiPassword)`. Pass this as the first argument to every SOAP call.

6. **In each public API method**, follow this pattern: check cache → call `$this->connect()` → optional `\StatisticClient::tick()` → call `$this->soapClient->methodName($this->getHash(), ...)` → `json_decode(..., true)` the response → optional `\StatisticClient::report()` → `myadmin_log('module', 'debug', json_encode($result), __LINE__, __FILE__)` → store in `$this->cache` → return.

7. **Add `tests/YourClassTest.php`** extending `PHPUnit\Framework\TestCase`. `setUp()` must instantiate via env vars:
   ```php
   $this->object = new YourClass(getenv('YOURAPI_USERNAME'), getenv('YOURAPI_PASSWORD'));
   ```
   Add `testConnect()` asserting `$this->object->connected === true` after calling `connect()`. Mark all unimplemented tests with `$this->markTestIncomplete('...')`.

   Verify tests run:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```

## Examples

**User says:** "Create a new SOAP API wrapper for Netenberg in src/"

**Actions taken:**
1. Created `src/Fantastico.php` under `namespace Detain\Fantastico;`
2. Added `public $wsdl`, `public $connected = false`, private `$apiUsername`, `$apiPassword`, `$soapClient`, `$cache`
3. Constructor stores credentials, inits `$cache = []` and `$soapClient = null`
4. `connect()` tries `\SoapClient` with `SOAP_1_1`, falls back to `nusoap_client`, sets `$connected = true`
5. `getHash()` returns `md5($username.$password)` for auth
6. Each public method calls `$this->connect()` lazily, passes `$this->getHash()` as first SOAP arg
7. Created `tests/FantasticoTest.php` with `setUp()` reading `getenv('FANTASTICO_USERNAME')` / `getenv('FANTASTICO_PASSWORD')`

**Result:** `vendor/bin/phpunit tests/ -v` passes `testConnect()`.

## Common Issues

- **`Fatal error: Class 'SoapClient' not found`** — `ext-soap` is not enabled. Run `php -m | grep soap`; enable `extension=soap` in `php.ini` or install `php-soap` package.
- **`testConnect()` fails with `$connected === false`** — The `\SoapClient` constructor threw but the nusoap fallback path was not reached. Add `var_dump($e->getMessage())` inside the catch block to check the exception, then verify `INCLUDE_ROOT` is defined before `connect()` is called.
- **WSDL fetch timeout** — Increase `ini_set('default_socket_timeout', 1000)` or check network access to the WSDL URL. Never cache WSDL during development (`soap.wsdl_cache_enabled = 0`).
- **`json_decode` returns `null`** — The SOAP endpoint returned non-JSON (e.g. raw XML or a SOAP fault). Use `$this->soapClient->__getLastResponse()` to inspect the raw response.
- **`faultcode 1302: invalid hash`** — `getHash()` is computing `md5` of wrong values. Confirm `$apiUsername` and `$apiPassword` are the raw credential strings, not empty (check env vars are set).
- **Scrutinizer fails on indentation** — File uses spaces. Run `:retab` in vim or `unexpand --first-only -t 4` to convert to tabs before committing.
