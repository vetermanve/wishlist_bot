<?php

chdir(__DIR__);
require_once "vendor/autoload.php";

// load env
$repository = Dotenv\Repository\RepositoryBuilder::createWithNoAdapters()
    ->addAdapter(Dotenv\Repository\Adapter\EnvConstAdapter::class)
    ->addWriter(Dotenv\Repository\Adapter\PutenvAdapter::class)
    ->immutable()
    ->make();

$dotEnv = Dotenv\Dotenv::create($repository, __DIR__, ['.env','.env.local'], false);
$dotEnv->load();