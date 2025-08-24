# Laravel + Inertia + Vue Starter Kit - Development Guidelines

This is a modern full-stack web application starter kit built with Laravel and Vue.js, designed for rapid development with GitHub Copilot.

## Technology Stack

- **Backend**: PHP 8.4, Laravel 12, Inertia.js v2, MySQL, Event-based architecture
- **Frontend**: Vue 3 (Composition API), Shadcn-vue, Tailwind CSS v4, Inertia.js v2, TypeScript
- **Infrastructure**: Docker, Task (taskfile.dev), Pest v4 testing framework
- **Analysis**: Laravel Pint, ESLint, PHPStan

## Development Workflow

**Essential Commands (Task-based)**:
```bash
task help                    # See all available tasks
task up                      # Start development environment
task down                    # Stop containers
task init                    # First-time setup (env, deps, migrations)
task a -- migrate            # Run artisan commands (task a -- [artisan command])
task composer -- install     # Composer operations
task npm -- run build        # NPM operations
task pint                     # Laravel Pint code formatter
task test                     # Run Pest test suite
```

**Docker Environment**:
- **PHP-FPM**: Main Laravel application
- **Nginx**: Web server at http://localhost
- **Node**: Vite dev server at http://localhost:5173
- **MySQL**: Database server
- **Mailpit**: Email testing at http://localhost:8025
- **Queue/Scheduler**: Background job processing

## Key Application Patterns

**Inertia.js Vue Integration**:
- Pages in `resources/js/pages/` with nested structure
- Shared layouts (`AppLayout.vue`)
- Component library in `resources/js/components/`
- Type-safe props with TypeScript interfaces
- Use `<script setup>` syntax consistently with Composition API

**Form Architecture**:
- Resource controllers with Policy-based authorization
- Form Request classes for validation
- Inertia form handling with `router.post()` and form state management
- Multi-mode forms with proper validation feedback

**Service Layer Architecture**:
- Service-based architecture for external API integrations
- Composable pattern for Vue components
- Graceful fallback when external services are unavailable
- Automatic data formatting and normalization

## Coding Standards & Best Practices

- **KISS**: Keep It Simple, Stupid - favor clarity over cleverness
- **PHP**: Always use `declare(strict_types=1);`, PHP 8.4 features, Laravel conventions
- **Vue**: Composition API with `<script setup>`, TypeScript interfaces, Tailwind CSS v4
- **Database**: Eloquent models with relationships, scopes, and caching
- **Testing**: Pest framework with Feature and Unit tests, use factories for test data
- **Internationalization**: Multi-language support via lang files and `t()` helper in Vue

### Laravel-Specific Patterns

- **Controllers**: Resource controllers with Policy authorization
- **Models**: Eloquent relationships, scopes for filtering, proper type declarations
- **Services**: External API integrations with proper error handling
- **Validation**: Form Request classes, not inline validation
- **Cache**: Intelligent caching for frequently accessed data
- **Queue**: Background processing for time-consuming operations

### Vue/Frontend Patterns
- **Always use shadcn-vue if possible** https://www.shadcn-vue.com/
- **Always use shadcn-vue components if possible** https://www.shadcn-vue.com/docs/components/
- **Components**: Single File Components with TypeScript
- **Props**: Define clear interfaces with proper type declarations
- **State**: Reactive refs and computed properties, avoid complex state management
- **API Calls**: Composables for reusable API logic
- **Icons**: Use unified `Icon.vue` component with Lucide icons
- **Forms**: Inertia form helpers with proper error handling

### Performance Guidelines

- Use Eloquent eager loading to prevent N+1 queries
- Implement intelligent caching strategies
- Lazy load images and implement pagination for large datasets
- Optimize Vite build for production deployments

## Testing Philosophy

- **Pest Framework**: Modern PHP testing with descriptive test names
- **Feature Tests**: End-to-end functionality testing via HTTP requests
- **Unit Tests**: Focused testing of individual components/services
- **Browser Tests**: Pest v4 browser testing for UI interactions
- **Factory Pattern**: Use model factories for consistent test data

## Security & Validation

- **Authorization**: Laravel Policies for resource access control
- **Validation**: Form Request classes with comprehensive rules
- **CSRF Protection**: Built-in Laravel CSRF for all form submissions
- **Input Sanitization**: Proper data validation and sanitization
- **API Rate Limiting**: Throttling for external API calls

## Internationalization

- **Multi-language**: Support for multiple languages (French/English by default)
- **Translation Files**: Organized by feature in `lang/` directory
- **Frontend Integration**: `t()` helper function available in all Vue components
- **Middleware**: `HandleInertiaRequests` shares translations with frontend

## Application Architecture Best Practices

**Model Design**:
- Use proper Eloquent relationships and type declarations
- Implement model scopes for common queries
- Use enums for status fields and content types
- Add appropriate database indexes for performance

**Controller Design**:
- Keep controllers thin, move business logic to services
- Use Form Request classes for validation
- Implement proper authorization with Policies
- Return consistent API responses

**Frontend Component Structure**:
- Create reusable UI components in `resources/js/components/ui/`
- Use composables for shared logic between components
- Implement proper TypeScript interfaces for props and data
- Follow consistent naming conventions

**Error Handling**:
- Use Laravel's exception handling for backend errors
- Implement user-friendly error messages in the frontend
- Log errors appropriately for debugging
- Provide fallback UI states for failed requests

**Code Organization**:
- Group related files together (controllers, requests, policies)
- Use consistent naming conventions across the application
- Implement proper separation of concerns
- Document complex business logic

Remember: This starter kit is designed for rapid development of modern web applications. Prioritize user experience, maintainable code, and proper testing coverage while leveraging the full power of Laravel and Vue.js ecosystems.
