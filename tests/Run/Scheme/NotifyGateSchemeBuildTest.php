<?php


namespace Run\Scheme;


use App\Done\Controller\Done;
use App\Item\Controller\All;
use App\Item\Controller\EditMode;
use App\Landing\Controller\Landing;
use PHPUnit\Framework\TestCase;
use Verse\Di\Env;
use Verse\Notify\Service\NotifyGate;
use Verse\Run\RunContext;
use Verse\Run\RunCore;
use Verse\Run\RuntimeLog;
use Verse\Telegram\Run\RequestRouter\ResourceCompiler;
use Verse\Telegram\Run\Scheme\TelegramPullExtendedScheme;

class NotifyGateSchemeBuildTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $path = explode(DIRECTORY_SEPARATOR, getcwd());
        $key = array_search('tests', $path);
        if ($key) {
            $path = array_slice($path, 0, $key+1);
            chdir(implode(DIRECTORY_SEPARATOR, $path));
        }

        parent::setUpBeforeClass();
    }

    public function testTrue()
    {
        // start build schema
        $schema = new TelegramPullExtendedScheme();

        $context = new RunContext();
        $role = 'TestRun';
        $pidId = ($role.'.'.getmypid() . '@' . gethostname());

        $context->fill([
            RunContext::HOST     => $role,
            RunContext::IDENTITY => $pidId,
            RunContext::IS_SECURE_CONNECTION => false,
            RunContext::GLOBAL_CONFIG => $_ENV + [RunContext::IDENTITY => $pidId],
        ]);

        $runtime = new RuntimeLog($context->get(RunContext::IDENTITY));
        $runtime->catchErrors();

        $core = new RunCore();
        $core->setContext($context);
        $core->setSchema($schema);
        $core->setRuntime($runtime);

        $core->configure();
        $core->prepare();

        $gate = Env::getContainer()->bootstrap(NotifyGate::class);

        $this->assertInstanceOf(NotifyGate::class, $gate,
            'NotifyGate was bootstrapped');

    }
}