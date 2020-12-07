# Payments Prototype

This is a prototype for a payments service. Included here are endpoints for creating and deleting users and payments.

## Installation

To install this project simply clone this repository and run the following command

```bash
cp .env.example .env
docker-compose up -d
./composer.sh install
```

Then run the migrate command

```bash
./artisan.sh migrate
```

## Static Analysis

This project uses a few static analysis tools. Run the following command to execute them

```bash
./composer.sh static-analyze
```

## Testing

To run the tests, simply execute the following command

```bash
./phpunit.sh
```

## License

this is project is available under the [MIT license](https://opensource.org/licenses/MIT).
