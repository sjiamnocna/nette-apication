<?php

use Bootstrap;
use APIcation\CLI\CRunner;

// relative path to /vendor/bin
include $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';
// in case it don't work, check this path and also Namespace to your Bootstrap class
// as defauld I suppose your path is /app/bootstrap.php
require_once __DIR__ . '/../../app/Bootstrap.php';

// create the same environment as with whole app
Bootstrap::boot()
  ->createInstance(CRunner::class)
  ->run( $argv );