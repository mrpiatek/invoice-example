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

# Design decisions and reasoning

## Invoice lines can only be modified in `draft` status

## Prices represented as integers with no currency specified

In real world scenario I would imagine application-wide currency settings, regional pricing or adding a currency information to the price.

## Customers as Value Objects

In real application Customer would be have its own identity, but in this app they are value objects for simplicity.

## Flow controlled by Exceptions

For simplicity application flow is controlled by Exceptions in Invoice or InvoiceRepository but in real world I would imagine adding more methods to safely interact with it. (like InvoiceRepository::exists or Invoice::canTransition)

## HTTP Layer depends on Domain Exceptions

This is not great -- ideally there would be a layer of transforming Domain and Infrastructure Exceptions into HTTP Layer native response objects.

## HTTP Validation

Validation is split between HTTP validation and Domain validation. HTTP validation focuses on request validity and infrastructure constraints and Domain validation enforces business rules.

## InvoiceControllerTest is meant as a sanity check rather than fully-covering feature test

## Non-atomic invoice sending

The current locking mechanisms mitigates multiple requests trying to send one invoice but does not quarantee agains fast webhook callback observing an invoice still in `draft` state.
In order to prevent this we would need a new status for Invoice that would denote failed sending (and necessary transition rules) so that we could immidiately mark Invoice as `sent-to-client`, try using the Notify Provider and on failure set Invoice status to `failed-to-send` and act accordingly.

## Removed Redis from the project in effort to minimize its footprint