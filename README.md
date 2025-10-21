# EaseSQL - FluentPDO Integration

SQL/PDO Support for EasePHP Framework using FluentPDO

![Project logo](php-ease-fluentpdo.svg?raw=true)

[![Latest Stable Version](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/version)](https://packagist.org/packages/vitexsoftware/ease-fluentpdo)
[![Total Downloads](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/downloads)](https://packagist.org/packages/vitexsoftware/ease-fluentpdo)
[![Latest Unstable Version](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/v/unstable)](//packagist.org/packages/vitexsoftware/ease-fluentpdo)
[![License](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/license)](https://packagist.org/packages/vitexsoftware/ease-fluentpdo)
[![Monthly Downloads](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/d/monthly)](https://packagist.org/packages/vitexsoftware/ease-fluentpdo)
[![Daily Downloads](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/d/daily)](https://packagist.org/packages/vitexsoftware/ease-fluentpdo)

## Overview

This library provides SQL database support for the [EasePHP Framework](https://github.com/VitexSoftware/php-ease-core) using [FluentPDO](https://github.com/fpdo/fluentpdo) for fluent SQL query building. It bridges the gap between Ease Framework's object-oriented approach and modern database operations.

### Features

- **Multi-database Support**: MySQL, PostgreSQL, SQLite, SQL Server
- **Fluent Query Builder**: Intuitive SQL query construction
- **ORM Capabilities**: Object-relational mapping with the Ease Framework
- **Connection Management**: Persistent and non-persistent connections
- **Debug Support**: SQL query logging and debugging
- **Migration Support**: Database schema versioning with Phinx
- **Logging Integration**: Built-in SQL logging capabilities

### Key Components

- `\Ease\SQL\Engine` - Main database engine with FluentPDO integration
- `\Ease\SQL\PDO` - Enhanced PDO wrapper
- `\Ease\SQL\Orm` - Object-relational mapping base class
- `\Ease\SQL\SQL` - Abstract SQL operations class
- `\Ease\Logger\LogToSQL` - Database logging functionality


## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

// Configure database connection
define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_DATABASE', 'myapp');
define('DB_USERNAME', 'user');
define('DB_PASSWORD', 'password');

// Create a database-backed object
class User extends \Ease\SQL\Engine {
    public function __construct($identifier = null) {
        $this->myTable = 'users';
        $this->keyColumn = 'id';
        parent::__construct($identifier);
    }
}

// Usage example
$user = new User();
$user->setDataValue('name', 'John Doe');
$user->setDataValue('email', 'john@example.com');
$user->save(); // Inserts or updates the record
```

## Installation

Download https://github.com/VitexSoftware/php-ease-fluentpdo/archive/master.zip or use

Composer
--------

```shell
    composer require vitexsoftware/ease-fluentpdo
```

Linux
-----

For Debian, Ubuntu & friends please use repo:

```shell
echo "deb http://repo.vitexsoftware.com $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/vitexsoftware.list
sudo wget -O /etc/apt/trusted.gpg.d/vitexsoftware.gpg http://repo.vitexsoftware.com/keyring.gpg
sudo apt update
sudo apt install php-vitexsoftware-ease-fluentpdo
```

In this case please add this to your app composer.json:

```json
    "require": {
        "deb/ease-fluentpdo": "*"
    },
    "repositories": [
        {
            "type": "path",
            "url": "/usr/share/php/EaseSQL",
            "options": {
                "symlink": true
            }
        }
    ]
```

## Configuration

### Environment Constants

The library uses these environment constants for database configuration:

| Constant | Description | Default | Example |
|----------|-------------|---------|----------|
| `DB_TYPE` | Database type | - | `mysql`, `pgsql`, `sqlite`, `sqlsrv` |
| `DB_HOST` | Database host | `localhost` | `127.0.0.1`, `db.example.com` |
| `DB_PORT` | Database port | - | `3306`, `5432` |
| `DB_DATABASE` | Database name/schema | - | `myapp`, `production_db` |
| `DB_USERNAME` | Database user | - | `dbuser`, `app_user` |
| `DB_PASSWORD` | Database password | - | `secret123` |
| `DB_SETUP` | Setup commands after connect | - | `SET NAMES utf8` |
| `DB_PERSISTENT` | Use persistent connections | `1` | `0` (disable), `1` (enable) |
| `DB_SETTINGS` | PDO connection settings | - | JSON string of PDO options |
| `DB_DEBUG` | Enable SQL query logging | `false` | `true`, `false` |

### Configuration Examples

#### MySQL Configuration
```php
define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_DATABASE', 'myapp');
define('DB_USERNAME', 'dbuser');
define('DB_PASSWORD', 'password');
define('DB_SETUP', 'SET NAMES utf8mb4');
```

#### PostgreSQL Configuration
```php
define('DB_TYPE', 'pgsql');
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_DATABASE', 'myapp');
define('DB_USERNAME', 'postgres');
define('DB_PASSWORD', 'password');
```

#### SQLite Configuration
```php
define('DB_TYPE', 'sqlite');
define('DB_DATABASE', '/path/to/database.sqlite');
```

## Usage Examples

### Basic ORM Operations

```php
// Create a model class
class Article extends \Ease\SQL\Engine {
    public function __construct($identifier = null) {
        $this->myTable = 'articles';
        $this->keyColumn = 'id';
        parent::__construct($identifier);
    }
}

// Create new record
$article = new Article();
$article->setDataValue('title', 'My First Article');
$article->setDataValue('content', 'This is the content...');
$article->setDataValue('author_id', 1);
$savedId = $article->save();

// Load existing record
$article = new Article(1);
echo $article->getDataValue('title');

// Update record
$article->setDataValue('title', 'Updated Title');
$article->save();

// Delete record
$article->delete();
```

### FluentPDO Query Builder

```php
$engine = new \Ease\SQL\Engine();

// Select with conditions
$users = $engine->listingQuery()
    ->from('users')
    ->where('active = ?', 1)
    ->where('created_at > ?', '2023-01-01')
    ->orderBy('name ASC')
    ->fetchAll();

// Complex joins
$articles = $engine->listingQuery()
    ->from('articles a')
    ->leftJoin('users u ON a.author_id = u.id')
    ->select('a.*, u.name as author_name')
    ->where('a.published = ?', 1)
    ->fetchAll();
```

### Logging to Database

```php
// Configure SQL logging
define('DB_DEBUG', true);

// Log messages to database
$logger = new \Ease\Logger\LogToSQL();
$logger->addToLog('Application started', 'info');
$logger->addToLog('User login failed', 'warning', ['user_id' => 123]);
```

## Development & Testing

### Prerequisites

- PHP 7.4 or higher
- Composer
- Database server (MySQL, PostgreSQL, SQLite, or SQL Server)

### Setup Development Environment

```bash
# Clone repository
git clone https://github.com/VitexSoftware/php-ease-fluentpdo.git
cd php-vitexsoftware-ease-fluentpdo

# Install dependencies
composer install

# Copy environment configuration
cp tests/.env.example tests/.env
# Edit tests/.env with your database credentials
```

### Database Setup for Testing

#### MySQL
```bash
mysqladmin -u root -p create easetest
mysql -u root -p -e "GRANT ALL PRIVILEGES ON easetest.* TO easetest@localhost IDENTIFIED BY 'easetest'"
```

#### PostgreSQL
```bash
sudo -u postgres createuser --createdb --password easetest
sudo -u postgres createdb -O easetest easetest
```

#### Run Migrations
```bash
cd tests
../vendor/bin/phinx migrate
../vendor/bin/phinx seed:run
```

### Running Tests

```bash
# Run all tests
make phpunit

# Or run directly with PHPUnit
./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php --configuration ./phpunit.xml

# Run specific test
./vendor/bin/phpunit tests/src/Ease/SQL/EngineTest.php
```

### Code Quality

```bash
# Static analysis
make static-code-analysis

# Code style fixes
make cs

# Run all quality checks
composer test
```

## Building & Packaging

### Debian Package

```bash
# Build Debian package
make deb
# or
dpkg-buildpackage -b -uc
```

### Composer Package

```bash
# Validate composer.json
composer validate

# Update dependencies
composer update

# Create optimized autoloader
composer dump-autoload --optimize
```

## API Reference

### Core Classes

#### `\Ease\SQL\Engine`
Main database engine class providing ORM functionality.

**Key Methods:**
- `save()` - Insert or update record
- `load($id)` - Load record by ID
- `delete()` - Delete current record
- `listingQuery()` - Get FluentPDO query builder
- `setDataValue($key, $value)` - Set field value
- `getDataValue($key)` - Get field value

#### `\Ease\SQL\PDO`
Enhanced PDO wrapper with additional functionality.

#### `\Ease\SQL\Orm`
Base ORM class for database-backed objects.

#### `\Ease\Logger\LogToSQL`
Database logging implementation.

**Methods:**
- `addToLog($message, $type, $data)` - Add log entry
- `getLogLevel()` - Get current log level
- `setLogLevel($level)` - Set log level

## Migration Support

The library includes support for database migrations using Phinx:

```bash
# Create new migration
make newmigration

# Run migrations
make migration

# Create seed
make newseed

# Run seeds
make seed
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`make phpunit`)
5. Run code quality checks (`make cs && make static-code-analysis`)
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

## Requirements

- **PHP**: >= 7.4
- **Extensions**: PDO with appropriate database drivers
- **Dependencies**:
  - `vitexsoftware/ease-core`: >= 1.49.0
  - `fpdo/fluentpdo`: >= 2.2.4

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Links & Resources

- **Homepage**: https://www.vitexsoftware.cz/ease.php
- **GitHub**: https://github.com/VitexSoftware/php-ease-fluentpdo
- **Packagist**: https://packagist.org/packages/vitexsoftware/ease-fluentpdo
- **Documentation**: https://github.com/VitexSoftware/php-ease-fluentpdo/wiki
- **Issue Tracker**: https://github.com/VitexSoftware/php-ease-fluentpdo/issues
- **EasePHP Core**: https://github.com/VitexSoftware/php-ease-core
- **FluentPDO**: https://github.com/fpdo/fluentpdo

## Support

For support and questions:
- Create an [issue](https://github.com/VitexSoftware/php-ease-fluentpdo/issues)
- Contact: info@vitexsoftware.cz
- Visit: https://www.vitexsoftware.cz
