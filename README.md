# Slim Async API

A high-performance REST API built with Slim Framework, Swoole, and MongoDB, implementing Clean Architecture principles and following PSR standards.

## Overview

This project demonstrates a modern PHP API architecture using:
- **Slim Framework 4** for HTTP routing and middleware
- **Swoole** for high-performance asynchronous processing
- **MongoDB** for document-based data storage
- **Clean Architecture** for maintainable and testable code
- **PSR-12** coding standards with automated formatting

## Features

- RESTful API with full CRUD operations
- Asynchronous request handling with Swoole coroutines
- MongoDB integration with proper data modeling
- Clean Architecture implementation (Domain, Application, Infrastructure, Presentation layers)
- Comprehensive test suite (Unit and Integration tests)
- Static analysis with PHPStan
- Code formatting with PHP CS Fixer
- OpenAPI 3.0 documentation
- Docker containerization

## Architecture

The project follows Clean Architecture principles with clear separation of concerns:

```
src/
├── Domain/              # Business logic and entities
│   ├── Entities/       # Domain entities
│   ├── ValueObjects/   # Value objects
│   ├── Enums/          # Enumerations
│   └── Interfaces/     # Repository interfaces
├── Application/         # Use cases and DTOs
│   ├── UseCases/       # Business use cases
│   ├── DTOs/           # Data transfer objects
│   └── Services/       # Application services
├── Infrastructure/      # External concerns
│   ├── Repositories/   # Data persistence
│   └── Mongo/          # Database connection
├── Presentation/        # Controllers and routing
│   ├── Controllers/    # HTTP controllers
│   ├── Container.php   # Dependency injection
│   └── RouteFactory.php # Route generation
└── routes/             # Route definitions
```

## Requirements

- PHP 8.2 or higher
- Swoole extension
- MongoDB 4.4 or higher
- Composer
- Docker (optional)

## Installation

### Using Docker (Recommended)

1. Clone the repository:
```bash
git clone <repository-url>
cd slim-async
```

2. Build and start the containers:
```bash
docker-compose up -d
```

3. Install dependencies:
```bash
docker exec slim_swoole_app composer install
```

### Manual Installation

1. Install PHP dependencies:
```bash
composer install
```

2. Configure MongoDB connection in your environment

3. Start the server:
```bash
php src/server.php
```

## API Endpoints

### Health Check
- `GET /` - Basic health check
- `GET /health` - Detailed service status
- `GET /health/mongo` - MongoDB connection test
- `GET /health/async` - Swoole coroutines test

### User Management
- `GET /users` - List users (with pagination)
- `POST /users` - Create user
- `GET /users/{id}` - Get user by ID
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user

## API Documentation

The API is documented using OpenAPI 3.0 specification. The documentation file is located at `docs/api/openapi.json`.

To view the documentation:
1. Copy the content of `docs/api/openapi.json`
2. Paste it into [Swagger Editor](https://editor.swagger.io/)
3. Explore the interactive documentation

## Development

### Code Quality

The project maintains high code quality through:

- **PHPStan** for static analysis (level 5)
- **PHP CS Fixer** for code formatting (PSR-12)
- **PHPUnit** for comprehensive testing

### Running Tests

```bash
# Run all tests
composer test

# Run unit tests only
composer test:unit

# Run integration tests only
composer test:integration
```

### Code Analysis

```bash
# Run static analysis
composer stan

# Format code
composer cs-fix

# Check code formatting
composer cs-check
```

### Development Scripts

```bash
# Start the server
composer start

# Run static analysis (level 5)
composer stan:level5

# Run static analysis (maximum level)
composer stan:level8
```

## Configuration

### Environment Variables

Create a `.env` file with the following variables:

```env
MONGO_HOST=localhost
MONGO_PORT=27017
MONGO_DB=slim_async
```

### MongoDB Setup

The application expects a MongoDB instance running on the configured host and port. The database and collections will be created automatically when first accessed.

## Testing

### Test Structure

```
tests/
├── Unit/              # Unit tests
│   ├── Domain/       # Domain layer tests
│   └── ...           # Other unit tests
└── Integration/      # Integration tests
    └── ...           # Database and API tests
```

### Running Specific Tests

```bash
# Run domain tests
vendor/bin/phpunit tests/Unit/Domain/

# Run integration tests
vendor/bin/phpunit tests/Integration/
```

## Performance

This API is optimized for high performance through:

- **Swoole coroutines** for asynchronous processing
- **MongoDB** for efficient document storage
- **Clean Architecture** for maintainable code
- **Static analysis** for bug prevention
- **Comprehensive testing** for reliability

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes following PSR-12 standards
4. Add tests for new functionality
5. Run the test suite and static analysis
6. Submit a pull request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for all new functionality
- Maintain 100% test coverage
- Use meaningful commit messages
- Keep functions small and focused
- Apply SOLID principles

## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Support

For questions and support, please open an issue in the repository or contact the development team.

## Changelog

### Version 1.0.0
- Initial release
- Complete CRUD API for users
- Clean Architecture implementation
- Comprehensive test suite
- Docker containerization
- OpenAPI documentation
