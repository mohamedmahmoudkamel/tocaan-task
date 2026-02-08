## Quick Start with Docker

### Prerequisites

- Docker & Docker Compose
- Git
- Make

### Setup

1. **Clone** repository
   ```bash
   git clone <repository-url>
   cd tocaan-task
   ```

1. **Build containers**
   ```bash
   make build
   ```

1. **Copy environment files**
   ```bash
   make copy-env
   ```

1. **Start containers**
   ```bash
   make up
   ```

1. **Install dependencies**
   ```bash
   make composer-install
   ```


1. **Run tests**
   ```bash
   make test
   ```


### Access Points

- **API**: http://localhost:8000
- **Database**: localhost:3306
- **phpMyAdmin**: http://localhost:8080

## Development

### Accessing container
```bash
make bash
```

### Running Tests
```bash
make test
```

### Code Style
```bash
make lint
```

### Database Migrations
```bash
make migrate-fresh
```

## Adding a New Payment Gateway

### Step 1: Create Gateway Class

Create a new gateway class in `app/Gateways/`:

### Step 2: Add Payment Method Enum

Add the new payment method to `app/Enums/PaymentMethod.php`:

### Step 3: Register Gateway

Register the new gateway in `app/Providers/PaymentServiceProvider.php`:

### Step 4: Update Database Migration

Add the new payment method to the payments table:

### Step 5: Update Factory

Update `database/factories/PaymentFactory.php` to include the new method: