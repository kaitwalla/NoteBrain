# API Authentication for Mobile Apps

This document describes how to use the API authentication system for mobile apps.

## Overview

The API authentication system uses Laravel Sanctum to provide token-based authentication for mobile apps. This allows mobile apps to authenticate with the API without using cookies, which is more suitable for mobile environments.

## Obtaining a Token

To obtain a token, send a POST request to the `/api/login` endpoint with the following parameters:

```json
{
    "email": "user@example.com",
    "password": "password",
    "device_name": "iPhone 12"
}
```

The `device_name` parameter is used to identify the device that is using the token. This is useful for managing tokens, as users can have multiple tokens for different devices.

If the credentials are valid, the server will respond with a JSON object containing the token and the user information:

```json
{
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "email_verified_at": "2023-01-01T00:00:00.000000Z",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
    }
}
```

## Using the Token

To use the token for authenticated requests, include it in the `Authorization` header of your HTTP requests:

```
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890
```

## Protected Routes

The following routes are protected and require authentication:

- `POST /api/logout`: Revoke the current token
- `GET /api/user`: Get the authenticated user's information
- `POST /api/articles`: Create a new article
- `POST /api/articles/{article}/keep-unread`: Mark an article as unread
- `POST /api/articles/{article}/read`: Mark an article as read
- `POST /api/articles/{article}/summarize`: Summarize an article

## Revoking a Token

To revoke a token, send a POST request to the `/api/logout` endpoint with the token in the `Authorization` header. This will revoke the current token, and it will no longer be valid for authentication.

## Error Handling

If the credentials are invalid when trying to obtain a token, the server will respond with a 422 Unprocessable Entity status code and a JSON object containing the validation errors:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The provided credentials are incorrect."
        ]
    }
}
```

If a request to a protected route is made without a valid token, the server will respond with a 401 Unauthorized status code.
