<?php

$relay = new Relay\Relay(
    getenv('REDIS_HOST') ?: '127.0.0.1'
);

var_dump(
    $relay,
    $relay->set('foo', 'bar'),
    $relay->get('foo'),
    $relay->get('foo'),
);
