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

   > Port 8000 is currently served by an Apache virtual host pointing to `public/`. It intercepts malformed URL combinations (for example `///...///`) and returns the dashboard. `php artisan serve` works for normal pages, but PHP's built-in server rejects `...` paths before Laravel can redirect them.

## Features mapped to the lab exercise

| Lab part | Implementation |
| --- | --- |
| Environment & DB config | Laravel 13 project, MySQL `.env`, migrations |
| Authentication | Laravel `Auth` facade with custom login/register Blade views, hashed passwords, `remember me`, throttle on POST routes, guest/auth middleware |
| Staff accounts | `StaffController` and `staff/*` views let you add, edit, and remove staff users from the **Staff** page; passwords are hashed and your own account cannot be removed |
| Models & migrations | `User`, `Brand`, `Category`, `Product`, `InventoryTransaction`. Products belong to a brand and a category via foreign keys; unique SKU, casts, `active`/`inactive` status scopes, `lowStock`/`outOfStock` scopes, `stockStatus()` |
| Brands & categories | First-class `brands` and `categories` tables with seeders; selectable on product create/edit and filterable on the inventory page |
| CRUD controller | `app\Http\Controllers\ProductController.php` (resource controller with implicit route binding) |
| Validation | `StoreProductRequest` / `UpdateProductRequest` with custom messages, SKU uniqueness on create/update, normalized uppercase SKU |
| Blade views | `layouts/app`, `layouts/guest`, dashboard, product index/create/edit/show, reusable `components/stock-badge`, shared product form |
| Routes | `routes/web.php`: guest auth routes + auth-protected dashboard and `Route::resource('products', ...)` |
| Dashboard with real data | `DashboardController` computes product count, low/out-of-stock counts, total inventory value, recent products, category totals, and chart data |
| Seeder | `ProductSeeder` inserts 20 realistic computer parts across 10 categories, including low-stock and out-of-stock examples |
| Theme integration | `public/css/app.css` CSS variables override Bootstrap defaults; same style on auth and app pages |
| Stock audit trail | `inventory_transactions` table logs initial stock and quantity adjustments, shown on the product detail page |
| Inactive products | The delete action sets `status` to `inactive` instead of removing rows; products and inventory history stay in the database and can be restored |
| Testing | Verified login, dashboard statistics, inventory list, create + validation, edit, transaction logging, and deactivate/restore in a browser |

## Important files

```text
app/Models/Product.php
app/Models/Brand.php
app/Models/Category.php
app/Models/InventoryTransaction.php
app/Http/Controllers/ProductController.php
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
database/seeders/BrandSeeder.php
database/seeders/CategorySeeder.php
database/seeders/ProductSeeder.php
database/seeders/UserSeeder.php
resources/views/layouts/app.blade.php
resources/views/layouts/guest.blade.php
resources/views/dashboard/index.blade.php
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
