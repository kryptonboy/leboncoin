# FizzBuzz Technical Test
A REST API implementation for fizz-buzz built with Symfony 8.1 (minimal skeleton) and PHP 8.5

## Requirements
- [Docker](https://docs.docker.com/get-docker/)

## Getting started

### Build the docker image:
```bash
docker build -t fizzbuzz .
```

### Running tests
```bash
docker run --rm fizzbuzz vendor/bin/phpunit
```

## CI
Every push or pull request to the `main` branch triggers a github actions workflow that builds the Docker image and ruins the unit test suite. See [`.github/workflows/ci.yml`](.github/workflows/ci.yml) 
