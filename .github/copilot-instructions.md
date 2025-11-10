# Copilot Instructions for Laravel Upwork Job Feed

## Architecture Overview

This is a **Laravel 12 + Inertia + Vue 3** application that fetches jobs from Upwork's GraphQL API and sends matched jobs to users via Telegram. The core workflow:

1. **Scheduler** (`routes/console.php`) runs `app:fetch-jobs` every 2 minutes
2. **FetchUpworkJobs** command pulls jobs from Upwork GraphQL API, creates `UpworkJob` records
3. For each new job, dispatches **FindMatchingUsers** job to the queue
4. Job iterates through active `Feed` models, calling `Feed::matchesJob()` to evaluate category + keyword rules
5. Matched feeds trigger Telegram notifications via `TelegramNotificationService`

## Key Integration Points

### Upwork OAuth2 & GraphQL
- **Provider**: `App\Services\UpworkProvider` (custom OAuth2 provider extending `league/oauth2-client`)
- **Service**: `App\Services\UpworkService` handles token refresh + GraphQL queries
- Tokens cached with automatic refresh logic (check `getValidToken()`)
- GraphQL queries return jobs with nested client/category data - map carefully in `UpworkJob::createFromUpworkArray()`

### Telegram Integration
- Uses `irazasyed/telegram-bot-sdk` for bot API
- **Auth**: Telegram Login Widget validates signatures (see `TelegramAuthService`)
- **Notifications**: `TelegramNotificationService::sendJobNotification()` formats job data with HTML, respects 4000-char limit
- Users must have `telegram_id` to receive notifications

### Queue System
- `FindMatchingUsers` job runs for EVERY new Upwork job
- Uses chunk(20) to batch process feeds - avoid memory issues with large datasets
- Command stops importing on duplicate job ID (see `UniqueConstraintViolationException` catch)

## Development Workflow

### Running the App
```bash
composer run dev  # Runs: php artisan serve + queue:listen + pail + npm run dev
```

For production-like setup:
```bash
php artisan serve
npm run dev
php artisan queue:work     # Required for job matching
php artisan schedule:work  # Required for scheduled imports
```

### Database
- **Default**: SQLite at `database/database.sqlite`
- Run `php artisan migrate --seed` to populate Upwork categories
- Key models: `UpworkJob`, `Feed`, `UpworkCategory`, `User`

### Testing
```bash
composer test  # Runs Pest tests
```

## Project-Specific Patterns

### Feed Matching Logic
`Feed::matchesJob()` implements AND logic across rules:
- Categories: If feed has categories selected, job MUST match one
- Search rules: Each rule is an AND condition - all must pass
- Keywords within a rule: 'any', 'all', or 'none' condition
- Search locations: 'title', 'description', 'category'

Example rule:
```php
[
    'keywords' => ['laravel', 'vue'],
    'location' => ['title', 'description'],
    'condition' => 'all'  // Both keywords must appear
]
```

### Inertia + Vue Frontend
- Pages in `resources/js/pages/` (auto-loaded via glob in `app.ts`)
- UI components from **shadcn-vue** (Reka UI + Tailwind 4) - import from `@/components/ui/`
- Use `<Link>` from `@inertiajs/vue3`, not `<a>` tags for internal navigation
- TypeScript enabled - prefer `.ts` over `.js`

### Model Relationships
- `Feed` belongsToMany `UpworkCategory` (pivot: `feed_categories`)
- `Feed` belongsTo `User`
- `UpworkJob` belongsTo `UpworkCategory` (mapped by `slug` during import)

### Conventions
- **Controllers**: Resource controllers for CRUD (see `FeedController`)
- **Authorization**: Always scope queries by `auth()->id()` for user resources
- **Validation**: Inline in controller methods (no FormRequest classes yet)
- **Routing**: `routes/web.php` for web, `routes/auth.php` for auth, `routes/settings.php` for settings

## Configuration

### Required .env Variables
```env
UPWORK_CLIENT_ID=your_id
UPWORK_CLIENT_SECRET=your_secret
UPWORK_REDIRECT_URI=/oauth/upwork/callback

TELEGRAM_BOT_NAME=YourBotName
TELEGRAM_TOKEN=your_bot_token
TELEGRAM_REDIRECT_URI=/oauth/telegram/callback
```

### Upwork API Setup
1. Create app at Upwork, get client credentials
2. Redirect URI must match: `{APP_URL}{UPWORK_REDIRECT_URI}`
3. Initial auth: visit `/oauth/upwork` route
4. Token stored in Laravel cache, auto-refreshes

## Common Tasks

### Adding a New Search Rule Type
1. Update `Feed::evaluateRule()` to handle new rule structure
2. Add validation in `FeedController` for the new rule format
3. Update Vue form component to include new rule UI

### Modifying Job Import Logic
- **Command**: `app/Console/Commands/FetchUpworkJobs.php`
- Stops on first duplicate (UniqueConstraintViolationException)
- Update `UpworkService::searchJobs()` to modify GraphQL query
- Map new fields in `UpworkJob::createFromUpworkArray()`

### Changing Notification Format
Edit `TelegramNotificationService::formatJobMessage()` - watch the 4000-char limit, HTML parsing enabled
