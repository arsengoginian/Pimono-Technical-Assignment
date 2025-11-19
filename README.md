# Pimono Technical Assignment

A simplified **digital wallet application** built with **Laravel 12** (backend) and **Vue 3 + Vite** (frontend), featuring real-time money transfers, transaction history, and user balance updates.

This project demonstrates **Full Stack skills**, including:

- REST API design with Laravel
- Stateless token authentication with **Laravel Sanctum**
- Real-time event broadcasting via **Pusher**
- Frontend using **Vue 3 Composition API** and **Tailwind CSS**
- Handling concurrency and scalable balance management

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Project](#running-the-project)

---

## Features

- User registration and login with **stateless token authentication**
- Dashboard with:
    - User profile info
    - Current balance display
    - Transaction history table
    - Money transfer form
- Real-time updates of balance and transactions using **Pusher + Laravel Echo**
- Secure private channels for each user
- Input validation and error handling
- Scalable balance management

---

## Requirements

- PHP >= 8.2
- Laravel 12
- Node.js >= 18
- npm
- MySQL or PostgreSQL
- Pusher account for real-time events

---

## Installation

1. Clone the repository:
```bash
git clone https://github.com/arsengoginian/Pimono-Technical-Assignment.git
cd Pimono-Technical-Assignment
```

2. Install backend dependencies:
```bash
composer install
```

3. Install frontend dependencies:

```bash
npm install
```

## Configuration

1. Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

2. Generate app key:

```bash
php artisan key:generate
```

3. Configure .env for database and Pusher:

```bash
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pimono
DB_USERNAME=root
DB_PASSWORD=

# Pusher
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=YOUR_APP_ID
PUSHER_APP_KEY=YOUR_APP_KEY
PUSHER_APP_SECRET=YOUR_APP_SECRET
PUSHER_APP_CLUSTER=YOUR_CLUSTER

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost
```
4. Configure frontend `.env` (Vite):
```bash
VITE_PUSHER_APP_KEY=YOUR_APP_KEY
VITE_PUSHER_APP_CLUSTER=YOUR_CLUSTER
```

## Database

Run migrations and seed sample users:
```bash
php artisan migrate --seed
```

## Running the Project

1. Start Laravel backend:
```bash
php artisan serve
```
2. Start Vite frontend:
```bash
npm run dev
```
3. Open your browser at http://127.0.0.1:5173 (Vite dev server)
