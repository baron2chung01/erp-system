# Laravel ERP System

This repository contains a Laravel-based ERP system that serves as a backend for managing various aspects of a business. The system includes several modules and integrates with a mobile attendance app.

## Modules

The following modules are included in the ERP system:

- Employee: Manage employee data, including profiles, roles, and attendance records.
- Role: Define and assign roles to employees.
- Supplier: Manage supplier information, such as contact details and products/services provided.
- Subcontractor: Track subcontractor details, agreements, and performance.
- Client: Manage client information, including contact details and project history.
- Attendance: Record and track employee attendance using a mobile app.
- Outline Agreement: Create and manage outline agreements with clients or suppliers. This module allows you to associate clients, purchase orders, group tasks, and locations with each outline agreement.
- Purchase Order: Generate and track purchase orders for procurement. Generate exported pdf of Quotation, Supplier/Subcontractor PO, Invoice, Receipt.
- Location: Manage locations, such as warehouses or office branches.
- Work Instruction: Create and assign work instructions to employees.
- Work Order: Track and manage work orders for various projects.

## Integration with Mobile Attendance App

The ERP system seamlessly integrates with a mobile attendance app, allowing employees to clock in and out of work using their smartphones. The attendance records are automatically synced with the ERP system, providing real-time data for attendance management.

## Getting Started

To set up and run the Laravel ERP system locally, follow these steps:

1. Clone this repository to your local machine.
2. Install the required dependencies by running `composer install`.
3. Configure the database connection in the `.env` file.
4. Customize migration files based on your nedds and run the database migrations  using `php artisan migrate`.
5. Customize seed files based on your nedds and seed the database with initial data using `php artisan db:seed`.
6. Start the development server with `php artisan serve`.

## Contributing

Contributions are welcome! If you'd like to contribute to the Laravel ERP system, please follow these guidelines:

1. Fork this repository.
2. Create a new branch for your feature or bug fix.
3. Commit your changes and push the branch to your fork.
4. Submit a pull request, describing your changes and their purpose.

## License

This Laravel ERP system is open-source and distributed under the MIT License.

## Contact

If you have any questions or suggestions regarding this project, please feel free to post on this repository's issues.
