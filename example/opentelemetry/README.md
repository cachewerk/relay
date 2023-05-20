# Example for Relay OpenTelemetry instrumentation

This example demonstrates how to monitor Redis using [OpenTelemetry](https://opentelemetry.io/) and
[Uptrace](https://uptrace.dev/get/open-source-apm.html. It requires Docker to start Redis Server and Uptrace.

**Step 1**. Download the example using Git:

```shell
git clone https://github.com/cachewerk/relay.git
cd example/opentelemetry
```

**Step 2**. Start the services using Docker:

```shell
docker-compose up -d
```

**Step 3**. Make sure Redis and Uptrace are running:

```shell
docker-compose logs redis
docker-compose logs uptrace
```

**Step 4**. Install dependencies and run the Relay example:

```shell
composer install
php main.php
```

**Step 5**. Follow the link from the CLI to view the trace:

```shell
php main.php
trace: http://localhost:14318/traces/ee029d8782242c8ed38b16d961093b35
```

![Relay trace](./image/relay-trace.png)

You can also open Uptrace UI at [http://localhost:14318](http://localhost:14318) to view available
spans, logs, and metrics.

See [Monitoring Relay Redis client with OpenTelemetry](https://uptrace.dev/blog/posts/relay-cache-opentelemetry.html).
