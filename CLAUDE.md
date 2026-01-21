<!-- OPENSPEC:START -->
# OpenSpec Instructions

These instructions are for AI assistants working in this project.

Always open `@/openspec/AGENTS.md` when the request:
- Mentions planning or proposals (words like proposal, spec, change, plan)
- Introduces new capabilities, breaking changes, architecture shifts, or big performance/security work
- Sounds ambiguous and you need the authoritative spec before coding

Use `@/openspec/AGENTS.md` to learn:
- How to create and apply change proposals
- Spec format and conventions
- Project structure and guidelines

Keep this managed block so 'openspec update' can refresh the instructions.

<!-- OPENSPEC:END -->

# CLAUDE.md

This file provides guidance to Claude Code when working with this repository.

## Project Overview

**Botble CMS v7.6.0** - Modular Laravel CMS built on Laravel 12.x (PHP 8.2+)

**Official Documentation**: https://docs.botble.com/cms

### Architecture
```
/platform/
├── core/      # Core modules (ACL, base, dashboard, media, settings, table)
├── packages/  # Packages (menu, page, SEO, theme, widget)
├── plugins/   # Feature plugins (blog, analytics, contact, gallery)
└── themes/    # Frontend themes (default: ripple)
```

### Tech Stack
- **Backend**: Laravel 12.x, PHP 8.2+
- **Frontend**: Vue.js 3, Bootstrap 5.3.7, jQuery
- **Build**: Laravel Mix, npm workspaces
- **Database**: MySQL (SQLite for tests)
- **UI**: Tabler UI (https://docs.tabler.io/ui)

## Development Commands

```bash
# Build
npm run dev|prod|watch              # Development/production/watch builds
npm run format                      # Format JS/Vue/Blade files

# Testing & Quality
php artisan test                    # Run PHPUnit tests (SQLite)
./vendor/bin/pint [path]            # Format PHP (PSR-12 + custom rules)
./vendor/bin/phpstan analyse --level=5    # Static analysis
./vendor/bin/rector process         # Automated refactoring

# CMS Commands
php artisan cms:plugin:list|activate {name}
php artisan cms:theme:activate {name}
php artisan cms:make:form|table {name}
php artisan cms:publish:assets
php artisan cms:backup:create {name}
php artisan cms:translations:export
```

## Coding Standards

### Naming Conventions
- Files: `kebab-case.php`
- Classes/Enums: `PascalCase`
- Methods: `camelCase`
- Variables/Properties: `snake_case`
- Constants/Enum Cases: `SCREAMING_SNAKE_CASE`

### Best Practices
1. Eloquent queries: `User::query()->where(...)`
2. Translations: `trans()` for admin, `__()` for frontend
3. HTTP requests: Use Laravel HTTP facade
4. Options: Use enums for select/radio/checklist
5. **ALWAYS run `./vendor/bin/pint` on changed files before commit**
6. Remove unnecessary comments from code (keep only `@var`, `@param`, `@return` docblock comments)

### Plugin Structure
```
/platform/plugins/my-plugin/
├── config/
├── database/migrations/
├── resources/ (lang/, views/, js/, sass/)
├── routes/
├── src/
│   ├── Database/, Enums/, Exporters/, Importers/
│   ├── Forms/, Http/, Models/, Tables/
│   ├── Listeners/, Providers/, Services/, Supports/
│   ├── Repositories/, Widgets/
│   └── Plugin.php
└── plugin.json
```

### Form & Table Builders
```php
// Form (extends FormAbstract)
$this->setupModel(new MyModel)
    ->setValidatorClass(MyRequest::class)
    ->add('field', 'text', ['label' => trans('...'), 'required' => true]);

// Table (extends TableAbstract)
$this->model(MyModel::class)->addColumns([Column::make('name')]);
```

## Shortcodes

### Adding New Shortcodes

When adding a new shortcode to a theme, follow these steps:

1. **Register the shortcode** in `platform/themes/{theme}/functions/shortcodes.php` or a dedicated file like `shortcodes-{feature}.php`

2. **Create view partials** in `platform/themes/{theme}/partials/shortcodes/{shortcode-name}/`
   - `index.blade.php` - Frontend rendering
   - `admin.blade.php` - Admin configuration form (if needed)

3. **Add preview image** for the shortcode picker in admin:
   ```php
   Shortcode::setPreviewImage('shortcode-name', Theme::asset()->url('images/ui-blocks/shortcode-name.png'));
   ```

4. **Capture screenshot** for the preview image:
   ```bash
   # Capture screenshot from demo/local site
   npm run shortcode-screenshot shortcode-name --site=https://demo.site.com

   # With custom URL and selector
   npm run shortcode-screenshot shortcode-name --url=/page-path --selector=".shortcode-section"

   # List all configured shortcodes
   npm run shortcode-screenshot --list

   # Show help
   npm run shortcode-screenshot --help
   ```

5. **Update screenshot config** (if needed) in `platform/packages/shortcode/assets/js/capture-shortcode-preview.js`:
   ```javascript
   'my-shortcode': { url: '/page-with-shortcode', selector: '.my-shortcode-class' },
   ```

**Screenshot script options:**
- `--site=<url>` - Base URL (default: http://localhost)
- `--theme=<name>` - Theme name (default: gerow)
- `--url=<path>` - Page URL path
- `--selector=<css>` - CSS selector for the section
- `--full` - Capture full page
- `--wait=<ms>` - Wait time before capture (default: 1000)

**Note:** Some shortcodes (sliders, iframes, dynamic content) may not capture correctly with automation. In those cases, take manual screenshots and save to `platform/themes/{theme}/public/images/ui-blocks/`.

## Hooks System

```php
// Actions
do_action('event_name', $param1, $param2);
add_action('event_name', $callback, priority: 20);

// Filters
$value = apply_filters('filter_name', $value, $param);
add_filter('filter_name', $callback, priority: 20);
```

**Common hooks**: `BASE_ACTION_META_BOXES`, `BASE_FILTER_BEFORE_RENDER_FORM`, `BASE_ACTION_AFTER_CREATE_CONTENT`

## Database

**Standard fields**: `id`, `name`/`title`, `slug`, `status` (BaseStatusEnum), `created_at`, `updated_at`, `author_id`, `author_type`

### ID Type Support (Integer & UUID)
This CMS supports both integer IDs and UUIDs. Follow these guidelines:

**Function Parameters**:
```php
// ❌ BAD - Only supports integer IDs
public function show(int $id): Response

// ✅ GOOD - Supports both integer and UUID
public function show(int|string $id): Response
```

**Migrations - Foreign Keys**:
```php
// ❌ BAD - Hardcoded to big integer
$table->bigInteger('user_id')->unsigned();

// ✅ GOOD - Uses foreignId which adapts to the referenced table's ID type
$table->foreignId('user_id')->constrained();

// ✅ ALSO GOOD - With custom table reference
$table->foreignId('author_id')->constrained('users');
```

**Route Model Binding**:
- Models using UUIDs should have `$keyType = 'string'` set
- Use `getRouteKeyName()` if the route key differs from the primary key

## Multi-language

```php
trans('plugins/blog::posts.create')  // Admin panel (core/packages/plugins)
__('theme.home')                     // Frontend (themes)
```

**Translation Workflow**:
1. Add new strings to `resources/lang/en/*.php` in your module first
2. Translate to all other languages in `resources/lang/{locale}/*.php`
3. Translation paths: `platform/{core|packages|plugins}/*/resources/lang/`
4. **ALWAYS check for syntax errors after translation:**
   ```bash
   # Check specific file
   php -l platform/packages/theme/resources/lang/fr/theme.php

   # Check all translation files in a module
   find platform/core/base/resources/lang -name "*.php" -exec php -l {} \;
   ```
5. **Run Pint to format translation files:**
   ```bash
   ./vendor/bin/pint platform/core/base/resources/lang
   ```

**Translation Best Practices**:
- **CRITICAL**: Avoid using arrays as translation values - always use strings
- **NEVER** convert existing string translations to arrays (causes "Array to string conversion" errors)
- **ALWAYS escape apostrophes in single-quoted strings:**
  ```php
  // ❌ BAD - Unescaped apostrophe causes syntax error
  'message' => 'L'utilisateur n'existe pas',

  // ✅ GOOD - Escaped apostrophes
  'message' => 'L\'utilisateur n\'existe pas',

  // ✅ ALSO GOOD - Use double quotes (no escaping needed for apostrophes)
  'message' => "L'utilisateur n'existe pas",
  ```
- If you need structured translations, create separate flat keys instead:
  ```php
  // ❌ BAD - Array value
  'settings' => [
      'title' => 'Settings',
      'description' => 'Configure your settings',
  ],

  // ✅ GOOD - Flat string keys
  'settings_title' => 'Settings',
  'settings_description' => 'Configure your settings',
  ```

**Common Translation Syntax Errors**:
- **French, Italian**: Unescaped apostrophes (l'élément → l\'élément)
- **Turkish, Vietnamese**: Special characters in single quotes need escaping
- **All languages**: Missing commas between array elements
- **Fix with**: `php -l <file>` to check syntax, then escape apostrophes with backslash

**AI Translation Tools** (for bulk translation tasks):
- Google Cloud Translation API
- DeepL API
- AWS Translate

### Translation Scripts & Dictionaries

**Location**: `../shell-scripts/translation-scripts/`

**Purpose**: Reusable translation management system for multi-site deployment

**Workflow**:
1. **When translating seeders**, use scripts from `../shell-scripts/translation-scripts/`:
   - `extract-existing-translations.php` - Extract translations from existing seeder files
   - `generate-page-translations.php` - Generate page content translations
   - `dictionaries/` - JSON translation dictionaries for each language

2. **Every time you translate a new word/phrase**:
   - **MUST update** dictionaries in `../shell-scripts/translation-scripts/dictionaries/`
   - Keep dictionaries synchronized across all projects
   - Dictionaries are key-value pairs: `{"English text": "Translated text"}`

3. **Translation embedding in seeders**:
   - Copy translations **directly** into seeder methods (self-contained)
   - Seeders should NOT depend on external scripts at runtime
   - Scripts are for translation generation only, not runtime execution

**Available Dictionaries**:
- `dictionaries/ar.json` - Arabic translations
- `dictionaries/vi.json` - Vietnamese translations
- `dictionaries/fr.json` - French translations
- `dictionaries/id.json` - Indonesian translations

**Example workflow**:
```bash
# 1. Extract existing translations (if any)
cd ../shell-scripts/translation-scripts
php extract-existing-translations.php

# 2. Generate page translations
php generate-page-translations.php

# 3. Update dictionaries with new translations
# Edit dictionaries/*.json to add new entries

# 4. Copy translations to TranslationSeeder
# Embed translations directly in seeder methods
```

**Important Notes**:
- Dictionaries enable translation reuse across multiple sites
- Always update dictionaries when adding new translations
- Seeders must be self-contained (no runtime dependencies on scripts)
- Handle apostrophe encoding carefully (straight `'` vs curly `'`)

## Security

```bash
# .env
SESSION_HTTP_ONLY=true
SESSION_SECURE_COOKIE=true
ENABLE_HTTP_SECURITY_HEADERS=true
```

```javascript
// CSRF in AJAX
headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
```

### XSS Prevention in Blade Templates

**For HTML contexts** - When using unescaped output `{!! !!}`, wrap with `BaseHelper::clean()`:

```php
// ❌ BAD - Vulnerable to XSS
{!! $userContent !!}
{!! $htmlMarkup !!}

// ✅ GOOD - XSS protected
{!! BaseHelper::clean($userContent) !!}
{!! BaseHelper::clean($htmlMarkup) !!}
```

**For JavaScript contexts** - Use `@json()` directive for safe JavaScript embedding:

```php
// ❌ BAD - Vulnerable to XSS and encoding issues
<script>
var message = '{!! trans('module::file.key') !!}';
var data = '{{ $variable }}';
</script>

// ✅ GOOD - Properly escaped for JavaScript
<script>
var message = @json(trans('module::file.key'));
var data = @json($variable);
</script>
```

**When to use each approach**:
- `{{ }}` - Default, auto-escapes for HTML (converts `&` to `&amp;`)
- `{!! BaseHelper::clean() !!}` - For trusted HTML content that needs to render
- `@json()` - For embedding values in JavaScript (handles quotes, special chars)

## UI Development

- Use **Tabler UI** components: https://docs.tabler.io/ui
- Follow Tabler patterns for consistency

### Badges (Admin Panel)

Reference: https://docs.tabler.io/ui/components/badges

**Color Classes**:
- Base: `bg-blue`, `bg-azure`, `bg-indigo`, `bg-purple`, `bg-pink`, `bg-red`, `bg-orange`, `bg-yellow`, `bg-lime`, `bg-green`, `bg-teal`, `bg-cyan`
- Light variants: Append `-lt` (e.g., `bg-blue-lt`, `bg-red-lt`)

**Style Variants**:
- `.badge` - Default square badge
- `.badge-pill` - Rounded corners
- `.badge-notification` - Positioned in top right corner
- `.badge-blink` - Animated blinking effect

**Sizes**: `.badge-sm` (small), `.badge` (default), `.badge-lg` (large)

```html
<!-- Examples -->
<span class="badge bg-green">Active</span>
<span class="badge bg-red-lt">Inactive</span>
<span class="badge badge-pill bg-blue">12</span>
```

## Routes

```php
// Admin routes
AdminHelper::registerRoutes(function(): void {
    Route::group(['prefix' => 'my-plugin', 'as' => 'my-plugin.'], function(): void {
        Route::resource('', 'MyController')->parameters(['' => 'item']);
    });
});

// Public/theme routes
Theme::registerRoutes(function(): void {
    Route::get('search', ['as' => 'public.search', 'uses' => 'PublicController@getSearch']);
});
```

## Available Form Fields & Options

**Field Types**: TextField, SelectField, EditorField, MediaImageField, MediaImagesField, OnOffField, TreeCategoryField, RadioField, CheckboxField, DatePickerField, TimePickerField, ColorField, RepeaterField, TagField, FileField, NumberField, HiddenField, etc.

**FieldOptions**: NameFieldOption, StatusFieldOption, ContentFieldOption, DescriptionFieldOption, SelectFieldOption, MediaImageFieldOption, DatePickerFieldOption, etc.

```php
// Modern pattern with FieldOptions
->add('name', TextField::class, NameFieldOption::make()->required())
->add('status', SelectField::class, StatusFieldOption::make())
->add('content', EditorField::class, ContentFieldOption::make()->allowedShortcodes())

// Date picker (date only)
->add('start_date', DatePickerField::class, DatePickerFieldOption::make()->label('Start Date'))

// Date time picker (with time)
->add('published_at', DatePickerField::class, DatePickerFieldOption::make()->label('Published At')->withTimePicker())
```

## Available Table Components

**Columns**: IdColumn, NameColumn, ImageColumn, StatusColumn, CreatedAtColumn, FormattedColumn, EnumColumn

**Actions**: EditAction, DeleteAction

**HeaderActions**: CreateHeaderAction

**BulkActions**: DeleteBulkAction

**BulkChanges**: NameBulkChange, StatusBulkChange, CreatedAtBulkChange, SelectBulkChange, IsFeaturedBulkChange

## Request Validation

```php
class MyRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:250'],
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }

    public function attributes(): array
    {
        return ['name' => trans('plugins/my::my.form.name')];
    }
}
```

## Repository Pattern

```php
// Interface
interface MyInterface extends RepositoryInterface {}

// Implementation
class MyRepository extends RepositoriesAbstract implements MyInterface
{
    // Custom methods using $this->model
}

// Register in ServiceProvider
$this->app->bind(MyInterface::class, MyRepository::class);
```

## Important Facades & Helpers

**Facades**:
- **AdminHelper**: Admin route registration, helpers
- **Assets**: Asset management (addScripts, addStyles)
- **DashboardMenu**: Menu registration
- **BaseHelper**: General CMS helpers
- **MetaBox**: Meta box management
- **Setting**: Settings management
- **RvMedia**: Media management
- **Form**, **Html**: Form/HTML builders

**Helper Functions**:
```php
is_in_admin()              // Check if in admin panel
page_title()               // Page title manager
dashboard_menu()           // Dashboard menu manager
render_editor($name, $value, $withShortcode)  // Render editor field
```

## Models & Query Scopes

**Base Model**: Extend `BaseModel` for all models (includes metadata, UUID support, macros)

**Common Query Scopes**:
```php
Post::query()->wherePublished()  // Filter by published status
```

**Model Traits**:
```php
use RevisionableTrait;  // Auto-track model changes

protected bool $revisionEnabled = true;
protected bool $revisionCleanup = true;
protected int $historyLimit = 20;
protected array $dontKeepRevisionOf = ['views'];
```

## Environment Variables

```bash
ADMIN_DIR=admin                  # Custom admin URL
CMS_ENABLE_INSTALLER=true        # Enable/disable installer
```

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2.28
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/socialite (SOCIALITE) - v5
- tightenco/ziggy (ZIGGY) - v2
- larastan/larastan (LARASTAN) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- rector/rector (RECTOR) - v2
- vue (VUE) - v3
- prettier (PRETTIER) - v3

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
</laravel-boost-guidelines>
