# ix-api-framework

- IX-API Project: https://ix-api.net/
- IX-API V2 Schema: https://docs.ix-api.net/v2/redoc

## What is this framework?
This project provides a base template for IX-API to make it simpler and faster to implement IX-API.

The project is written in PHP using the Symfony [API Platform](https://api-platform.com/) framework.

## How does this framework work?
API Platform maps class based entities to RESTful API endpoints, this suits the IX-API schema.

All IX-API endpoints have been coded as entity classes, this means all an implementor has to do is map their internal
business logic to and from these classes to have a functioning implementation.

## What is included?
- JWT based authentication and security including assignable roles per endpoint and method
- IX-API entities for each endpoint in the schema
- Basic test suite based on Codeception, by default authentication is covered
- Swagger UI auto generated via API Platform
- Docker image for quick development setup

## Swagger UI
![alt text](public/images/example1.png)

## How to enable certain entities only?
It is possible to toggle enabled entities via the `config/packages/api_platform.yaml` config file:
```yaml
api_platform:
    mapping:
        paths:
```

## Version Support
This branch supports the V2 schema, future branches will support further versions.
