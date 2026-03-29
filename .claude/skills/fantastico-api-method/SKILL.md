---
name: fantastico-api-method
description: Adds a new public method to `src/Fantastico.php` following the SOAP call pattern used by addIp/editIp/deactivateIp. Use when user says 'add method', 'new API call', 'add function', or needs to expose a new Fantastico API operation. Key capabilities: SOAP call wiring, StatisticClient instrumentation, cache management, PHPDoc blocks, and a matching test stub. Do NOT use for modifying the constructor, connect(), or soapIpFunction() internals.
---
# fantastico-api-method

## Critical

- Use **tabs** for indentation (not spaces) — enforced by `.scrutinizer.yml`
- Every public method **must** have a PHPDoc block with `@param` and `@return` tags
- Constants must be `UPPERCASE`; method/property names must be `camelCase`
- No short open tags (`<?` is forbidden; use `<?php`)
- Every new method requires a corresponding `test*` method in `tests/FantasticoTest.php`
- Do **not** modify `connect()`, `__construct()`, or `soapIpFunction()`

## Instructions

1. **Determine the SOAP call pattern** based on what parameters the new method needs:
   - Single `$ipAddress` param, no extra args → delegate to `$this->soapIpFunction('methodName', $ipAddress)` (same pattern as `deactivateIp`, `reactivateIp`, `deleteIp`)
   - Multiple params or custom response handling → implement inline (same pattern as `addIp` or `editIp`)
   - Read-only list/query with caching needed → use the `getIpList` / `getIpDetails` cache pattern
   - Verify the SOAP method name matches what Netenberg's WSDL exposes before proceeding.

2. **Write the method in `src/Fantastico.php`** inside the `Detain\Fantastico\Fantastico` class, before the closing `}`.

   For a single-IP-param method (delegate pattern):
   ```php
   /**
    * Fantastico::newMethodName()
    * One-line description of what it does.
    *
    * Output Success
    * Array
    * (
    *     [key] => value
    * )
    *
    * Output Error
    * Array
    * (
    *     [faultcode] => 1801
    *     [fault ] => "The IP Address that you have specified does not exist."
    * )
    *
    * @param string $ipAddress ip address to operate on
    * @return array response array with result keys or faultcode/fault on error
    */
   public function newMethodName($ipAddress)
   {
   	return $this->soapIpFunction('newMethodName', $ipAddress);
   }
   ```

   For a multi-param method (inline pattern, modelled on `addIp`):
   ```php
   /**
    * Fantastico::newMethodName()
    * One-line description.
    *
    * @param string  $ipAddress ip address
    * @param integer $param2    description of second param
    * @return array response array containing faultcode/fault on error, or result keys on success
    */
   public function newMethodName($ipAddress, $param2)
   {
   	if (!$this->validIp($ipAddress)) {
   		$response = ['faultcode' => 1, 'fault' => 'Invalid IP Address '.$ipAddress];
   	} else {
   		$this->connect();
   		if (class_exists(\StatisticClient::class, false)) {
   			\StatisticClient::tick('Fantastico', 'newMethodName');
   		}
   		$response = json_decode($this->soapClient->newMethodName($this->getHash(), $ipAddress, $param2), true);
   		if ($response === false) {
   			if (class_exists(\StatisticClient::class, false)) {
   				\StatisticClient::report('Fantastico', 'newMethodName', false, 1, 'Soap Client Error', STATISTICS_SERVER);
   			}
   		} else {
   			if (class_exists(\StatisticClient::class, false)) {
   				\StatisticClient::report('Fantastico', 'newMethodName', true, 0, '', STATISTICS_SERVER);
   			}
   		}
   		myadmin_log('fantastico', 'debug', json_encode($response), __LINE__, __FILE__);
   		if (isset($response['fault '])) {
   			$response['fault'] = $response['fault '];
   			unset($response['fault ']);
   		}
   	}
   	$this->cache = [];
   	return $response;
   }
   ```

3. **Add a test stub in `tests/FantasticoTest.php`** inside `FantasticoTest`, following the existing pattern:
   ```php
   /**
    * @covers Detain\Fantastico\Fantastico::newMethodName
    * @todo   Implement testNewMethodName().
    */
   public function testNewMethodName()
   {
   	// Remove the following lines when you implement this test.
   	$this->markTestIncomplete(
   		'This test has not been implemented yet.'
   	);
   }
   ```

4. **Run the test suite** to confirm no syntax errors and existing tests still pass:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```
   Verify no test regressions appear beyond the expected `markTestIncomplete` skips.

## Examples

**User says:** "Add a `suspendIp` method that suspends a license by IP"

**Actions taken:**
- Single IP param, mutating operation → use delegate pattern via `soapIpFunction`
- Add to `src/Fantastico.php`:
  ```php
  /**
   * Fantastico::suspendIp()
   * Suspends a Fantastico IP License
   *
   * Output Success
   * Array
   * (
   *     [ipAddress] => 130.253.175.32
   *     [status] => Inactive
   * )
   *
   * Output Error
   * Array
   * (
   *     [faultcode] => 1801
   *     [fault ] => "The IP Address that you have specified does not exist."
   * )
   *
   * @param string $ipAddress ip address to suspend
   * @return array response array with status or faultcode/fault on error
   */
  public function suspendIp($ipAddress)
  {
  	return $this->soapIpFunction('suspendIp', $ipAddress);
  }
  ```
- Add to `tests/FantasticoTest.php`:
  ```php
  /**
   * @covers Detain\Fantastico\Fantastico::suspendIp
   * @todo   Implement testSuspendIp().
   */
  public function testSuspendIp()
  {
  	$this->markTestIncomplete(
  		'This test has not been implemented yet.'
  	);
  }
  ```

**Result:** PHPUnit reports the new test as skipped (incomplete), all prior tests unchanged.

## Common Issues

- **`Fatal error: Call to undefined method SoapClient::newMethodName()`** — the SOAP method name passed to `soapIpFunction()` or `$this->soapClient->` does not match the WSDL. Check the exact method name at `https://netenberg.com/api/netenberg.wsdl`.
- **`json_decode() returns null` instead of array** — the SOAP response is not valid JSON. Add `var_dump($this->soapClient->newMethodName(...))` temporarily to inspect the raw response before decoding.
- **Scrutinizer fails with `parameter_doc_comments`** — a `@param` tag is missing or its type/name doesn't match the actual parameter. Every parameter in the signature needs a `@param type $name description` line.
- **Scrutinizer fails with `use_tabs`** — spaces were used for indentation. Run `unexpand --first-only -t 4 src/Fantastico.php` to convert or manually replace leading spaces with tabs.
- **Cache not cleared after mutation** — mutating methods (`add`, `edit`, `delete`, `deactivate`, `reactivate`) must end with `$this->cache = [];`. Read-only methods should populate `$this->cache['key']` and return from it on repeat calls.
