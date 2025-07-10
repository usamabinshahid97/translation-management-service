# Translation Management Service

A high-performance Laravel-based API service for managing translations across multiple locales with advanced tagging and search capabilities.

## Features

- **Multi-locale Support**: Store translations for multiple languages (en, fr, es, de, etc.)
- **Advanced Tagging**: Categorize translations with context tags (mobile, web, desktop, etc.)
- **Full-text Search**: Search translations by keys, values, or content
- **High Performance**: Optimized for 100k+ records with sub-500ms response times
- **Token Authentication**: Secure API with Laravel Sanctum
- **JSON Export**: Frontend-ready translation exports
- **Docker Support**: Complete containerized setup
- **Comprehensive Testing**: Feature and performance tests
- **OpenAPI Documentation**: Complete Swagger/OpenAPI 3.0 documentation
- **CDN Support**: Multi-provider CDN integration with caching and versioning

## Requirements

- PHP 8.1+
- MySQL 8.0+
- Redis 6.0+
- Composer
- Docker & Docker Compose (for containerized setup)

## Installation

### Docker Setup (Recommended)

1. Clone the repository:
```bash
git clone <repository-url>
cd TranslationManagementService
```

2. Build and start the containers:
```bash
docker-compose up -d
```

3. Install dependencies:
```bash
docker-compose exec app composer install
```

4. Run migrations:
```bash
docker-compose exec app php artisan migrate
```

5. Generate test data:
```bash
docker-compose exec app php artisan translations:generate 100000
```

The API will be available at `http://localhost:8080`

### Local Setup

1. Clone and install dependencies:
```bash
git clone <repository-url>
cd TranslationManagementService
composer install
```

2. Configure environment:
```bash
cp .env.example .env
# Update database and Redis configuration
```

3. Generate application key:
```bash
php artisan key:generate
```

4. Run migrations:
```bash
php artisan migrate
```

5. Start the server:
```bash
php artisan serve
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user (requires auth)
- `GET /api/auth/me` - Get current user (requires auth)

### Translations
- `GET /api/translations` - List translations (with filtering)
- `POST /api/translations` - Create translation (requires auth)
- `GET /api/translations/{id}` - Get translation (requires auth)
- `PUT /api/translations/{id}` - Update translation (requires auth)
- `DELETE /api/translations/{id}` - Delete translation (requires auth)
- `GET /api/translations/search?q=term` - Search translations (requires auth)
- `GET /api/translations/export?locale=en` - Export translations (public)

### Locales
- `GET /api/locales` - List locales (requires auth)
- `POST /api/locales` - Create locale (requires auth)
- `GET /api/locales/{id}` - Get locale (requires auth)
- `PUT /api/locales/{id}` - Update locale (requires auth)
- `DELETE /api/locales/{id}` - Delete locale (requires auth)

### Tags
- `GET /api/tags` - List tags (requires auth)
- `POST /api/tags` - Create tag (requires auth)
- `GET /api/tags/{id}` - Get tag (requires auth)
- `PUT /api/tags/{id}` - Update tag (requires auth)
- `DELETE /api/tags/{id}` - Delete tag (requires auth)

## Usage Examples

### Create Translation
```bash
curl -X POST http://localhost:8080/api/translations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "key": "welcome.message",
    "value": "Welcome to our application",
    "locale_code": "en",
    "tags": ["web", "mobile"]
  }'
```

### Search Translations
```bash
curl "http://localhost:8080/api/translations/search?q=welcome&locale=en" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Export Translations
```bash
curl "http://localhost:8080/api/translations/export?locale=en&tags[]=web"
```

## API Documentation

Interactive API documentation is available at:
- Swagger UI: `http://localhost:8080/api/documentation`
- OpenAPI JSON: `http://localhost:8080/api/documentation.json`

## CDN Configuration

The service supports multiple CDN providers for asset delivery:

### Enable CDN
```bash
# In .env file
CDN_ENABLED=true
CDN_DEFAULT_PROVIDER=cloudflare
CDN_CLOUDFLARE_BASE_URL=https://cdn.yourservice.com
```

### CDN Management Commands
```bash
# Check CDN status
php artisan cdn:manage info

# Purge CDN cache
php artisan cdn:manage purge --path=css/app.css
php artisan cdn:manage purge --all

# Preload assets
php artisan cdn:manage preload --all

# Health check
php artisan cdn:manage health
```

### CDN Helper Functions
```php
// Generate CDN URL
echo cdn_asset('css/app.css');
// Output: https://cdn.yourservice.com/assets/css/app.css?v=1609459200

// Use specific provider
echo cdn_asset('js/app.js', ['provider' => 'aws_cloudfront']);
```

## Performance Features

- **Database Indexing**: Optimized indexes on frequently queried columns
- **Redis Caching**: Cached exports and search results
- **Batch Operations**: Efficient bulk data operations
- **Query Optimization**: Eager loading and optimized queries
- **Full-text Search**: MySQL full-text indexes for fast content search
- **CDN Integration**: Multi-provider CDN support with automatic versioning
- **Asset Optimization**: Compression and caching headers for static assets

## Testing

Run the test suite:
```bash
# In Docker
docker-compose exec app php artisan test

# Local
php artisan test
```

Generate test data:
```bash
php artisan translations:generate 100000
```

## Database Schema

### Locales
- `id` - Primary key
- `code` - Locale code (e.g., 'en', 'fr')
- `name` - Locale name (e.g., 'English', 'French')
- `is_active` - Status flag

### Tags
- `id` - Primary key
- `name` - Tag name (e.g., 'mobile', 'web')
- `description` - Tag description

### Translations
- `id` - Primary key
- `key` - Translation key
- `value` - Translation value
- `locale_id` - Foreign key to locales
- Unique constraint on (key, locale_id)
- Full-text index on (key, value)

### Translation_Tags (Pivot)
- `translation_id` - Foreign key to translations
- `tag_id` - Foreign key to tags

## Architecture Decisions

1. **Separate Locales Table**: Enables easy addition of new languages and locale-specific settings
2. **Many-to-Many Tags**: Flexible tagging system for complex categorization
3. **Composite Unique Keys**: Prevents duplicate translations per locale
4. **Full-text Indexing**: Fast content search across large datasets
5. **Redis Caching**: Reduces database load for frequently accessed data
6. **Sanctum Authentication**: Lightweight token-based auth for API
7. **Factory Pattern**: Efficient test data generation

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License.