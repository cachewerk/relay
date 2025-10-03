#!/usr/bin/env bash

set -e

if [ $# -ne 1 ]; then
    echo "Usage: $0 <version>"
    exit 1
fi

VERSION="$1"

URL="https://builds.r2.relay.so/${VERSION}/relay.stub.php"

echo "Downloading ${VERSION} stubs..."
curl -s -o "relay.stub.php" "$URL"

if [[ "$OSTYPE" == "darwin"* ]]; then
    SED="gsed"
else
    SED="sed"
fi

echo "Adjusting stubs..."

"$SED" -i -E \
    -e 's/^(.*\bfunction\b.*);/\1 {}/' \
    -e 's/^(\s*)\);$/\1) {}/'\
    -e 's/^(\s*)\): ([^;]+);$/\1): \2 {}/' \
    -e 's/^(\s+\*) @alias (.+)$/\1 @see \2()/' \
    -e 's/^(\s+\*) @var \$context array/\1 @example $context array/' \
    -e '/#\[\\SensitiveParameter\]$/N;s/#\[\\SensitiveParameter\]\s*\n\s*/#[\\SensitiveParameter] /' \
    relay.stub.php
