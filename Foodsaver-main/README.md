# 🍽️ FoodSaver — Food Waste Management Platform

A full-stack web application connecting **donors**, **volunteers**, and **receivers** to reduce food waste. Features real-time GPS tracking, OTP-verified pickup/delivery, Razorpay payment integration, and role-based dashboards.

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Spring Boot 3.2, Java 17, MongoDB, Spring Security (JWT), WebSocket (STOMP + SockJS), Swagger/OpenAPI |
| **Frontend** | React 18, Vite 5, Tailwind CSS 3, Leaflet, STOMP.js |
| **Database** | MongoDB 7+ |
| **Payments** | Razorpay |
| **Testing** | JUnit 5 + Mockito (backend), Vitest + jsdom (frontend) |

---

## System Architecture

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│   React UI  │────▶│  REST API    │────▶│   MongoDB   │
│  (Vite 5174)│     │  :8080       │     │  :27017     │
└──────┬──────┘     └──────┬───────┘     └─────────────┘
       │                   │
       │  WebSocket        │  Razorpay
       │  (STOMP/SockJS)   │  API
       ▼                   ▼
┌──────────────┐   ┌──────────────┐
│ Live Tracking│   │  Payment     │
│ (Leaflet map)│   │  Gateway     │
└──────────────┘   └──────────────┘
```

---

## Features

### 👤 Four User Roles
- **Donor** — Upload donations, view status, share OTP for pickup
- **Volunteer** — Browse available donations, accept deliveries, share GPS location, verify pickup with OTP
- **Receiver** — Browse & request donations, verify delivery with OTP
- **Admin** — Full dashboard, manage donations/users/certificates, assign volunteers

### 🔐 Security
- JWT-based authentication & role-based authorization
- Passwords hashed with BCrypt
- OTP verification for pickup (6-digit) and delivery (4-digit)
- Input validation with `jakarta.validation` (`@Valid`, `@NotBlank`, `@Email`)
- Custom exceptions (`ResourceNotFoundException`, `InvalidOtpException`, `BadRequestException`) with `@RestControllerAdvice`
- CORS configured for localhost development

### 📍 Real-Time Tracking
- WebSocket (STOMP over SockJS) for live location sharing
- Leaflet map showing all active delivery locations
- Any authenticated user can view the tracking map
- GPS auto-share with one click (opt-in)

### 💳 Payments (Razorpay)
- Create payment orders
- Verify payment signatures
- Test mode with Razorpay test keys

### 📄 Certificates
- Receivers upload certificates for verification
- Admin approves/rejects certificates

---

## Prerequisites

- **Java 17+** (JDK)
- **Node.js 18+** and **npm**
- **MongoDB 7+** (local or Docker)
- **Razorpay test account** (for payment flow)

---

## Setup & Run

### 1. Clone & Navigate

```bash
git clone <repo-url>
cd Foodsaver-main
```

### 2. Start MongoDB

```bash
# Local install
mongod

# OR via Docker
docker run -d -p 27017:27017 --name mongodb mongo:7
```

### 3. Backend

```bash
cd foodsaver-backend

# Configure (optional defaults shown)
# Edit src/main/resources/application.properties if needed

# Get your Razorpay test keys from https://dashboard.razorpay.com
# Update in application.properties:
# razorpay.key-id=rzp_test_xxxxxxxxxxxx
# razorpay.key-secret=your_secret_here

# Build & run
mvn spring-boot:run
```

Backend starts at `http://localhost:8080`.
A default admin is auto-created: `admin@foodsaver.com` / `admin123`

### 4. Frontend

```bash
cd foodsaver-frontend

# Configure API URL (optional, defaults to http://localhost:8080)
cp .env.example .env
# Edit .env if your backend runs on a different port

# Install & run
npm install
npm run dev
```

Frontend starts at `http://localhost:5173` (or next available port).

---

## Running Tests

```bash
# Backend (12 tests)
cd foodsaver-backend
mvn test

# Frontend (6 tests)
cd foodsaver-frontend
npm test
```

---

## API Overview

