<?php

require_once __DIR__.'/../bootstrap.php';

use Monolog\Handler\RotatingFileHandler;
use Verse\Telegram\Run\Scheme\TelegramPullExtendedScheme;
use Verse\Run\RunContext;
use Verse\Run\RunCore;
use Verse\Run\RuntimeLog;

// start build schema
$schema = new TelegramPullExtendedScheme();

$context = new RunContext();
$role = 'TelegramProvider';
$pidId = ($role.'.'.getmypid() . '@' . gethostname());

$context->fill([
    RunContext::HOST     => $role,
    RunContext::IDENTITY => $pidId,
    RunContext::IS_SECURE_CONNECTION => false,
    RunContext::GLOBAL_CONFIG => $_ENV + [RunContext::IDENTITY => $pidId],
]);

$runtime = new RuntimeLog($context->get(RunContext::IDENTITY));
$runtime->pushHandler(new RotatingFileHandler(getcwd().'/logs/'.$role.'/out.log'));
$runtime->catchErrors();

$core = new RunCore();
$core->setContext($context);
$core->setSchema($schema);
$core->setRuntime($runtime);

$core->configure();
$core->prepare();
$core->run();
