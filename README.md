# Masar

Masar is a self-hosted e-learning platform built for a **single instructor** running multiple courses, designed for contexts where students often don't have access to international payment cards.

Instead of integrating a payment gateway, Masar uses a **manual verification flow**: students transfer payment via CCP (postal account) and upload a photo of their receipt. The teacher reviews each submission from a dashboard and approves or rejects it before the student gets access to course content.

## Features

- **Public catalogue** — home page, course listing with filters, course detail pages, teacher profile
- **Manual payment verification** — CCP transfer + receipt upload, with a pending → approved/rejected lifecycle and resubmission on rejection
- **Teacher dashboard** — create/manage courses and modules/lessons, review pending payments, manage enrolled students, profile/security/payment settings
- **Student dashboard** — payment history with view/edit/resubmit; enrolled courses and module/lesson browser in progress
- **Multi-language** — English, French, and Arabic, with full RTL layout support
- **Two-factor authentication** — TOTP-based 2FA with recovery codes

## Tech stack

- **Backend:** PHP / [Laravel](https://laravel.com)
- **Database:** MySQL (via Eloquent ORM)
- **Frontend:** Blade templates, vanilla CSS (no framework), vanilla JS for the language switcher and interactivity
- **Auth:** Laravel's built-in auth, extended with TOTP-based 2FA

## Project structure

```
app/
  Models/            Eloquent models (User, Course, Module, Lesson, Enrollment, Payment, ...)
  Http/Controllers/  Public, Teacher, and Student controllers
resources/
  views/
    public/          Landing pages (home, courses, course detail, about, register)
    teacher/          Teacher dashboard views
    student/          Student dashboard views
  css/                Shared stylesheets (one per side: public / teacher / student)
  lang/               Translation files (en / fr / ar)
routes/
  web.php
database/
  migrations/
```

## Getting started

### Requirements
- PHP >= 8.1
- Composer
- MySQL (or another Laravel-supported database)
- Node.js & npm (only if building frontend assets via Vite — check whether your setup actually uses it)

### Setup

```bash
# 1. Clone the repo
git clone https://github.com/<your-username>/masar.git
cd masar

# 2. Install PHP dependencies
composer install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env
DB_DATABASE=masar
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migrations
php artisan migrate

# 6. (Optional) Install & build frontend assets, if using Vite
npm install
npm run dev

# 7. Serve the app
php artisan serve
```

The app will be available at `https://masar-1be6.onrender.com/`.

## Payment verification flow

1. Student registers and selects a course.
2. Student is shown the teacher's CCP account details and uploads a photo of the payment receipt.
3. The enrollment is created with status `pending`.
4. The teacher reviews the receipt from their dashboard and marks it `approved` or `rejected` (with an optional note).
5. If rejected, the student can re-upload a new receipt, resetting the enrollment to `pending`.
6. Once `approved`, the student gets full access to the course's modules and lessons.


## Author

Built by Kryovelle as a portfolio project.
