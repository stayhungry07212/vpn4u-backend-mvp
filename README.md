# VPN4U Backend MVP

A secure, scalable backend system for a VPN service built with Laravel, OpenVPN, and Docker.

## Prerequisites

### Core Requirements
- **Docker & Docker Compose** (v20.10+)
- **Git** (v2.30+)
- **PHP** (v8.1+)
- **Composer** (v2.0+)
- **Node.js** (v16+) & npm (v8+)

### PHP Extensions
Ensure the following PHP extensions are enabled:
- pdo_mysql
- mbstring
- openssl
- gd
- zip
- fileinfo

### Network Requirements
Make sure these ports are available on your local machine:
- 8000: Laravel API
- 8080: Admin Dashboard
- 3306: MySQL
- 6379: Redis
- 1194: OpenVPN Server (UDP)

## Quick Start

1. **Clone the repository**
   ```bash
   git clone [repository-url] vpn4u-backend
   cd vpn4u-backend
   ```

2. **Set up environment file**
   ```bash
   cp .env.example .env
   ```

3. **Build and start Docker containers**
   ```bash
   docker-compose up -d
   ```

4. **Install dependencies and set up database**
   ```bash
   docker-compose exec api composer install
   docker-compose exec api php artisan key:generate
   docker-compose exec api php artisan jwt:secret
   docker-compose exec api php artisan migrate --seed
   ```

5. **Build the admin dashboard**
   ```bash
   cd admin-dashboard
   npm install
   npm run build
   cd ..
   ```

## Project Structure

```
vpn4u-backend/
├── app/                # Laravel application code
│   ├── Http/           # Controllers and middleware
│   ├── Models/         # Database models
│   └── Services/       # Business logic services
├── docker/             # Docker configuration
│   ├── api/            # API container
│   └── vpn-server/     # OpenVPN server container
├── admin-dashboard/    # React admin interface
└── public/             # Public assets
```

## Key Features

- **User Authentication**: JWT-based secure authentication
- **Server Management**: Intelligent server selection and load balancing
- **VPN Connection**: OpenVPN integration with client configuration
- **Subscription Control**: Tier-based access and connection limiting
- **Admin Dashboard**: Real-time monitoring and management

## API Documentation

API documentation is available at:
- Local: http://localhost:8000/api/documentation
- Endpoints are also described in `resources/docs/api.md`

## Admin Dashboard

Access the admin dashboard at:
- Local: http://localhost:8080

## Testing

Run the test suite with:
```bash
docker-compose exec api php artisan test
```

## Future Enhancements

1. **Protocol Support**
   - Add WireGuard protocol implementation
   - Support for IKEv2/IPSec

2. **Security Features**
   - Implement multi-factor authentication
   - Add Perfect Forward Secrecy
   - Enhanced logging and anomaly detection

3. **Performance Optimization**
   - Server-side load metrics for better balancing
   - Geographic routing optimization
   - Connection speed testing and optimization

4. **User Experience**
   - Bandwidth usage monitoring and alerts
   - Custom DNS configurations
   - Split tunneling capabilities

5. **Infrastructure**
   - Kubernetes deployment for better scaling
   - Multi-region server deployment
   - Automated server provisioning

## License

This project is proprietary and confidential.

## Contact

For questions or inquiries, contact me at stay.hungry07212@gmail.com