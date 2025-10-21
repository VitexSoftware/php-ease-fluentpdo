# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Comprehensive documentation updates
- Enhanced README with usage examples and API reference
- Detailed WARP.md for AI development assistance
- Configuration examples for multiple database types

### Changed
- Improved project structure documentation
- Enhanced testing and development workflow descriptions

### Fixed
- Documentation formatting and organization

## Previous Versions

For older versions, please check the Git history:
```bash
git log --oneline --decorate --graph
```

## Version Guidelines

### Major Versions (x.0.0)
- Breaking API changes
- Database schema changes requiring manual migration
- Major architectural changes

### Minor Versions (x.y.0) 
- New features and functionality
- New database support
- Performance improvements
- Deprecated features (with backward compatibility)

### Patch Versions (x.y.z)
- Bug fixes
- Security updates
- Documentation improvements
- Minor performance optimizations

## Migration Notes

When upgrading between versions, please check:

1. **Database Migrations**: Run `make migration` to update schema
2. **Configuration Changes**: Check for new environment constants
3. **API Changes**: Review method signatures and return types
4. **Dependencies**: Update with `composer update`

## Support Policy

- **Current Major Version**: Full support with new features and bug fixes
- **Previous Major Version**: Security updates and critical bug fixes only
- **Older Versions**: Community support only

For support questions, please:
- Check the [Issues](https://github.com/VitexSoftware/php-ease-fluentpdo/issues) on GitHub
- Contact: info@vitexsoftware.cz
- Visit: https://www.vitexsoftware.cz