# Fantastico Licensing API

PHP library wrapping the Fantastico (Netenberg) SOAP API to manage Server and VPS licenses.

## Commands

```bash
composer install                        # install deps including phpunit
vendor/bin/phpunit tests/ -v            # run all tests
vendor/bin/phpunit tests/ -v --coverage-clover coverage.xml --whitelist src/  # with coverage
```

## Architecture

- **Source**: `src/Fantastico.php` · namespace `Detain\Fantastico\` · autoloaded via `composer.json` PSR-4
- **Tests**: `tests/FantasticoTest.php` · extends `PHPUnit\Framework\TestCase`
- **Transport**: PHP `ext-soap` extension — all API calls go through SOAP client in `src/Fantastico.php`
- **Auth**: credentials passed to constructor; env vars `FANTASTICO_USERNAME` / `FANTASTICO_PASSWORD` in tests
- **License types**: `Fantastico::ALL_TYPES` constant · type `1` = Server · type `2` = VPS
- **IDE config**: `.idea/` contains PhpStorm project settings including `inspectionProfiles`, `deployment.xml`, and `encodings.xml`

## Key Methods

| Method | Description |
|---|---|
| `connect()` | Initialize SOAP client; sets `$this->connected` |
| `getIpTypes()` | Returns array of license types |
| `getIpList($type)` | List IPs by type |
| `getIpListDetailed($type)` | Returns array with `ipAddress`, `addedOn`, `isVPS`, `status` |
| `getIpDetails($ip)` | Details for a single IP |
| `addIp($ip, $type)` | Create license (type 1=Server, 2=VPS) |
| `editIp($oldIp, $newIp)` | Change IP on existing license |
| `deactivateIp($ip)` | Deactivate license |
| `reactivateIp($ip)` | Reactivate license |
| `deleteIp($ip)` | Remove license |

## Usage Example

```php
$fantastico = new \Detain\Fantastico\Fantastico(
    getenv('FANTASTICO_USERNAME'),
    getenv('FANTASTICO_PASSWORD')
);
$list = $fantastico->getIpListDetailed(\Detain\Fantastico\Fantastico::ALL_TYPES);
foreach ($list as $entry) {
    echo $entry['ipAddress'].' status='.$entry['status'].PHP_EOL;
}
```

## Conventions

- One class per file; class lives in `src/`, tests in `tests/`
- camelCase methods and properties per `.scrutinizer.yml` checks
- Tabs for indentation (`.scrutinizer.yml` `use_tabs: true`)
- All new methods require a corresponding `test*` method in `tests/FantasticoTest.php`
- Mark incomplete tests with `$this->markTestIncomplete('...')` rather than leaving empty
- PHPDoc blocks required on all public methods (`parameter_doc_comments: true` in `.scrutinizer.yml`)
- Constants `UPPERCASE` per scrutinizer `uppercase_constants` check
- No short open tags (`no_short_open_tag: true`)

## Running Tests with Credentials

```bash
FANTASTICO_USERNAME=user FANTASTICO_PASSWORD=pass vendor/bin/phpunit tests/ -v
```

<!-- caliber:managed:pre-commit -->
## Before Committing

Run `caliber refresh` before creating git commits to keep docs in sync with code changes.
After it completes, stage any modified doc files before committing:

```bash
caliber refresh && git add CLAUDE.md .claude/ .cursor/ .github/copilot-instructions.md AGENTS.md CALIBER_LEARNINGS.md 2>/dev/null
```
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->
