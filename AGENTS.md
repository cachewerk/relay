When bumping Relay versions, check these version-string locations first:

- `/home/runner/work/relay/relay/.github/workflows/docker.yml` for workflow matrix and `RELAY_VERSION`
- `/home/runner/work/relay/relay/docker/**/*.Dockerfile` for Docker build arguments

Important: `/home/runner/work/relay/relay/docker/pie/pie-pinned.Dockerfile` uses bare semver (`0.50.0`), while the other Dockerfiles and the workflow use the `v`-prefixed form (`v0.50.0`).
