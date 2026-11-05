<div align="center">
  <h1>🚀 Laravel 13 Admin Kit Boilerplate</h1>
  <p>A highly customized, fully responsive, and modern Admin Dashboard Boilerplate built on Laravel, Bootstrap 5, and AdminLTE v4.</p>
</div>

---

## 🌟 Overview

The **Laravel Admin Kit** is a robust, open-source boilerplate designed to save developers hours of setup time. Whether you're building a CRM, CMS, or a custom SaaS backend, this starter kit provides a rock-solid foundation with advanced features right out of the box. 

We recently migrated the entire UI architecture to **Bootstrap 5** and the latest **AdminLTE v4**, ensuring zero legacy dependencies, full mobile responsiveness, and a premium modern aesthetic.

---

## ✨ Key Features

### 🔐 Advanced Security & Auth
- **Admin Authentication:** Completely isolated `auth:admin` guard and routing to keep your backend strictly separated from frontend users.
- **Role-Based Access Control (RBAC):** Powered by `spatie/laravel-permission`. Create roles, define permissions, and assign them through a beautiful, custom-built sync interface.

### 🎨 Modern UI & UX (AdminLTE v4 + Bootstrap 5)
- **100% Responsive Design:** Beautifully scales from large desktop monitors down to mobile devices.
- **Glassmorphism & Premium Hover Effects:** Custom CSS enhancements provide a modern, eye-catching dashboard experience.
- **Custom Blade Components:** Reusable `<x-form.input>`, `<x-form.submit>`, and `<x-form.submit-delete>` components keep your views incredibly clean and maintainable.
- **Toastr & SweetAlert2 Integration:** Beautiful, non-intrusive notifications and confirmation dialogues pre-configured out of the box.

### 📊 Data Management
- **Server-Side DataTables:** Integrated with `yajra/laravel-datatables-oracle`. Handle tables with millions of rows effortlessly with built-in search, pagination, and sorting.
- **Responsive Tables:** All DataTables are wrapped in mobile-friendly scrolling containers to prevent layout breaks on small screens.

### 🛠️ Developer Tools & Utilities
- **Database Backup System:** Built-in routes and controllers (using `spatie/laravel-backup`) to allow admins to instantly download database dumps directly from the dashboard.
- **Log Viewer:** Integrated `log-viewer` to monitor application health without needing server access.
- **Extensive Helper Functions:** Custom HTML and general PHP helper files pre-loaded in `app/Helper`.

---

## 🚀 How to Utilize This Boilerplate

Stop rebuilding authentication and basic CRUD interfaces! Use this kit to jumpstart your next client project or startup idea.

### 1. Installation

Clone the repository and install dependencies:

```bash
git clone https://github.com/AR-Shahin/laravel-13-admin-kit.git
cd laravel-13-admin-kit
composer install
npm install
```

### 2. Environment Setup

Configure your `.env` file with your database credentials:

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database & Seeding

Run the migrations and seed the initial Super Admin account and roles:

```bash
php artisan migrate --seed
```

### 4. Run the Application

```bash
npm run build
php artisan serve
```

---

## 🗺️ Project Structure Guide

- **Admin Routes:** Defined cleanly in `routes/admin/web.php` and `routes/admin/auth.php`.
- **Views & UI:** The entire admin layout is located in `resources/views/admin/`.
- **Blade Components:** Explore `resources/views/components/form/` to see how easily you can build new forms.
- **Global JS/CSS:** Custom scripts and UI enhancements are located in `public/custom/js/script.js` and `resources/views/admin/includes/css.blade.php`.

---

## 🤝 Contributing (Open Source)

This project is **100% Open Source**! We welcome contributions from the community. If you find a bug, have an idea for a new feature, or want to improve the UI further:

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

Distributed under the MIT License. Feel free to use this boilerplate for personal, educational, or commercial projects!
