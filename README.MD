# *Simple PHP REST API using bramus/router Library*

## Introduction

Welcome to this PHP REST API using the Bramus Router library and Composer as a package manager! This API serves as a starting point for building RESTful web services using PHP. The Bramus Router library is a lightweight and easy-to-use router that helps you define routes and handle requests quickly and efficiently. Composer is a popular package manager that simplifies the process of managing dependencies and third-party libraries in PHP projects. With this API, you can easily create endpoints and handle HTTP requests to retrieve or manipulate data, making it a powerful tool for building modern web applications.

## Documentation for `index.php`:

This file contains the backend code for an API. The API provides endpoints for retrieving information from and adding information to a database. The endpoints are related to a system that logs the time when employees of a company clock in and out.

### Prerequisites

- PHP 7.3 or higher
- Composer

### Libraries Used

- [bramus/router](https://github.com/bramus/router) - A simple and fast PHP router

### Installation

1. Clone this repository using `git clone https://github.com/[username]/[repository_name].git`
2. Run `composer install` in the project root directory to install the required libraries.
3. Change the values in the square brackets to your desired configuration.

### Usage

1. Navigate to the `src/` directory in the command prompt do enter `php -S localhost:8000`
3. You can now access the endpoints through `http://localhost:8000/api/[endpoint]`.
   For example, `http://localhost:8000/api/read/items/entry_logs/10` retrieves the last 10 entries in the database.
4. The allowed request method is set to any method.

### Endpoints

- `/api/read/items/entry_logs/{limit}` - Retrieves the last {limit} entries in the database.
- `/api/read/items/find_entries/{search}` - Retrieves entries in the database that match the {search} parameter.
- `/api/read/items/find_account_no/{account_no}` - Retrieves the account information of an employee based on their account number.
- `/api/create/items/new_entry/time_in` - Logs a new entry in the database for an employee that is clocking in.
- `/api/create/items/new_entry/time_out` - Logs a new entry in the database for an employee that is clocking out.
- `/api/create/items/new_personnel` - Adds a new employee to the database.

## DatabaseConnectionMYSQL class

The `DatabaseConnectionMYSQL` class provides a PDO database connection to a MySQL server. The class contains the following properties:

- `$DB_HOST`: The MySQL server host name or IP address. Default is `localhost`.
- `$DB_PORT`: The MySQL server port number. Default is `3306`.
- `$DB_USER`: The MySQL server username. Default is `root`.
- `$DB_PASS`: The MySQL server password. Default is an empty string.
- `$DB_NAME`: The name of the MySQL database to connect to. Default is `logbook_db`.

The constructor method sets up the database connection with the PDO object and stores it in the `$pdo` property. The method takes no parameters.

The `connect` method returns the `$pdo` property, allowing other classes or methods to use the same PDO object to execute SQL statements.

The `__destruct` method destroys the PDO object and sets `$pdo` to null when the object is no longer in use.

## Helper class

The `Helper` class contains helper methods that can be used throughout the application.

- `SanitizeInput`: This method takes a string or integer as input, trims any leading or trailing whitespace, encodes any special characters to HTML entities, and removes any slashes added by the `magic_quotes_gpc` setting in PHP. The method returns the sanitized input.
- `SetResponse`: This method takes an integer status code and data (a string, integer, or array) as input. It sets the HTTP response code to the given status code and returns an array containing the status code and the response data.
- `RequestBody`: This method takes an array representing the request body as input. It returns an array containing all the request body information/data. If the input array is empty, the method gets the request body from the `$_POST` global variable.
- `QueryPlaceholder`: This method takes an array and a placeholder string as input. It returns a comma-separated string containing the placeholder repeated for each value in the array. This string can be used as a placeholder in a SQL query to represent multiple values.
