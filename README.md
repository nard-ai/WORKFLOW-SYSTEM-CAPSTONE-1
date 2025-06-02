# A-Sync Workflow System

A comprehensive workflow management system built for Lyceum of Alabang, designed to streamline and automate approval processes for various types of requests.

## Features

- **Dynamic Request Management**
  - IOM (Inter-Office Memorandum) Processing
  - Leave Request Management
  - Multi-level Approval Workflow

- **User-Friendly Interface**
  - Modern, responsive design
  - Dark mode support
  - Intuitive navigation
  - Real-time updates

- **Advanced Approval System**
  - Digital signature integration
  - Multiple signature styles
  - Required comments for rejections
  - Batch approval capabilities

- **Dashboard & Analytics**
  - Request status tracking
  - Processing time metrics
  - Department-wise statistics
  - Priority request handling

## Technology Stack

- **Backend Framework**: Laravel
- **Frontend**: 
  - Blade Templates
  - TailwindCSS
  - JavaScript
- **Database**: MySQL
- **Server Requirements**: 
  - PHP >= 8.1
  - Composer
  - Node.js & NPM

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
cd capstone1-workflow
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Create and configure your environment file:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure your database in the `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run database migrations:
```bash
php artisan migrate
```

7. Build assets:
```bash
npm run dev
```

8. Start the development server:
```bash
php artisan serve
```

## Usage

1. Access the system through your web browser
2. Log in with your credentials
3. Navigate through the dashboard to:
   - Submit new requests
   - Review pending approvals
   - Track request status
   - Manage department settings

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Lyceum of Alabang for the opportunity to develop this system
- All contributors and testers who helped improve the system
- The open-source community for providing excellent tools and frameworks
