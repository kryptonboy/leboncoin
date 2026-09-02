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

## API

### `GET/POST /fizzbuzz`

#### Parameters (query string for `GET`, JSON body for `POST`):

| Name    | Type    | Constraints                                         |
|---------|---------|-----------------------------------------------------|
| `int1`  | integer | must be strictly positive                           |
| `int2`  | integer | must be strictly positive and different from `int1` |
| `limit` | integer | must be strictly positive                           |
| `str1`  | string  | must not be blank                                   |
| `str2`  | string  | must not be blank                                   |

#### Example request

```bash
curl "http://localhost:8000/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz&str2=buzz"
```

#### Example response - success

```json
{
    "result": [ "1", "2", "fizz", "4", "buzz", "fizz", "7", "8", "fizz", "buzz", "11", "fizz", "13", "14", "fizzbuzz" ]
}
```

#### Example response - validation error

```json
{
  "errors": {
    "int1": "This value should be positive.",
    "str1": "This value should not be blank."
  }
}
```

#### Postman collection
A ready to use [Postman collection](postman_collection.json) is available at the root of the repository, covering both success and validation error cases.

To use it:
1. Import `postman_collection.json` into Postman
2. Set the `base_url` variable to match the one corresponding to the docker container (defaults to `http://localhost:8000`)

## CI
Every push or pull request to the `main` branch triggers a github actions workflow that builds the Docker image and runs both the unit and functional test suites. See [`.github/workflows/ci.yml`](.github/workflows/ci.yml) 

## Design decisions

This section documents choices that weren't dictated by the exercise's requirements.

### `int1` and `int2` must be different
I considered it would be better not to allow the two numbers to be the same cause they would have modified the same slots. Can be easily modified by modifying the constraints on `int2` in the [DTO](src/Dto/FizzBuzzRequest.php)

### `str1` and `str2` must not be blank
As the previous decision, this one seemed logic to me, not to have empty slots in the generated sequence

