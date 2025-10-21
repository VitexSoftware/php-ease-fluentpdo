# Contributing to EaseSQL - FluentPDO Integration

Thank you for your interest in contributing to this project! We welcome contributions from the community.

## Getting Started

1. **Fork** the repository on GitHub
2. **Clone** your fork locally
3. **Create** a feature branch from `main`
4. **Make** your changes
5. **Test** your changes thoroughly
6. **Submit** a pull request

## Development Setup

### Prerequisites

- PHP 7.4 or higher
- Composer
- Database server (MySQL, PostgreSQL, SQLite, or SQL Server)
- Make (for build automation)

### Local Setup

```bash
# Clone your fork
git clone git@github.com:YOUR_USERNAME/php-ease-fluentpdo.git
cd php-vitexsoftware-ease-fluentpdo

# Install dependencies
composer install

# Set up test environment
cp tests/.env.example tests/.env
# Edit tests/.env with your database credentials

# Run database migrations
cd tests
../vendor/bin/phinx migrate
../vendor/bin/phinx seed:run
cd ..
```

## Code Standards

### PHP Standards

- **PSR-12**: Follow PHP coding standard
- **Strict Types**: Use `declare(strict_types=1);`
- **Type Hints**: Use type declarations for all parameters and return types
- **PHPDoc**: Document all classes, methods, and properties

### Code Quality

Before submitting a PR, ensure your code passes:

```bash
# Run tests
make phpunit

# Static analysis
make static-code-analysis

# Code style fixes
make cs

# Or run all checks
composer test
```

### Database Standards

- Use **snake_case** for table and column names
- Primary keys should be named `id`
- Foreign keys should follow `table_name_id` pattern
- Always use migrations for schema changes
- Include both up and down migrations

## Testing

### Writing Tests

- Write unit tests for all new functionality
- Include integration tests for database operations
- Test edge cases and error conditions
- Maintain or improve code coverage

### Test Categories

1. **Unit Tests**: Test individual methods and classes
2. **Integration Tests**: Test database interactions
3. **Performance Tests**: Verify query performance
4. **Edge Case Tests**: Test boundary conditions

### Running Tests

```bash
# All tests
make phpunit

# Specific test file
./vendor/bin/phpunit tests/src/Ease/SQL/EngineTest.php

# With coverage
./vendor/bin/phpunit --coverage-html coverage/
```

## Database Migrations

### Creating Migrations

```bash
# Create new migration
make newmigration
# Enter migration name when prompted

# Create seed file
make newseed
# Enter seed name when prompted
```

### Migration Guidelines

- **Reversible**: Always include both `up()` and `down()` methods
- **Data Safety**: Never delete data without explicit confirmation
- **Performance**: Consider impact on large tables
- **Testing**: Test migrations on sample data

## Documentation

### Code Documentation

- **PHPDoc**: Use proper PHPDoc blocks for all public methods
- **Examples**: Include usage examples in docblocks
- **Types**: Specify parameter and return types accurately

### Project Documentation

- Update `README.md` for user-facing changes
- Update `WARP.md` for development-related changes
- Add entries to `CHANGELOG.md` following Keep a Changelog format

## Pull Request Process

### Before Submitting

1. **Rebase** on latest `main` branch
2. **Test** all functionality works correctly
3. **Lint** code passes all quality checks
4. **Document** changes in CHANGELOG.md
5. **Review** your own changes for completeness

### PR Description

Include in your PR:

- **Summary** of changes made
- **Motivation** for the changes
- **Testing** performed
- **Breaking Changes** if any
- **Related Issues** (use "Fixes #123" to auto-close)

### Review Process

- PRs require at least one approval
- All CI checks must pass
- Maintainers may request changes
- Be responsive to feedback

## Issue Guidelines

### Reporting Bugs

Include:

- **Environment**: PHP version, database type, OS
- **Steps to Reproduce**: Minimal example
- **Expected Behavior**: What should happen
- **Actual Behavior**: What actually happens
- **Error Messages**: Full stack traces

### Feature Requests

Describe:

- **Use Case**: Why is this needed?
- **Proposed Solution**: How should it work?
- **Alternatives**: What other approaches exist?
- **Breaking Changes**: Would this break existing code?

## Database Support

We support multiple database engines:

- **MySQL**: Primary development database
- **PostgreSQL**: Full support
- **SQLite**: For testing and lightweight applications
- **SQL Server**: Basic support

When adding features, consider compatibility across all supported databases.

## Performance Considerations

- **Query Optimization**: Use appropriate indexes
- **Connection Management**: Consider persistent connections
- **Memory Usage**: Avoid loading large datasets into memory
- **Caching**: Implement appropriate caching strategies

## Security

- **SQL Injection**: Always use prepared statements
- **Input Validation**: Validate all user inputs
- **Secrets**: Never commit passwords or keys
- **Dependencies**: Keep dependencies updated

## Community

- Be respectful and inclusive
- Help others learn and grow
- Share knowledge and best practices
- Follow the code of conduct

## Questions?

- Check existing [Issues](https://github.com/VitexSoftware/php-ease-fluentpdo/issues)
- Review project [Documentation](README.md)
- Contact: info@vitexsoftware.cz

Thank you for contributing! 🙏