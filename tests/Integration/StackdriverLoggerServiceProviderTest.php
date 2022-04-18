<?php

declare(strict_types=1);

namespace Wonderkind\StackdriverLogging\Test\Integration;

use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Orchestra\Testbench\TestCase;
use Wonderkind\StackdriverLogging\Handler\StackdriverLoggingHandler;
use Wonderkind\StackdriverLogging\LoggerInterface;
use Wonderkind\StackdriverLogging\StackdriverLogger;
use Wonderkind\StackdriverLogging\StackdriverLoggerServiceProvider;

final class StackdriverLoggerServiceProviderTest extends TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getPackageProviders($app): array
    {
        return [StackdriverLoggerServiceProvider::class];
    }

    /**
     * @test
     */
    public function it_loads_the_configuration(): void
    {
        $projectId = env('GOOGLE_STACKDRIVER_PROJECT_ID');
        $log = env('GOOGLE_STACKDRIVER_LOG');

        $this->assertTrue(!empty($projectId) && !empty($log));
        $this->assertEquals($projectId, config('stackdriver.project_id'));
        $this->assertEquals($log, config('stackdriver.log'));
    }

    /**
     * @test
     */
    public function it_uses_the_same_config_path_if_function_does_not_exists(): void
    {
        // Used when using Lumen. which does not have the config_path helper.
        // It is tested this way so we don't have to override the function_exists function
        $this->assertEquals(config_path('stackdriver.php'), $this->app->basePath() . '/config/stackdriver.php');
    }

    /**
     * @test
     */
    public function it_binds_the_log_handler(): void
    {
        $handler = $this->app->make(StackdriverLoggingHandler::class);
        $this->assertInstanceOf(StackdriverLoggingHandler::class, $handler);
    }

    /**
     * @test
     */
    public function it_can_be_injected_from_the_container(): void
    {
        $logger = $this->app->make(LoggerInterface::class);
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertInstanceOf(StackdriverLogger::class, $logger);

        $haystack = $logger->getLogger()->getHandlers();
        $handler = $haystack[array_key_first($haystack)];

        $this->assertInstanceOf(StackdriverLoggingHandler::class, $handler);
    }

    /**
     * @test
     */
    public function it_can_be_registered_as_log_channel(): void
    {
        config(['logging.channels.stackdriver' => [
            'driver' => 'monolog',
            'handler' => StackdriverLoggingHandler::class
        ]]);


        $haystack = Log::channel('stackdriver')->getLogger()->getHandlers();
        $handler = $haystack[array_key_first($haystack)];

        $this->assertInstanceOf(StackdriverLoggingHandler::class, $handler);
    }
}
