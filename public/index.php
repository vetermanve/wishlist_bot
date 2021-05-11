<?php

chdir(dirname(__DIR__));

require_once 'vendor/autoload.php';

use Base\Run\Component\BootstrapWorkerDC;
use Base\Run\RoutingProcessor;
use Monolog\Handler\RotatingFileHandler;
use Verse\Run\RunContext;
use Verse\Run\RunCore;
use Verse\Run\RuntimeLog;
use Verse\Run\Schema\RegularHttpRequestSchema;
use Verse\Run\Util\HttpEnvContext;

// load env
$dotenv = Dotenv\Dotenv::createImmutable(getcwd());
$dotenv->load();

// build request context
$env = new HttpEnvContext();
$env->fill([
    HttpEnvContext::HTTP_COOKIE    => &$_COOKIE,
    HttpEnvContext::HTTP_GET       => &$_GET,
    HttpEnvContext::HTTP_POST      => &$_POST,
    HttpEnvContext::HTTP_POST_BODY => trim(file_get_contents("php://input")),
    HttpEnvContext::HTTP_SERVER    => &$_SERVER,
    HttpEnvContext::HTTP_HEADERS   => getallheaders(),
]);

// build schema
$schema = new RegularHttpRequestSchema();
$schema->setProcessor(new RoutingProcessor());
$schema->setHttpEnv($env);
$schema->addComponent(new BootstrapWorkerDC());

$context = new RunContext();
$pidId = ('http.'.getmypid() . '@' . gethostname());

$context->fill([
    RunContext::HOST     => $_SERVER['HTTP_HOST'],
    RunContext::IDENTITY => $pidId,
    RunContext::IS_SECURE_CONNECTION => stripos($_SERVER['SERVER_PROTOCOL'],'https') === true,
    RunContext::GLOBAL_CONFIG => $_ENV + [RunContext::IDENTITY => $pidId]
]);

$runtime = new RuntimeLog($context->get(RunContext::IDENTITY));
$runtime->pushHandler(new RotatingFileHandler(dirname(__DIR__).'/logs/out.log'));
$runtime->catchErrors();

$core = new RunCore();
$core->setContext($context);
$core->setSchema($schema);
$core->setRuntime($runtime);

$core->configure();
$core->prepare();
$core->run();