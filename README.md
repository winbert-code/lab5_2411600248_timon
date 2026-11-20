# ByteStock — Computer Parts Inventory Management System

Laravel MVC implementation of Laboratory Exercise 5, using the suggested theme
**computer parts shop** instead of the generic hardware-store example.

## Theme & visual identity

- **Business:** ByteStock, an online/local shop selling CPUs, GPUs, motherboards,
  RAM, storage, PSUs, cases, cooling, peripherals, and networking gear.
- **Color scheme ("Green Rush"):** deep greens `#0E5113` / `#03730B`,
  vivid green `#1DBF04`, lime `#A7F205` / `#98D800`, and off-white
  `#F8F8F8`. Sampled from the supplied reference image — no navy/blue.
- The same palette is applied to the login/register screens, dashboard,
  inventory tables, forms, badges, and buttons through `public/css/app.css`.

## Stack

- Laravel 13 (PHP 8.3, Composer)
- MySQL via XAMPP (`computer_parts_inventory`)
- Bootstrap 5 + Bootstrap Icons (CDN)
- Chart.js (CDN) for the dashboard category chart
- Server-rendered Blade views; no npm build step required

## Quick start

1. Start Apache/MySQL in XAMPP and ensure database `computer_parts_inventory` exists:
   ```powershell
   C:\xampp2\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS computer_parts_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```
2. Confirm `.env` database settings:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=computer_parts_inventory
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Migrate and seed:
   ```powershell
   C:\xampp2\php\php.exe artisan migrate:fresh --seed
   ```
4. Run the app:
   ```powershell
   C:\xampp2\php\php.exe artisan serve --host=127.0.0.1 --port=8000
   ```
5. Open <http://127.0.0.1:8000> and log in with:

   | Email | Password |
   | --- | --- |
   | `winbert@gmail.com` | `winbert906` |

   > The app is currently launched with `php artisan serve` on port 8000. All normal pages and malformed URLs except the exact `///...///` pattern redirect to the dashboard; serving port 8000 through the included Apache vhost also handles that pattern because PHP's built-in server rejects `...` paths before Laravel sees them.

## Features mapped to the lab exercise

| Lab part | Implementation |
| --- | --- |
| Environment & DB config | Laravel 13 project, MySQL `.env`, migrations |
| Authentication | Laravel `Auth` facade with custom login/register Blade views, hashed passwords, `remember me`, throttle on POST routes, guest/auth middleware |
| Users & roles | `UserController` and `users/*` views: add users as **admin** or **staff**, edit roles and passwords, deactivate/restore accounts, and filter by role/status; inactive users cannot log in and you cannot deactivate yourself |
| Models & migrations | `User`, `Brand`, `Category`, `Product`, `InventoryTransaction`, `ActivityLog`. Products belong to a brand and a category via foreign keys; unique SKU, casts, `active`/`inactive` status scopes, `lowStock`/`outOfStock` scopes, `stockStatus()` |
| Brands & categories | First-class `brands` and `categories` tables with their own pages under **Brands** and **Categories**; removing an item marks it inactive (never deletes it), and the restore button makes it available for stock again; inactive items are hidden from new-product dropdowns but kept for products already using them |
| CRUD controller | `app\Http\Controllers\ProductController.php` (resource controller with implicit route binding) |
| Validation | `StoreProductRequest` / `UpdateProductRequest` with custom messages, SKU uniqueness on create/update, normalized uppercase SKU |
| Navigation | Dashboard (products, categories, brands at a glance), Activity Log, Inventory (stock overview), Products (CRUD + deactivate/restore), Categories (CRUD + deactivate/restore), Brands (CRUD + deactivate/restore), Users (roles + status) |
| Blade views | `layouts/app`, `layouts/guest`, dashboard, activity log, inventory, product index/create/edit/show, category/brand/user CRUD views, reusable `components/stock-badge`, shared form partials |
| Routes | `routes/web.php`: guest auth routes + auth-protected dashboard, activity log, inventory, and resource routes for products, categories, brands and users |
| Dashboard with real data | `DashboardController` computes product/category/brand counts, low/out-of-stock counts, total inventory value, recent products, category and brand summaries, chart data, and recent activity |
| Seeder | `ProductSeeder` inserts 20 realistic computer parts across 10 categories, including low-stock and out-of-stock examples |
| Theme integration | `public/css/app.css` CSS variables override Bootstrap defaults; same style on auth and app pages |
| Stock audit trail | `inventory_transactions` table logs initial stock and quantity adjustments, shown on the product detail page |
| Activity log | `activity_logs` table monitors page visits, searches and filters, sign-ins (including failed and inactive attempts), sign-outs, registrations, blocked self-deactivation, stock adjustments, and every create/update/deactivate/restore action on products, categories, brands and users; recorded by `MonitorActivity` middleware plus controllers, filterable on the **Activity Log** page and summarized on the dashboard |
| Inactive products | The delete action sets `status` to `inactive` instead of removing rows; products and inventory history stay in the database and can be restored |
| Testing | Verified login, dashboard statistics, inventory overview, product/category/brand CRUD, inactive brand/category restore, user roles, inactive-user login rejection, deactivate/restore, and activity-log filters in a browser |

## Important files

```text
app/Models/Product.php
app/Models/User.php
app/Models/Brand.php
app/Models/Category.php
app/Models/InventoryTransaction.php
app/Models/ActivityLog.php
app/Http/Controllers/ActivityLogController.php
app/Http/Controllers/ProductController.php
app/Http/Controllers/InventoryController.php
app/Http/Controllers/CategoryController.php
app/Http/Controllers/BrandController.php
app/Http/Controllers/UserController.php
app/Http/Controllers/DashboardController.php
app/Http/Controllers/Auth/LoginController.php
app/Http/Controllers/Auth/RegisterController.php
app/Http/Requests/StoreProductRequest.php
app/Http/Requests/UpdateProductRequest.php
config/inventory.php
database/migrations/*_create_brands_table.php
database/migrations/*_create_categories_table.php
database/migrations/*_create_products_table.php
database/migrations/*_create_inventory_transactions_table.php
database/migrations/*_create_activity_logs_table.php
database/seeders/BrandSeeder.php
database/seeders/CategorySeeder.php
database/seeders/ProductSeeder.php
database/seeders/UserSeeder.php
resources/views/layouts/app.blade.php
resources/views/layouts/guest.blade.php
resources/views/dashboard/index.blade.php
resources/views/activity-logs/index.blade.php
resources/views/products/*.blade.php
public/css/app.css
```

## Note on Breeze

The lab suggests Laravel Breeze with the Blade stack. This environment has no
Node/npm, which Breeze's default Tailwind/Vite build needs. The equivalent
functionality is implemented directly with Laravel's built-in authentication
facilities and custom Bootstrap/Blade views, so the same custom color scheme is
applied without a frontend build step.

## Pushing to GitHub

```powershell
git init
git add .
git commit -m "Lab 5: ByteStock computer parts inventory (Laravel MVC)"
git remote add origin <your-repository-url>
git push -u origin main
```
