# ix-api-framework

- IX-API Project: https://ix-api.net/
- IX-API V2 Schema: https://docs.ix-api.net/v2/redoc

## What is this framework?
The aim of this project is to provide a base implementation template for the IX-API, this should make it simpler and 
faster for Internet Exchanges to implement IX-API.

The project is coded in PHP using [API Platform](https://api-platform.com/) framework, which is written in Symfony.

## How does this framework work?
API Platform maps class based entities to RESTful API endpoints, this works very well for the IX-API schema.

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

## Version Support
This branch supports the V2 schema, future branches will support further versions.
