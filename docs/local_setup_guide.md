# Local Development Setup Guide (Windows & macOS with Laravel Herd)

This guide provides step-by-step instructions for onboarding new developers and setting up the **Bidora** application on a local development machine using **Laravel Herd**, **phpMyAdmin**, and **MySQL**.

---

## 📋 Table of Contents

1. [Tech Stack Overview](#1-tech-stack-overview)
2. [Prerequisites](#2-prerequisites)
3. [Installing & Configuring Laravel Herd on Windows](#3-installing--configuring-laravel-herd-on-windows)
4. [Setting up phpMyAdmin on Laravel Herd](#4-setting-up-phpmyadmin-on-laravel-herd)
5. [Cloning the Repository & Initializing the App](#5-cloning-the-repository--initializing-the-app)
6. [Database Setup & Importing the SQL Dump](#6-database-setup--importing-the-sql-dump)
7. [Configuring Laravel Reverb (Real-Time WebSockets)](#7-configuring-laravel-reverb-real-time-websockets)
8. [Running the Application](#8-running-the-application)
9. [Running Tests & Verifying the Setup](#9-running-tests--verifying-the-setup)
10. [Troubleshooting & FAQs](#10-troubleshooting--faqs)

---

## 1. Tech Stack Overview

| Component | Technology / Version |
| :--- | :--- |
| **Backend Framework** | Laravel 13 (PHP 8.3 or 8.4) |
| **Frontend Framework** | Vue 3 + Inertia.js v3 |
| **Styling** | Tailwind CSS v4 |
| **Database** | MySQL 8.x / MariaDB |
| **Local Web Server** | Laravel Herd |
| **Database UI** | phpMyAdmin (served via Herd) |
| **Real-time WebSockets** | Laravel Reverb + Laravel Echo |
| **Queue & Async Jobs** | Laravel Queue (`database` driver locally) |

---

## 2. Prerequisites

Ensure you have the following installed on your local machine:

1. **Git**: [Download Git for Windows](https://git-scm.com/download/win) (choose Git Bash during setup).
2. **Node.js & npm**: [Download Node.js LTS (v20 or v22)](https://nodejs.org/).
3. **Laravel Herd**: [Download Laravel Herd for Windows](https://herd.laravel.com/windows).

---

## 3. Installing & Configuring Laravel Herd on Windows

Laravel Herd is a zero-dependency PHP development environment that bundles PHP, Composer, and Nginx.

### Step 3.1: Install Laravel Herd
1. Run the downloaded Herd installer for Windows (`Herd-Setup.exe`).
2. Follow the on-screen instructions. Herd will automatically install Composer and configure PHP.
3. During setup, choose your preferred PHP version: **PHP 8.3** or **PHP 8.4**.

### Step 3.2: Verify Herd Parked Directory
Herd serves sites from designated "parked" directories. By default on Windows, this is usually:
```text
C:\Users\<YourUsername>\Herd
```
- Open the Herd application window from your Windows system tray.
- Navigate to **General / Sites** settings to confirm or add your development directory (e.g. `C:\Users\<YourUsername>\Herd`).
- Any subfolder created in this parked directory is automatically accessible in your browser at `http://<folder-name>.test` (e.g. `http://bidora.test`).

### Step 3.3: Set up MySQL Database Service
You need a running MySQL server on port `3306`:
- **If using Herd Pro**: Enable the **MySQL / MariaDB** service directly in the Herd Services tab.
- **If using Herd Free**: Ensure you have a local MySQL server running (e.g., MySQL Community Server, DBngin, XAMPP MySQL, or Docker MySQL) listening on `127.0.0.1:3306`.

---

## 4. Setting up phpMyAdmin on Laravel Herd

You can serve phpMyAdmin directly through Laravel Herd as a local site.

### Step 4.1: Download phpMyAdmin
1. Visit the official phpMyAdmin download page: [phpmyadmin.net/downloads](https://www.phpmyadmin.net/downloads/).
2. Download the latest **all-languages.zip** (e.g. `phpMyAdmin-5.2.x-all-languages.zip`).

### Step 4.2: Extract to your Herd Directory
1. Open your Herd parked directory (e.g. `C:\Users\<YourUsername>\Herd`).
2. Extract the contents of the ZIP file directly into this directory.
3. Rename the extracted folder to simply:
   ```text
   phpmyadmin
   ```
4. Full path should look like: `C:\Users\<YourUsername>\Herd\phpmyadmin`.

### Step 4.3: Configure phpMyAdmin
1. Open the `phpmyadmin` folder.
2. Duplicate `config.sample.inc.php` and rename the copy to `config.inc.php`.
3. Open `config.inc.php` in a text editor (e.g. VS Code, Notepad).
4. Generate a 32-character random string and set the `blowfish_secret`:
   ```php
   $cfg['blowfish_secret'] = '32_character_random_secret_string_here_12345';
   ```
5. Confirm server connection settings (usually default is sufficient):
   ```php
   $cfg['Servers'][$i]['host'] = '127.0.0.1';
   $cfg['Servers'][$i]['port'] = '3306';
   $cfg['Servers'][$i]['AllowNoPassword'] = true;
   ```
6. Save and close the file.

### Step 4.4: Increase PHP Upload Limits for Database Import
To prevent "File size limit exceeded" errors when importing large database `.sql` files:
1. Click the Herd icon in your Windows system tray.
2. Go to **Settings** > **PHP** > Click **Open php.ini**.
3. Search for and update the following directives:
   ```ini
   upload_max_filesize = 128M
   post_max_size = 128M
   memory_limit = 512M
   max_execution_time = 300
   ```
4. Save the file and restart PHP services from the Herd tray menu.

### Step 4.5: Open phpMyAdmin
Open your browser and navigate to:
```text
http://phpmyadmin.test
```
Log in with your MySQL credentials:
- **Server**: `127.0.0.1`
- **Username**: `root`
- **Password**: *(Leave empty or enter your MySQL root password)*

---

## 5. Cloning the Repository & Initializing the App

### Step 5.1: Clone into your Herd Directory
Open Git Bash or your terminal and clone the repository directly inside your Herd directory:

```bash
cd /c/Users/<YourUsername>/Herd
git clone https://github.com/<your-org-or-user>/bidora.git
cd bidora
```

### Step 5.2: Create the Local `.env` File
Copy the example environment configuration:

```bash
# In Git Bash / WSL / macOS
cp .env.example .env

# Or in Windows PowerShell / Command Prompt
copy .env.example .env
```

### Step 5.3: Install Composer Dependencies
```bash
composer install
```

### Step 5.4: Generate Application Key
```bash
php artisan key:generate
```

### Step 5.5: Install Node Dependencies
```bash
npm install
```

### Step 5.6: Create Storage Symlink
Link the public storage directory so uploaded images and assets display correctly:
```bash
php artisan storage:link
```

---

## 6. Database Setup & Importing the SQL Dump

### Step 6.1: Create the Database in phpMyAdmin
1. Open `http://phpmyadmin.test` in your browser.
2. Click **New** in the left navigation sidebar.
3. Database name: `bidora`
4. Collation: `utf8mb4_unicode_ci`
5. Click **Create**.

### Step 6.2: Import the Provided `.sql` Dump File
1. In phpMyAdmin, click on the newly created **`bidora`** database from the left sidebar.
2. Click the **Import** tab in the top navigation bar.
3. In the **File to import** section, click **Browse** (or **Choose File**) and select the provided local database `.sql` dump file.
4. Leave other options as default (Format: SQL).
5. Scroll to the bottom and click **Import**.
6. Wait for the green success message indicating all tables and rows have been imported.

> **Tip (Command Line alternative):**  
> If you prefer CLI or have a very large dump file, you can import directly:
> ```bash
> mysql -u root -p bidora < path/to/local_database_dump.sql
> ```

### Step 6.3: Configure `.env` Database Credentials
Open `.env` in the `bidora` project root and set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bidora
DB_USERNAME=root
DB_PASSWORD=
```
*(Update `DB_PASSWORD` if your MySQL `root` user has a password).*

### Step 6.4: Run Pending Migrations (Optional)
If there are any new migrations since the dump was created:
```bash
php artisan migrate
```

---

## 7. Configuring Laravel Reverb (Real-Time WebSockets)

Bidora uses **Laravel Reverb** for real-time live auction bidding, countdown timers, and notifications.

Ensure your `.env` contains the local Reverb and Broadcast settings:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=local-reverb-id
REVERB_APP_KEY=local-reverb-key
REVERB_APP_SECRET=local-reverb-secret

REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

> [!NOTE]
> For local development, `REVERB_SCHEME` is set to `http` (port `8080`).

---

## 8. Running the Application

Because the project is in your Herd folder, Herd automatically handles serving the web application at **`http://bidora.test`** (or `https://bidora.test`).

To support Hot Module Replacement (Vite), Background Queues, and Real-Time WebSockets, keep the following processes running during development.

### Method A: Separate Terminals (Recommended)

Open 3 terminal tabs in the `bidora` directory:

#### Terminal 1: Vite (Frontend Build & Hot Reloading)
```bash
npm run dev
```

#### Terminal 2: Queue Worker (Background Jobs & Points Processing)
```bash
php artisan queue:listen
```

#### Terminal 3: Reverb Server (WebSocket Engine for Live Bids)
```bash
php artisan reverb:start
```

---

### Method B: Single Command (Concurrently)

You can run the built-in development script:
```bash
composer run dev
```
And in a second terminal, start Reverb:
```bash
php artisan reverb:start
```

---

## 9. Running Tests & Verifying the Setup

### Step 9.1: Access the Application
1. Open your browser and navigate to:
   ```text
   http://bidora.test
   ```
2. Log in using test credentials from the imported database or register a new test user.

### Step 9.2: Verify WebSocket Connection
1. Open Browser DevTools (F12) > **Network** tab.
2. Filter by **WS** (WebSockets).
3. Reload the page and confirm you see an active connection to `ws://127.0.0.1:8080/app/...` with status `101 Switching Protocols`.

### Step 9.3: Run Automated Tests
Run the test suite to ensure everything is functioning correctly:
```bash
php artisan test
```

---

## 10. Troubleshooting & FAQs

### 1. `phpmyadmin.test` or `bidora.test` gives 404 / Not Found
- Ensure the project folder resides directly inside your Herd Parked Paths (e.g. `C:\Users\<User>\Herd\bidora`).
- Check Herd tray icon > **Sites** to ensure `bidora` and `phpmyadmin` are listed.

### 2. Database Connection Refused (`SQLSTATE[HY000] [2002]`)
- Confirm your MySQL server is running and listening on port `3306`.
- If using `localhost` in `.env`, try changing it to `127.0.0.1`.

### 3. phpMyAdmin Import Error: "413 Request Entity Too Large" or "Max File Size Exceeded"
- Open your `php.ini` file via Herd tray settings.
- Ensure `upload_max_filesize = 128M` and `post_max_size = 128M`.
- Save and restart PHP from Herd.

### 4. WebSocket / Reverb Connection Failed
- Ensure `php artisan reverb:start` is running in an active terminal.
- Verify `VITE_REVERB_HOST=127.0.0.1` and `VITE_REVERB_PORT=8080` in `.env`.
- If you modified `.env`, stop and restart `npm run dev`.

### 5. Windows Tailwind / Rollup Native Binary Warnings
- If Vite complains about optional binaries on Windows, run:
  ```bash
  npm install --force
  ```
