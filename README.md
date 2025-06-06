# API Health Monitoring Application

This is a Laravel 12-based API health monitoring application designed to monitor client API endpoints, send email alerts on failures, and display client endpoints via a Vue.js Single Page Application (SPA). The application is built to be scalable, supporting hundreds of clients with up to 12 endpoints each, and is deployed on a LAMP stack with Redis for queuing.

## Features
- **Client Input**: Manually enter client email addresses and API endpoints into the database.
- **Monitoring**: Checks each endpoint every 10 minutes via HTTP GET requests, with an 8-second timeout.
- **Alerts**: Sends email notifications (via AWS SES) when an endpoint is unreachable or returns a non-2xx status code.
- **Frontend**: Vue.js SPA with a dropdown to select clients and display their endpoints as clickable links with confirmation dialogs.
- **Scalability**: Uses Redis for queuing, chunked database queries, and efficient HTTP requests to handle large client volumes.
- **Testing**: Includes feature tests for endpoint monitoring.

## Requirements
- **PHP**: ^8.2
- **Laravel**: ^12.0
- **Node.js**: ^18.0 or higher
- **MySQL/MariaDB**: For storing client and endpoint data
- **Redis**: For queue management
- **AWS SES**: For email notifications
- **Composer**: For PHP dependencies
- **npm**: For frontend dependencies

## Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/api-health-monitor.git
   cd api-health-monitor
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment**
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Update `.env` with your database, Redis, and AWS SES credentials:
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=api_health_monitor
     DB_USERNAME=your_username
     DB_PASSWORD=your_password

     QUEUE_CONNECTION=redis
     REDIS_HOST=127.0.0.1
     REDIS_PORT=6379
     REDIS_CLIENT=predis
     REDIS_PASSWORD=null
     REDIS_DB=0

     MAIL_MAILER=smtp
     MAIL_SCHEME=null
     MAIL_HOST=sandbox.smtp.mailtrap.io
     MAIL_PORT=587
     MAIL_USERNAME=your_username
     MAIL_PASSWORD=your_password
     MAIL_ENCRYPTION=tls
     MAIL_FROM_ADDRESS="alerts@example.com"
     MAIL_FROM_NAME="${APP_NAME}"

     VITE_APP_NAME="${APP_NAME}"
     VITE_APP_URL="${APP_URL}"
     ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**
   ```bash
   php artisan migrate
   ```

6. **Build Frontend**
   ```bash
   npm run build
   ```

7. **Start Queue Worker**
   ```bash
   php artisan queue:work redis --queue=monitoring --tries=3 --timeout=60
   ```

8. **Schedule Endpoint Checks**
   - Add the following cron job to run the Laravel scheduler every minute:
     ```bash
     * * * * * cd /path/to/api-health-monitor && php artisan schedule:run >> /dev/null 2>&1
     ```

9. **Serve the Application**
   ```bash
   php artisan serve
   ```
   Access the application at `http://localhost:8000`.

## Usage
- **Add Clients and Endpoints**: Manually insert client emails and their API endpoints into the `clients` and `endpoints` tables in the database.
- **Monitoring**: The `endpoints:check` command runs every 10 minutes to check all endpoints. If an endpoint fails (non-2xx status or timeout), an email is sent to the client.
- **Frontend**: Navigate to the homepage, select a client from the dropdown, and view their endpoints as clickable links. Clicking a link prompts a confirmation dialog before opening the URL in a new tab.

## Project Structure
```
api-health-monitor/
├── app/
│   ├── Console/Commands/CheckEndpoints.php        # Command to check endpoints
│   ├── Http/Controllers/ClientController.php      # API controller for client data
│   ├── Jobs/SendAlertEmail.php                    # Queued job for email alerts
│   ├── Mail/EndpointDown.php                      # Mailable for alert emails
│   ├── Models/                                    # Eloquent models
│   └── Services/EndpointChecker.php               # Service for checking endpoints
├── database/migrations/                          # Database schema
├── resources/
│   ├── js/                                       # Vue.js SPA
│   └── views/                                    # Blade templates
├── routes/                                       # API and web routes
├── tests/Feature/EndpointMonitoringTest.php       # Feature tests
├── composer.json                                  # PHP dependencies
├── package.json                                   # Frontend dependencies
└── README.md
```

## Testing
Run the test suite using Pest or PHPUnit:
```bash
php artisan test
```
The tests verify endpoint monitoring and email queuing for both successful and failed requests.

## Scalability Considerations
- **Database**: Indexes on `clients.email` and `endpoints.client_id` for fast queries.
- **Queueing**: Redis-backed queues for email notifications to handle high volumes.
- **Chunking**: Processes endpoints in chunks of 100 to optimize memory usage.
- **HTTP Requests**: Uses Laravel’s `Http` facade with an 8-second timeout for efficiency.

## Troubleshooting
- **Queue Issues**: Ensure Redis is running and the queue worker is active.
- **Email Failures**: Verify  credentials and region in `.env`.
- **Frontend Errors**: Run `npm run build` to ensure Vite compiles assets correctly.

## Contributing
- Follow Laravel coding standards (use `laravel/pint` for linting).
- Submit pull requests with clear descriptions of changes.
- Add tests for new features or bug fixes.

## License
This project is licensed under the MIT License.