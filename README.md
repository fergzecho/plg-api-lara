# Customer.io Segment Members API

A Laravel-based REST API application that provides secure access to Customer.io segment membership data with API key authentication.

## Features

- **Customer.io Integration**: Fetch all members of any Customer.io segment with automatic pagination
- **API Key Authentication**: Secure X-API-KEY header-based authentication for all endpoints
- **Unlimited Processing**: No execution time limits for large segments with thousands of members
- **Comprehensive Logging**: Full request/response logging for debugging and monitoring
- **Laravel 12**: Built on the latest Laravel framework with PHP 8.2+

## API Endpoints

### Get Segment Members
Retrieve all members of a specific Customer.io segment.

```
GET /api/segments/{id}/members
```

**Headers:**
```
X-API-KEY: your-api-key-here
Content-Type: application/json
```

**Parameters:**
- `id` (required): The Customer.io segment ID
- `start` (optional): Pagination start token (handled automatically)
- `limit` (optional): Items per page (default: 100, max handled by Customer.io)

**Response:**
```json
[
  {
    "id": "user123",
    "email": "user@example.com"
  }
]
```

**Error Response:**
```json
{
  "error": "Unauthorized"
}
```

## Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd plg-api-lara
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Environment setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure environment variables:**
   ```env
   API_KEY=your-secure-api-key-here
   CUSTOMER_IO_API_KEY=your-customer-io-api-key
   ```

5. **Run the application:**
   ```bash
   php artisan serve
   ```

## Configuration

### Required Environment Variables

- `API_KEY`: Your custom API key for authenticating requests to this application
- `CUSTOMER_IO_API_KEY`: Your Customer.io API key for accessing their services

### Optional Configuration

- `APP_ENV`: Application environment (local, production, etc.)
- `LOG_LEVEL`: Logging level (debug, info, warning, error)
- `DB_CONNECTION`: Database connection (defaults to SQLite)

## Authentication

All API endpoints require authentication via the `X-API-KEY` header:

```bash
curl -H "X-API-KEY: your-secure-api-key-here" \
     -H "Content-Type: application/json" \
     http://localhost:8000/api/segments/149/members
```

## Usage Examples

### Fetch segment members
```bash
curl -H "X-API-KEY: SUPERMANSECRETKET" \
     -H "Content-Type: application/json" \
     http://localhost:8000/api/segments/149/members
```

### With custom pagination
```bash
curl -H "X-API-KEY: SUPERMANSECRETKET" \
     -H "Content-Type: application/json" \
     "http://localhost:8000/api/segments/149/members?limit=50"
```

## Technical Details

### Architecture
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Database**: SQLite (default), configurable
- **Authentication**: Custom middleware-based API key validation
- **External API**: Customer.io REST API integration

### Key Features
- **Automatic Pagination**: Handles Customer.io's pagination seamlessly
- **Rate Limiting**: Built-in 1-second delay between API calls
- **Error Handling**: Comprehensive error responses and logging
- **No Timeout Limits**: Processes segments of any size without timing out

### Middleware
- `api.key`: Custom API key authentication middleware
- Applied to all `/api/segments/*` routes

## Development

### Running Tests
```bash
composer test
```

### Code Style
```bash
./vendor/bin/pint
```

### Development Server with Watching
```bash
composer dev
```

This runs the server, queue worker, logs, and Vite in parallel.

## Deployment

1. Set `APP_ENV=production` in your `.env`
2. Configure your production database
3. Set secure values for `API_KEY` and `CUSTOMER_IO_API_KEY`
4. Run `php artisan config:cache` and `php artisan route:cache`

## Security

- API key authentication required for all endpoints
- Environment-based configuration
- Comprehensive request logging
- No sensitive data in version control

## License

MIT License - feel free to use this for your projects.
