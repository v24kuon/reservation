## Technology Stack (Current)

### Backend
- **PHP**: 8.4.11 (CLI)
- **Laravel Framework**: 12.26.3 （Laravel 12.x / head 運用）
- **Database**:
  - Development: SQLite
  - Production: MySQL

### Frontend
- **Laravel Blade**: Server-side rendering (基本)
- **Livewire**: Dynamic functionality (必要な部分のみ)
- **Alpine.js**: Client-side interactions (Livewireに含まれる)
- **Node.js**: v22.2.0 (local env)
- **npm**: 10.7.0 (local env)
- **Vite**: ^7.0.4
- **Tailwind CSS**: ^4.0.0 (+ @tailwindcss/vite)
- **Laravel Vite Plugin**: ^2.0.0
- **Axios**: ^1.11.0
- **Concurrently**: ^9.0.1

### Payment System
- **Stripe**: Subscription payments
- **Laravel Cashier**: Stripe integration library
- **Stripe Checkout**: Payment processing
- **Webhooks**: Automatic synchronization

### Development Environment
- **Laravel Head**: Development environment
- **GitHub**: Version control & code sharing

### Dev Tools / Packages
- **Laravel Boost (dev)**: ^1.0
- **Laravel Pint (dev)**: ^1.24
- **Laravel Sail (dev)**: ^1.41
- **Laravel Pail (dev)**: ^1.2.2
- **PHPUnit (dev)**: ^11.5.3
- **FakerPHP (dev)**: ^1.23
- **Mockery (dev)**: ^1.6

### MCP Servers (Configured)
- **DeepWiki MCP**: `https://mcp.deepwiki.com/sse`
- **Laravel Boost MCP**: `php ./artisan boost:mcp`
- **Stripe MCP**: `npx @stripe/mcp --tools=all --api-key=***` (test key configured locally)

### Security Features
- **CSRF Protection**: Session-based CSRF attack prevention
- **XSS Prevention**: Blade template auto-escaping
- **SQL Injection Prevention**: Eloquent ORM parameter binding
- **Session Security**: HTTPS-only, HTTPOnly, SameSite settings
- **Rate Limiting**: API & form submission frequency limits
- **Input Validation**: Form Request strict validation

### Project-Specific Features
- **User Authentication**: Laravel Breeze/Fortify
- **Role Management**: Gates/Policies (user/instructor/admin)
- **Favorites System**: Store & instructor favorites
- **Reservation System**: Concurrent booking conflict resolution
- **Subscription Management**: Monthly billing with Stripe
- **Email Notifications**: Reservation confirmations & reminders

### Runtime/Local Notes
- Use `composer run dev` (see composer.json) to run server/queue/logs/vite concurrently
- Use MCP servers per project rules before implementation (DeepWiki/Boost, plus Stripe for Stripe-related work)
- All pages require authentication (login required)
- Mobile-first responsive design
- Hierarchical lesson categories (parent/child structure)
