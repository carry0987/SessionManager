# SessionManager
[![Packgist](https://img.shields.io/packagist/v/carry0987/session-manager.svg?style=flat-square)](https://packagist.org/packages/carry0987/session-manager)  
[![Build Status](https://travis-ci.com/carry0987/SessionManager.svg?branch=master)](https://travis-ci.com/carry0987/SessionManager)  
Modern PHP Session Management Library

## Introduction

`SessionManager` is a modern PHP session management library that provides an object-oriented wrapper around PHP's native session handling functions. It implements the `SessionHandlerInterface`, allowing for easy integration with existing projects. Additionally, it offers extra features such as session encryption and database storage handlers for scalable applications.

## Features

- Object-oriented session management.
- Protection against session fixation attacks.
- Automatic handling of session expiration.
- CSRF token generation and validation mechanisms.
- Easy integration into existing projects or frameworks.
- Support for custom session names and cookie parameters.

## Installation

You can install `SessionManager` via Composer:

```bash
composer require carry0987/session-manager
```

## Usage

Here is a basic example of how to use `SessionManager`:

```php
require 'vendor/autoload.php';

use carry0987\SessionManager\SessionManager;

// Create a SessionManager instance, you may optionally supply a custom session name and cookie parameters
$sessionManager = new SessionManager('MY_SESSION_NAME', [
    'lifetime' => 3600,           // Cookie lifetime
    'secure' => true,             // Send only over HTTPS
    'httponly' => true,           // Accessible only through the HTTP protocol
    'samesite' => 'Strict'        // Strict same-site policy
]);

// Set a session variable
$sessionManager->set('username', 'user123');

// Retrieve a session variable
$username = $sessionManager->get('username');

// Destroy the session
$sessionManager->destroy();
```

## Security Features

- Session fixation attack protection: `SessionManager` regenerates the session ID periodically.
- Session expiration mechanism: Sessions automatically expire after a period of inactivity.
- CSRF protection: Generates and validates CSRF tokens.
- Secure cookie parameters: Cookies are marked as HttpOnly and Secure by default to enhance security.

## API Reference

Here is a list of methods provided by `SessionManager`:

- `set($key, $value)`: Set a session variable.
- `get($key)`: Get a session variable.
- `exists($key)`: Check if a session variable exists.
- `remove($key)`: Remove a session variable.
- `destroy()`: Destroy the session.
- For more detailed methods and usage instructions, see the code comments.

## Contributing

If you have any suggestions for improvement or feature requests, please open an issue or submit a pull request.

## License

This project is licensed under the MIT License. See the LICENSE file for more information.
