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

The project uses a multi-stage `Dockerfile` with three targets:

| Target    | Description                                                                                                                                        |
|-----------|----------------------------------------------------------------------------------------------------------------------------------------------------|
| `dev`     | Used for local development,code is mounted as a volume to avoid rebuilding after each modification on the codebase, dependencies include dev tools |
| `builder` | Used by the CI, code is copied into the image, dependencies include dev tools                                                                      |
| `runtime` | Used for production environment, minimal image, no dev dependencies nor compilation toolchain                                                      |

### Build the docker image:

For local development (recommended — enables live code reload via volume):
```bash
docker compose up --build
```

To build a specific target manually:
```bash
docker build --target dev -t fizzbuzz:dev .
docker build --target runtime -t fizzbuzz:prod .
```

### Running tests
```bash
docker run --rm fizzbuzz:dev vendor/bin/phpunit
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

### `GET /statistics`

Returns the parameters and hit count of the most frequently requested `/fizzbuzz` combination. Accepts no parameters.

#### Example response - no data recorded yet

```json
{
    "parameters": null,
    "hits": 0
}
```

### Example response - with data

```json
{
    "parameters": {
        "int1": 3,
        "int2": 5,
        "limit": 15,
        "str1": "fizz",
        "str2": "buzz"
    },
    "hits": 23
}
```

### Postman collection
A ready to use [Postman collection](postman_collection.json) is available at the root of the repository, covering both success and validation error cases. It also provides `/statistics` scenarios 

To use it:
1. Import `postman_collection.json` into Postman
2. Set the `base_url` variable to match the one corresponding to the docker container (defaults to `http://localhost:8000`)

## CI
Every push or pull request to the `main` branch triggers a github actions workflow (See [`.github/workflows/ci.yml`](.github/workflows/ci.yml)) that:
- builds the Docker image 
- runs the unit tests suite
- runs the functional tests suite
- runs PHPStan Level 8
- runs PHP-cs-fixer

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

### Redis service in CI
The functional test suite needs a real Redis instance to run against. GitHub Actions [service containers](https://docs.github.com/en/actions/tutorials/use-containerized-services/create-redis-service-containers) provide this: Redis is started alongside the job and reachable via `localhost` on the runner, torn down automatically once the job completes.

### Multi-stage Docker build
The `Dockerfile` is split into a shared `base` stage (PHP extensions, Composer) and three targets: `dev` (local development, code mounted via volume), `builder` (used by CI, dependencies + code baked in), and `runtime` (deployment-ready, dependencies pruned via `--no-dev`, no compilation toolchain). This keeps the deployable image lean: **810 MB → 201 MB** (~75% reduction) compared to a single-stage build with the full compilation toolchain and dev dependencies included.
