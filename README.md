# FizzBuzz Technical Test
A REST API implementation for fizz-buzz built with Symfony 8.1 (minimal skeleton) and PHP 8.5

## Requirements

### Using docker (recommended)
- [Docker](https://docs.docker.com/get-docker/)

### Without docker
- PHP 8.5 or later
- A local Redis server
  - **Linux / macOS**: install via your package manager (`apt install redis-server`, `brew install redis`, ...)
  - **Windows**: [WSL2](https://learn.microsoft.com/windows/wsl/install) + `sudo apt install redis-server`
- Verify `Redis` is running: `redis-cli ping` should return `PONG` (if `redis-cli` doesn't exist, you might have to install `redis-tools` through your package manager)
- The PHP `redis` extension (`ext-redis`) installed and enabled:
  - **Linux / macOS**: `pecl install redis`
  - **Windows**: 
    - [Download](https://downloads.php.net/~windows/pecl/releases/redis/6.3.0/) the archive matching (retrievable in PHPinfo):
      - Your PHP version
      - The architecture (x64 or x86)
      - The Thread Safe configuration (Thread Safety enabled => TS, else => NTS)
      - For instance, with PHP version 8.5 , x64 architecture and Thread Safety enabled, you should download php_redis-6.3.0-**8.5**-**ts**-vs17-**x64**.zip
    - Extract and copy `php_redis.dll` into your PHP `ext/` folder
    - Add to your `php.ini` and restart PHP:
```ini
extension=redis
```

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

### `str1` and `str2` are compared case-sensitively for statistics grouping
Two requests with `str1=fizz` and `str1=Fizz` as only difference are tracked as distinct entries since they are generating different sequences

### Tie in statistics
In case two search parameters have the same hit number, I decided to break the tie by displaying the first one that reached that hit score. The other possibility was to show all the tied parameters, but only one result was expected according to the settlement.
This is tracked by storing a last-hit timestamp per combination, only compared among tied entries when a tie actually occurs.

### Statistics storage
Statistics are currently stored in Redis. Other backends could be added (a SQL database, for instance) by implementing [`StatisticsTrackerInterface`](src/Service/StatisticsTracker/StatisticsTrackerInterface.php) and updating the service binding in [`services.yaml`](config/services.yaml).
