# PromptEdit Assessment – Pixelforge Image Generation

A Laravel and Vue.js application that integrates with the Pixelforge API to generate images, estimate generation costs, calculate actual token-based charges, and maintain generation history.

## Live Application

**Application URL:** https://promptedit-assessment.ethiodeploy.app/

**GitHub Repository:** https://github.com/zeelvishalmehta/promptedit-assessment

Users can register, log in, submit image-generation requests, and view their generation history through the deployed application.

## Project Overview

Users can register, log in, submit image-generation requests, view estimated prices before generation, and track the status and cost of their requests.

The application integrates with the asynchronous Pixelforge API and supports webhook notifications, job processing, and scheduled polling to synchronize generation statuses.

## Features

### User Authentication

- User registration and login.
- Authenticated image-generation requests.
- User-specific generation history.

### Image Generation

- Submit a text prompt.
- Select image dimensions.
- Choose generation quality.
- Optionally provide reference image URLs.
- Submit requests to the Pixelforge API.
- Track generation status: queued, processing, succeeded, or failed.

### Pricing and Cost Calculation

- Retrieve pricing rates from Pixelforge.
- Estimate generation costs before submission.
- Display estimated customer pricing with a 25% markup.
- Calculate actual provider costs using token usage returned by Pixelforge.
- Calculate final customer charges using the same 25% markup.
- Failed generations have zero actual cost.

### Webhook Integration

- Receive Pixelforge generation events.
- Verify webhook signatures using HMAC SHA-256.
- Validate webhook timestamps.
- Prevent duplicate event storage using unique event IDs.
- Process webhook events through Laravel background jobs.
- Synchronize generation status and actual usage information.

### Background Processing and Polling

- Process webhook events using Laravel queues.
- Retry failed jobs with configured backoff intervals.
- Run scheduled polling for queued and processing generations.
- Refresh generation history automatically while requests are pending.
- Support synchronous queue processing in environments where persistent background workers are unavailable.

### Generation History

- View previous generation requests.
- View generated images and generation statuses.
- See estimated and actual provider/customer costs.
- View error information for failed generations.
- Navigate through paginated generation history.

## Technology Stack

| Component | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | Vue.js 3 |
| SPA Integration | Inertia.js |
| Styling | Tailwind CSS |
| Database | MySQL |
| API Integration | Pixelforge REST API |
| Background Jobs | Laravel Queue |
| Scheduling | Laravel Scheduler |
| Build Tool | Vite |
| Testing | PHPUnit / Laravel Tests |

## Application Architecture

The application follows a service-oriented Laravel structure.

- **GenerationController:** Handles generation requests, pricing estimates, and generation history.
- **PixelforgeService:** Handles communication with the external Pixelforge API.
- **PricingCalculator:** Calculates estimated and actual generation costs.
- **GenerationSyncService:** Synchronizes generation status and actual usage with the local database.
- **PixelforgeWebhookController:** Receives and verifies webhook events.
- **ProcessPixelforgeWebhook:** Processes webhook events through Laravel jobs.
- **SyncPendingGenerations:** Synchronizes pending generations through scheduled polling.

## Installation and Local Setup

### Prerequisites

- PHP 8.2 or later
- Composer
- Node.js and npm
- MySQL
- Pixelforge API account and credentials

### 1. Clone the repository

```bash
git clone https://github.com/zeelvishalmehta/promptedit-assessment.git
cd promptedit-assessment
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure environment variables

Create your local environment file:

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

Configure your database connection in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=promptedit_assessment
DB_USERNAME=root
DB_PASSWORD=
```

Configure Pixelforge credentials:

```env
PIXELFORGE_BASE_URL=https://pixelforge.promptedit.dev/v1
PIXELFORGE_API_KEY=your_pixelforge_api_key
PIXELFORGE_WEBHOOK_SECRET=your_pixelforge_webhook_secret
```

For local queue processing, configure:

```env
QUEUE_CONNECTION=database
```

**Important:** Never commit your `.env` file or expose API keys and webhook secrets.

### 4. Run database migrations

Create the database in MySQL, then run:

```bash
php artisan migrate
```

### 5. Start the application

Start the Laravel development server:

```bash
php artisan serve
```

Start the Vue/Vite development server in another terminal:

```bash
npm run dev
```

### 6. Start the queue worker

For local development:

```bash
php artisan queue:work --tries=6 --timeout=60
```

### 7. Start the Laravel scheduler

For local development:

```bash
php artisan schedule:work
```

The scheduler runs the pending-generation synchronization command every minute.

## Webhook Configuration

Configure the following webhook endpoint in your Pixelforge account:

```text
https://promptedit-assessment.ethiodeploy.app/webhooks/pixelforge
```

For local development, use a publicly accessible HTTPS tunnel or URL so Pixelforge can reach your application.

The webhook signing secret must match the value configured in `PIXELFORGE_WEBHOOK_SECRET`.

Supported events include:

- `generation.processing`
- `generation.succeeded`
- `generation.failed`
- `ping`

The application verifies webhook signatures and timestamps before processing events and prevents duplicate event storage.

## Production Deployment

The application is deployed using EthioDeploy's free hosting plan.

**Live URL:** https://promptedit-assessment.ethiodeploy.app/

Production environment variables include:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL`
- `APP_KEY`
- `DB_CONNECTION=mysql`
- `PIXELFORGE_BASE_URL`
- `PIXELFORGE_API_KEY`
- `PIXELFORGE_WEBHOOK_SECRET`
- `QUEUE_CONNECTION=sync`

Database connection details are provided through the hosting platform's managed MySQL add-on.

The free hosting plan may put the application to sleep after a period of inactivity. The first request after sleeping may take longer while the application starts.

Persistent background workers and scheduled tasks are not continuously available on this plan. Synchronous queue processing is configured for webhook jobs in production.

## Running Tests

Run the complete Laravel test suite:

```bash
php artisan test
```

The automated tests cover:

- Authentication and access control.
- Pricing calculations.
- Generation synchronization.
- Webhook signature verification.
- Duplicate webhook event handling.
- Generation history access.

## Continuous Integration

GitHub Actions is configured to run automated checks when code is pushed or a pull request is created.

The workflow:

- Installs PHP and Composer dependencies.
- Installs Node.js dependencies.
- Builds frontend assets.
- Configures a MySQL test database.
- Runs database migrations.
- Executes the Laravel automated test suite.

## Project Status

The core application functionality has been implemented, tested, and deployed successfully.

### Completed Features

- Laravel authentication.
- Pixelforge API integration.
- Image generation and status tracking.
- Estimated and actual token-based pricing with a 25% markup.
- Webhook signature verification using HMAC SHA-256.
- Duplicate webhook event protection.
- Generation synchronization through webhooks.
- Scheduled polling implementation.
- User-specific generation history.
- Automated Laravel test suite.
- GitHub Actions CI workflow.
- Public production deployment.

**Live Application:** https://promptedit-assessment.ethiodeploy.app/

**GitHub Repository:** https://github.com/zeelvishalmehta/promptedit-assessment

---

**Built with Laravel, Vue.js, Inertia.js, and Pixelforge API.**
