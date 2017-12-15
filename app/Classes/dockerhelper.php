<?php

function docker_secret(string $name, string $default = ""): string
{
    if (file_exists("/run/secrets/$name")) {
        return trim(file_get_contents('/run/secrets/' . $name));
    }
    return $default;
}

function docker_secret_callable(string $name, string $default = ""): Closure
{
    return function () use ($name, $default) {
        return docker_secret($name, $default);
    };
}

if (env('HAS_SECRETS', 0) != 1 && file_exists("/run/secrets/aircharts.env")) {
  $data = file_get_contents("/run/secrets/aircharts.env");
  file_put_contents(app_path(".env"), $data, FILE_APPEND);
  file_put_contents(app_path(".env"), "HAS_SECRETS=1\n", FILE_APPEND);
  header("Location: https://www.aircharts.org/");
}
