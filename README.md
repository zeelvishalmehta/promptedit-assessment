# PromptEdit Assessment – Pixelforge Image Generation

A Laravel and Vue.js application that integrates with the Pixelforge API to generate images, estimate generation costs, calculate actual token-based charges, and maintain generation history.

## Project Overview

Users can register, log in, submit image-generation requests, view estimated prices before generation, and track the status and cost of their requests.

The application integrates with the asynchronous Pixelforge API and supports webhook notifications, background processing, and scheduled polling to synchronize generation statuses.

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
- Process webhook events asynchronously using Laravel queues.
- Retry failed jobs with configured backoff intervals.
- Run scheduled polling for queued and processing generations.
- Refresh generation history automatically while requests are pending.

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
- **ProcessPixelforgeWebhook:** Processes webhook events asynchronously.
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

```bash
php artisan queue:work --tries=6 --timeout
