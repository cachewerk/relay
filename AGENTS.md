When bumping Relay versions, check these version-string locations first:

- `/home/runner/work/relay/relay/.github/workflows/docker.yml` for workflow matrix and `RELAY_VERSION`
- `/home/runner/work/relay/relay/docker/**/*.Dockerfile` for Docker build arguments
