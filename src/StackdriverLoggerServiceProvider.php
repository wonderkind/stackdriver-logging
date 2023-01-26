<?php

declare(strict_types=1);

namespace Wonderkind\StackdriverLogging;

use Exception;
use Monolog\Logger as Monolog;
use Google\Cloud\Logging\LoggingClient;
use Illuminate\Support\ServiceProvider;
use Wonderkind\StackdriverLogging\Handler\StackdriverLoggingHandler;
use Google\Cloud\Core\Compute\Metadata;

final class StackdriverLoggerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . $this->defaultPath() => $this->configFilePath(),
        ]);
    }

    public function register(): void
    {
        $this->mergeStackdriverConfig();
        $this->bindLoggingClient();
        $this->bindLogHandler();
        $this->bindLogger();
    }

    private function mergeStackdriverConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . $this->defaultPath(), 'stackdriver');
    }

    private function bindLogger(): void
    {
        $this->app->singleton(LoggerInterface::class, function ($app) {
            $monolog = new Monolog('stackdriver', [$app->make(StackdriverLoggingHandler::class)]);

            return new StackdriverLogger($monolog);
        });
    }

    private function bindLoggingClient(): void
    {
        $this->app->bind(LoggingClient::class, function () {
            return new LoggingClient(['projectId' => config('stackdriver.project_id')]);
        });
    }

    private function bindLogHandler(): void
    {
        $this->app->bind(StackdriverLoggingHandler::class, function ($app) {
            /** @var LoggingClient $loggingClient */
            $loggingClient = $app->make(LoggingClient::class);
            $options = $this->getLoggerOptions();

            return new StackdriverLoggingHandler($loggingClient->psrLogger(config('stackdriver.log'), $options));
        });
    }

    private function getLoggerOptions(): array
    {
        try {
            $metadata = new Metadata();
            $id = $metadata->get('instance/id');

            if (!$id) {
                return [];
            }

            return [
                'resource' => [
                    'type' => 'gce_instance',
                    'labels' => [
                        'instance_id' => $id,
                    ],
                ],
            ];
        } catch (Exception $exception) {
            return [];
        }
    }

    private function configFilePath(): string
    {
        return (function_exists('config_path')) ? $this->loadLaravel() : $this->loadLumen();
    }

    private function loadLaravel(): string
    {
        return config_path('stackdriver.php');
    }

    private function loadLumen(): string
    {
        return $this->app->basePath() . $this->defaultPath();
    }

    private function defaultPath(): string
    {
        return '/config/stackdriver.php';
    }
}
