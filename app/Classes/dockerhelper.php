<?php

function docker_secret(string $name, string $default = null): string
{
    if (file_exists("/run/secrets/$name")) {
        return trim(file_get_contents('/run/secrets/' . $name));
    }
    return $default;
}

function docker_secret_callable(string $name, string $default = null): Closure
{
    return function () use ($name) {
        return docker_secret($name);
    };
}

if (env('HAS_SECRETS', 0) != 1) {
  $data = file_get_contents("/run/secrets/env");
  file_put_contents(app_path(".env"), $data, FILE_APPEND);
  file_put_contents(app_path(".env"), "HAS_SECRETS=1\n", FILE_APPEND);
  header("Location: https://www.aircharts.org/")
}
