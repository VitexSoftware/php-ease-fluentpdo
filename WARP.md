# WARP.md - Working AI Reference for php-vitexsoftware-ease-fluentpdo

## Project Overview
**Type**: PHP Project/Debian Package
**Purpose**: SQL Support for EasePHP Framework using FluentPDO
**Status**: Active
**Repository**: git@github.com:VitexSoftware/php-ease-fluentpdo.git

## Key Technologies
- PHP
- Composer
- Debian Packaging

## Architecture & Structure

```
php-vitexsoftware-ease-fluentpdo/
├── src/
│   ├── Ease/
│   │   ├── SQL/
│   │   │   ├── Engine.php      # Main ORM class with FluentPDO
│   │   │   ├── PDO.php         # Enhanced PDO wrapper
│   │   │   ├── Orm.php         # Base ORM functionality
│   │   │   ├── SQL.php         # Abstract SQL operations
│   │   │   └── Debugger.php    # SQL debugging tools
│   │   └── Logger/
│   │       └── LogToSQL.php    # Database logging
├── tests/
│   ├── src/                    # Unit tests
│   ├── migrations/             # Phinx migrations
│   ├── seeds/                  # Database seeds
│   ├── bootstrap.php           # Test bootstrap
│   └── .env                    # Test environment config
├── debian/                     # Debian packaging
├── .github/                    # GitHub workflows
├── Makefile                    # Build automation
├── composer.json               # Dependencies
├── phpunit.xml                 # PHPUnit configuration
└── README.md                   # Documentation
```

## Key Technologies
- **PHP**: >= 7.4 (primary language)
- **FluentPDO**: Query builder integration
- **EasePHP Core**: Framework foundation
- **PHPUnit**: Testing framework
- **Phinx**: Database migrations
- **Composer**: Dependency management
- **Debian Packaging**: System package creation

## Development Workflow

### Prerequisites
- PHP 7.4+
- Composer
- Database server (MySQL/PostgreSQL/SQLite/SQL Server)
- Make (for build automation)

### Setup Instructions
```bash
# Clone the repository
git clone git@github.com:VitexSoftware/php-ease-fluentpdo.git
cd php-vitexsoftware-ease-fluentpdo

# Install dependencies
composer install

# Set up test environment
cp tests/.env.example tests/.env
# Edit tests/.env with your database credentials

# Run database migrations
cd tests && ../vendor/bin/phinx migrate && cd ..
```

### Build & Run
```bash
# Debian package
make deb
# or
dpkg-buildpackage -b -uc

# Run development server (if needed)
php -S localhost:8000
```

### Testing
```bash
# Run all tests
make phpunit

# Static analysis
make static-code-analysis

# Code style fixes
make cs

# Individual test files
./vendor/bin/phpunit tests/src/Ease/SQL/EngineTest.php
```

## Key Concepts

### Core Components
- **Engine**: Main ORM class extending EasePHP with SQL capabilities
- **PDO**: Enhanced PDO wrapper with additional features
- **Orm**: Base class for database-backed objects
- **LogToSQL**: Database logging implementation
- **Debugger**: SQL debugging and profiling tools

### Configuration
- Environment constants (DB_TYPE, DB_HOST, etc.)
- PDO connection options
- Logging configuration
- Migration settings

### Integration Points
- EasePHP Framework core
- FluentPDO query builder
- Multiple database engines
- Phinx migrations
- PSR-4 autoloading

## Common Tasks

### Development
- **Code Review**: Use GitHub PR process with automated checks
- **Feature Implementation**: 
  - Create feature branch from main
  - Implement with tests
  - Ensure PSR-12 compliance
  - Run static analysis
- **Bug Fixes**: 
  - Reproduce issue with test
  - Fix and verify with existing tests
  - Update documentation if needed

### Database Operations
- **Migrations**: Create with `make newmigration`
- **Seeds**: Create test data with `make newseed` 
- **Schema Changes**: Always use migrations, never direct SQL
- **Query Optimization**: Use FluentPDO query builder methods

### Testing
- **Unit Tests**: Test individual classes and methods
- **Integration Tests**: Test database interactions
- **Performance Tests**: Measure query performance
- **Coverage**: Maintain >80% code coverage

### Packaging & Deployment
- **Debian Package**: `make deb` for system installation
- **Composer Package**: Published to Packagist automatically
- **Documentation**: Auto-generated from docblocks
- **Releases**: Tagged versions with semantic versioning

## Troubleshooting

### Common Issues

#### Database Connection Problems
- **Check**: Environment constants are properly set
- **Verify**: Database server is running and accessible
- **Test**: Connection using direct PDO
- **Debug**: Enable DB_DEBUG for query logging

#### ORM Issues
- **Table Structure**: Ensure `myTable` and `keyColumn` are set
- **Data Types**: Verify column types match expected PHP types
- **Relationships**: Check foreign key constraints

#### Performance Issues
- **Query Analysis**: Use EXPLAIN on slow queries
- **Indexing**: Ensure proper database indexes
- **Connection Pooling**: Consider persistent connections

### Debug Commands
```bash
# Enable SQL debugging
define('DB_DEBUG', true);

# Check database connection
php -r "echo 'DB Test: '; try { new PDO('mysql:host=localhost;dbname=test', 'user', 'pass'); echo 'OK'; } catch(Exception $e) { echo $e->getMessage(); }"

# Validate composer setup
composer validate --strict

# Check autoloading
composer dump-autoload --optimize

# Run diagnostics
make phpunit --debug
```

### Performance Optimization
- **Query Caching**: Implement query result caching
- **Connection Reuse**: Enable persistent connections
- **Batch Operations**: Use bulk insert/update methods
- **Index Analysis**: Regular EXPLAIN PLAN review

## Standards & Conventions

### Code Style
- **PSR-12**: PHP coding standard compliance
- **PHPStan**: Level 8 static analysis
- **Type Declarations**: Strict typing enabled
- **Documentation**: PHPDoc blocks required

### Database Conventions
- **Naming**: snake_case for tables/columns
- **Primary Keys**: `id` as auto-increment integer
- **Timestamps**: `created_at`, `updated_at` datetime columns
- **Foreign Keys**: `table_name_id` convention

### Git Workflow
- **Branches**: feature/, bugfix/, hotfix/ prefixes
- **Commits**: Conventional commit messages
- **PRs**: Required for all changes to main branch
- **Tags**: Semantic versioning (v1.2.3)

## Integration Examples

### Laravel Integration
```php
// In Laravel service provider
use Ease\SQL\Engine;

class CustomModel extends Engine {
    protected $myTable = 'custom_table';
    protected $keyColumn = 'id';
}
```

### Symfony Integration
```php
// As Symfony service
services:
    App\Repository\CustomRepository:
        arguments:
            $dbConfig: '%database_url%'
```

## Related Projects
- **EasePHP Core**: https://github.com/VitexSoftware/php-ease-core
- **EasePHP Bootstrap**: https://github.com/VitexSoftware/php-ease-twbootstrap4
- **MultiFlexi**: https://github.com/VitexSoftware/MultiFlexi
- **FluentPDO**: https://github.com/fpdo/fluentpdo
