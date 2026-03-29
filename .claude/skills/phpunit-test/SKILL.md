---
name: phpunit-test
description: Adds a test method to `tests/FantasticoTest.php` following the project's PHPUnit pattern. Use when user says 'add test', 'write test', 'implement test', or when filling in `markTestIncomplete` stubs. Key capabilities: setUp with env-var credentials, assertion patterns, incomplete test stubs. Do NOT use for creating new test files or testing non-Fantastico classes.
---
# PHPUnit Test

## Critical

- Only edit `tests/FantasticoTest.php` — never create a new test file.
- Use tabs for indentation (not spaces) — `.scrutinizer.yml` enforces `use_tabs: true`.
- Every test method requires a `@covers Detain\Fantastico\Fantastico::methodName` PHPDoc tag.
- The `$this->object` fixture is already instantiated in `setUp()` via env vars — never re-instantiate in tests.
- When implementing a stub, remove the `@todo` line and the `markTestIncomplete` block entirely.

## Instructions

1. **Read `tests/FantasticoTest.php`** to locate any existing `markTestIncomplete` stub for the target method, or find the correct alphabetical insertion point for a new method.

2. **Write the PHPDoc block** above the method using this exact format:
	```php
	/**
	 * @covers Detain\Fantastico\Fantastico::methodName
	 */
	```

3. **Declare the method** as `public function testMethodName()` — prefix is always `test`, followed by the method name with its original casing (e.g. `testGetIpList`, `testValidIp`, `testIs_type`).

4. **Call `$this->object`** directly — it is already connected-ready from `setUp()`:
	```php
	public function testGetIpTypes()
	{
		$types = $this->object->getIpTypes();
		$this->assertTrue(is_array($types), 'returns an array of types');
	}
	```

5. **Choose assertions** matched to the return type documented in `src/Fantastico.php`:
	- `bool` return → `$this->assertTrue(...)` / `$this->assertFalse(...)`
	- `array` return → `$this->assertTrue(is_array($result), 'description')`
	- Array with known keys → `$this->assertArrayHasKey('keyName', $result)`
	- `$this->connected` state → `$this->assertTrue($this->object->connected, 'description')`
	- Invalid-input path (returns `false` or fault array) → assert `assertFalse($result)` or `assertArrayHasKey('faultcode', $result)`

6. **If the test requires live SOAP** (any method that calls `connect()` internally), note that `FANTASTICO_USERNAME` / `FANTASTICO_PASSWORD` env vars must be set or the SOAP call will fail. If credentials are unavailable, keep/restore `markTestIncomplete`.

7. **Verify** by running:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```

## Examples

**User says:** "implement testValidIp"

**Actions taken:**
1. Read `tests/FantasticoTest.php`, locate the `testValid_ip` stub.
2. Replace the stub body — remove `markTestIncomplete`. Check `src/Fantastico.php:validIp()`: returns `bool` via `ip2long`.
3. Write assertions for valid and invalid inputs.

**Result** (replacing lines 88–97 in `tests/FantasticoTest.php`):
```php
	/**
	 * @covers Detain\Fantastico\Fantastico::validIp
	 */
	public function testValid_ip()
	{
		$this->assertTrue($this->object->validIp('192.168.1.1'), 'valid ip should return true');
		$this->assertFalse($this->object->validIp('not-an-ip'), 'invalid ip should return false');
	}
```

**User says:** "add test for isType"

**Result** (replacing `testIs_type` stub):
```php
	/**
	 * @covers Detain\Fantastico\Fantastico::isType
	 */
	public function testIs_type()
	{
		$this->assertTrue($this->object->isType(\Detain\Fantastico\Fantastico::ALL_TYPES), 'ALL_TYPES is valid');
		$this->assertTrue($this->object->isType(\Detain\Fantastico\Fantastico::VPS_TYPES), 'VPS_TYPES is valid');
		$this->assertFalse($this->object->isType(99), 'unknown type should return false');
	}
```

## Common Issues

- **`Class 'Detain\Fantastico\Fantastico' not found`**: Run `composer install` first to generate the autoloader.
- **`SoapFault: Could not connect to host`** during a live-SOAP test: `FANTASTICO_USERNAME` / `FANTASTICO_PASSWORD` env vars are missing or wrong. Set them or add `$this->markTestIncomplete('Credentials not configured.')` at the top of the test.
- **Scrutinizer `parameter_doc_comments` failure**: Every public method in `src/Fantastico.php` needs `@param` tags — but test methods in `tests/` are not checked. No `@param` needed on test methods.
- **Indentation errors from scrutinizer**: Tabs only. If your editor auto-converted to spaces, run `unexpand --first-only -t 4 tests/FantasticoTest.php` to convert back.
- **`markTestIncomplete` not removed after implementation**: The `@todo` PHPDoc line and the entire `$this->markTestIncomplete(...)` block must both be deleted when implementing a stub.