| Endpoint | Method | Auth | Description |
|---|---|---|---|
| `/api/auth/login/donor` | POST | No | Donor login |
| `/api/auth/register/donor` | POST | No | Donor registration |
| `/api/auth/login/volunteer` | POST | No | Volunteer login |
| `/api/auth/register/volunteer` | POST | No | Volunteer registration |
| `/api/auth/login/receiver` | POST | No | Receiver login |
| `/api/auth/register/receiver` | POST | No | Receiver registration |
| `/api/auth/login/admin` | POST | No | Admin login |
| `/api/donations/upload` | POST | Donor | Upload donation with images |
| `/api/donations/available` | GET | Receiver | List available donations |
| `/api/donations/verify-pickup` | POST | Volunteer | Verify pickup OTP |
| `/api/donations/verify-delivery` | POST | Receiver | Verify delivery OTP |
| `/api/volunteer/accept/{id}` | POST | Volunteer | Accept a donation |
| `/api/volunteer/my-deliveries` | GET | Volunteer | View assigned deliveries |
| `/api/receiver/request/{id}` | POST | Receiver | Request a donation |
| `/api/admin/dashboard` | GET | Admin | Dashboard statistics |
| `/api/admin/accept-donation/{donationId}/{volunteerId}` | POST | Admin | Assign volunteer |
| `/api/payment/create-order` | POST | Auth | Create Razorpay order |
| `/api/payment/verify` | POST | Auth | Verify payment signature |

**WebSocket:** Connect via SockJS at `/ws`, subscribe to `/topic/location`, publish to `/app/location`.

---

## Project Structure

```
Foodsaver-main/
├── docker-compose.yml           # Docker Compose orchestration
├── foodsaver-backend/           # Spring Boot application
│   ├── Dockerfile               # Multi-stage Docker build
│   └── src/main/java/com/foodsaver/
│       ├── config/              # Security, CORS, WebSocket, Razorpay config
│       ├── controller/          # REST & WebSocket controllers
│       ├── dto/                 # Request/Response DTOs (with validation)
│       ├── exception/           # Custom exceptions + global handler
│       ├── model/               # MongoDB document entities
│       ├── repository/          # Spring Data MongoDB repositories
│       ├── security/            # JWT utilities & filter
│       └── service/             # Business logic layer
├── foodsaver-frontend/          # React application
│   ├── Dockerfile               # Multi-stage Nginx Docker build
│   └── src/
│       ├── components/          # Navbar, Footer, ProtectedRoute
│       ├── context/             # AuthContext (JWT state)
│       ├── pages/               # All page components by role
│       └── services/            # API client, WebSocket, Payment
```

---

## Environment Variables

| Variable | Default | Description |
|---|---|---|
| `VITE_API_URL` | `http://localhost:8080` | Backend API base URL |

Backend config (`application.properties`):

| Property | Default | Description |
|---|---|---|
| `server.port` | `8080` | Backend port |
| `MONGODB_URI` | `mongodb://localhost:27017/foodsaver` | MongoDB connection URI |
| `jwt.secret` | *(hardcoded)* | JWT signing secret (change in production) |
| `jwt.expiration` | `86400000` | JWT expiration (ms) |
| `razorpay.key-id` | *(placeholder)* | Razorpay key ID |
| `razorpay.key-secret` | *(placeholder)* | Razorpay key secret |

---

## Docker Compose

One-command setup using Docker Compose:

```bash
docker compose up --build
```

This starts three containers:
- **MongoDB 7** on port `27017`
- **Backend** (Spring Boot) on port `8080`
- **Frontend** (Nginx) on port `5173`

> **Note:** Replace `RAZORPAY_KEY_ID` and `RAZORPAY_KEY_SECRET` in `docker-compose.yml` with your Razorpay test keys before using payments.

---

## API Documentation (Swagger)

Once the backend is running, explore all REST endpoints interactively:

- **Swagger UI:** http://localhost:8080/swagger-ui.html
- **OpenAPI JSON:** http://localhost:8080/v3/api-docs

---

## What's Next / Future Improvements

- Email notifications (JavaMailSender)
- Pagination on list endpoints
- CI/CD with GitHub Actions
- PWA support for mobile access
- Refresh token rotation
- Production deployment configuration

---

## License

MIT
