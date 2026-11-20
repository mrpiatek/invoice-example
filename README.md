# Invoice Example

This is an example application in Laravel showcasing DDD implementation of a Invoice module.

See `./src/Modules/Invoices/`.

# Installing

`./start.sh`

# Running the container

`docker compose up -d`

> If you are using docker in rootless mode you might need to modify the `.env` file's `APP_PORT` with different value (for example `APP_PORT=8080`)

# OpenAPI

This project includes `dedoc/scramble` as an OpenAPI documentation provider. Visit http://localhost:8080/docs/api to view the docs.
