# Kitchen Recipe Management System
A production-ready recipe management system for large kitchens, built with Laravel 11.

## Features
- **Recipe Management**: Create, edit, and version recipes.
- **Portion Control**: Automatic scaling of ingredients and costs.
- **Production Planning**: Plan daily production, generate preparation sheets (print-ready).
- **Ingredient Management**: Track ingredients and costs.
- **Excel Integration**: Bulk import/export recipes with error handling.
- **Google Drive Integration**: Attach## 🚀 Key Features

### 1. Recipe Management
- **Detailed Costing**: Auto-calculation of cost per portion based on ingredients.
- **Allergens**: Auto-aggregation of allergens from ingredients.
- **Versioning**: Automatic version snapshots whenever a recipe is edited.

### 2. Excel Integration
- **Import**: Bulk upload recipes using the provided template. Includes validation and duplicate detection.
- **Export**: Download full recipe lists or a specific **Cost Summary** report.

### 3. Kitchen Production
- **Daily Plans**: Create production sheets for specific dates.
- **Auto-Scaling**: Enter portions to automatically calculate total ingredients needed.
- **Tasks**: Assign prep tasks to staff (e.g., "Alice -> Chop Onions").
- **Print View**: One-click ink-friendly print mode for kitchen clipboards.

### 4. Drive Integration
- **Attachments**: Link Google Drive files (PDFs, Images) to Recipes, Ingredients, or Production Days.
- **In-App Preview**: View attached files instantly in a modal without leaving the app.

### 5. Security & Roles
- **Admin**: Full access + Approve Recipes + View Audit Logs.
- **Manager**: Manage Production + Edit Recipes.
- **Staff**: View Recipes + Create Drafts + Complete Assigned Tasks.

## 🛠 Installation

1. **Clone & Setup**:
   ```bash
   git clone <repo>
   cd Kitchen_management
   cp .env.example .env
   composer install
   npm install && npm run build
   ```

2. **Database**:
   ```bash
   php artisan migrate --seed
   ```

3. **Run**:
   ```bash
   php artisan serve
   ```

## 📝 Deployment
A `.env.production` template is included. Ensure you set `APP_ENV=production` and `APP_DEBUG=false` on your live server.
migrate --seed` (Seeds default Admin/Manager users).
6. Run `php artisan serve`.

## Default Users
- **Admin**: admin@kitchen.com / password
- **Manager**: manager@kitchen.com / password
- **Staff**: staff@kitchen.com / password

## Tech Stack
- Laravel 11 (PHP 8.2+)
- MySQL 8.0
- Blade Templates (No React/Vue)
- Vanilla CSS (Premium Design)
- Maatwebsite/Excel

## Project Structure
- `app/Services`: Core business logic.
- `app/Observers`: Audit and Versioning logic.
- `app/Policies`: Authorization rules.
- `app/Enums`: Type definitions (Roles, Status, Units).
