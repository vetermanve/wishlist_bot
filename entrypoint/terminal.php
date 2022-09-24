<?php

require_once __DIR__.'/../bootstrap.php';

use Monolog\Handler\RotatingFileHandler;
use Verse\Run\RunContext;
use Verse\Run\RunCore;
use Verse\Run\RuntimeLog;
use Verse\TelegramTerminal\Schema\TerminalTelegramSchema;

$arg_env = [];
foreach (array_slice($argv, 1) as $index => $item) {
    if (strpos($item, '=')) {
        [$key, $value] = explode( '=', $item, 2);
        $arg_env[$key] = $value;
    } else {
        $arg_env[$item] = true;
    }
}

// start build schema
$schema = new TerminalTelegramSchema();

$context = new RunContext();
$role = 'Terminal';
$pidId = ($role.'.'.getmypid() . '@' . gethostname());

$context->fill([
    RunContext::HOST     => $role,
    RunContext::IDENTITY => $pidId,
    RunContext::IS_SECURE_CONNECTION => false,
    RunContext::GLOBAL_CONFIG => (array)$arg_env + $_ENV + [
        RunContext::IDENTITY => $pidId,
    ],
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
