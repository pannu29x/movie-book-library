
# Movie & Book Library Management System

A web-based application to manage a personal collection of movies and books. Users can add, view, edit, delete, search, filter, and organize their library items.

## Project Overview

The Movie & Book Library Management System is developed using PHP, PostgreSQL, HTML, and CSS. It provides a simple and user-friendly interface for maintaining a personal collection of movies and books.

## Technologies Used

- Frontend: HTML5, CSS3
- Backend: PHP
- Database: PostgreSQL
- Local Server: XAMPP
- Version Control: Git and GitHub

## Features

- Add new movies and books
- Edit existing library items
- Delete library items
- Search items by title
- Filter by item type and genre
- Sort by rating, title, and recently added
- Pagination for library records
- Dashboard statistics for total items, movies, and books
- CSRF protection for form submissions
- PostgreSQL database integration

## Project Structure

```text
movie_library/
├── index.php
├── add.php
├── edit.php
├── delete.php
├── db.php
├── csrf.php
├── style.css
├── database.sql
├── .gitignore
└── README.md
```

Note: `config.local.php` contains local database credentials and is intentionally excluded from GitHub.

## Database Setup

1. Install PostgreSQL.
2. Create a database named `movie_book_library`.
3. Open pgAdmin and execute the SQL statements in `database.sql`.
4. Create a local `config.local.php` file with your PostgreSQL connection details.
5. Make sure PHP's `pdo_pgsql` extension is enabled.

## Run the Project

1. Install XAMPP and start Apache.
2. Place the project folder inside the XAMPP `htdocs` directory.
3. Configure PostgreSQL credentials in `config.local.php`.
4. Open the following URL in your browser:

   http://localhost/movie_library/

## Security

- Prepared statements are used for database queries.
- CSRF tokens protect form submissions.
- Local database credentials are excluded from version control.

## Author

Pranay Raut

## License

This project is available for educational and learning purposes.