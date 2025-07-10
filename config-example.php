<?php

$DATABASE_HOST = getenv('DB_HOST') ?: 'localhost';
$DATABASE_USERNAME = getenv('DB_USER') ?: 'root';
$DATABASE_PASSWORD = getenv('DB_PASS') ?: '';
$DATABASE_NAME = getenv('DB_NAME') ?: 'webapp';
$ENVIRONMENT = getenv('ENVIRONMENT') ?: 'DEV';
$NEST_SERVER = getenv("NEST_SERVER") ?: "http://host.docker.internal:3000";