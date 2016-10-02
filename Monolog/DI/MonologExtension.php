<?php

namespace Adeira\Monolog\DI;

use Monolog\Formatter;
use Monolog\Processor;
use Nette\DI;

/**
 * Logging with Monolog: http://symfony.com/doc/current/logging.html
 * How to Log Messages to different Files: http://symfony.com/doc/current/logging/channels_handlers.html
 * Configure multiple loggers and handlers: https://github.com/theorchard/monolog-cascade
 */
class MonologExtension extends \Kdyby\Monolog\DI\MonologExtension
{

	const TAG_FORMATTER = 'adeira.monolog.formatter';
	const TAG_PROCESSOR = 'adeira.monolog.processor';
	const TAG_HANDLER = 'adeira.monolog.handler';
	const TAG_LOGGER = 'adeira.monolog.logger';

	public $defaults = [
		'formatters' => [
			'chromePHP' => Formatter\ChromePHPFormatter::class,
			'fluentd' => Formatter\FluentdFormatter::class,
			'gelfMessage' => Formatter\GelfMessageFormatter::class,
			'html' => Formatter\HtmlFormatter::class,
			'json' => Formatter\JsonFormatter::class,
			'line' => Formatter\LineFormatter::class,
			'loggly' => Formatter\LogglyFormatter::class,
			'mongoDB' => Formatter\MongoDBFormatter::class,
			'normalizer' => Formatter\NormalizerFormatter::class,
			'scalar' => Formatter\ScalarFormatter::class,
			'wildfire' => Formatter\WildfireFormatter::class,
		],
		'processors' => [
			'git' => Processor\GitProcessor::class,
			'introspection' => Processor\IntrospectionProcessor::class,
			'memoryPeakUsage' => Processor\MemoryPeakUsageProcessor::class,
			'memoryUsage' => Processor\MemoryUsageProcessor::class,
			'processId' => Processor\ProcessIdProcessor::class,
			'psrLogMessage' => Processor\PsrLogMessageProcessor::class,
			'tag' => Processor\TagProcessor::class,
			'uid' => Processor\UidProcessor::class,
			'web' => Processor\WebProcessor::class,
		],
		'handlers' => [
			'errorLog' => [
				'class' => \Monolog\Handler\ErrorLogHandler::class,
			],
		],
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		//FORMATTERS
		foreach ($config['formatters'] as $formatterName => $formatter) {
			DI\Compiler::loadDefinitions($builder, [
				$this->prefix('formatter.' . $formatterName) => [
					'class' => $formatter,
					'tags' => [
						self::TAG_FORMATTER,
					],
				],
			]);
		}

		//PROCESSORS
		foreach ($config['processors'] as $processorName => $processor) {
			DI\Compiler::loadDefinitions($builder, [
				$this->prefix('processor.' . $processorName) => [
					'class' => $processor,
					'tags' => [
						self::TAG_PROCESSOR,
					],
				],
			]);
		}

		//HANDLERS
		foreach ($config['handlers'] as $handlerName => $handlerConfig) {
			if (is_string($handlerConfig)) {
				throw new \Nette\UnexpectedValueException("Wrong handler format. Handlers configuration must be in this format:\n\nhandlers:\n\t{$handlerName}:\n\t\tclass: $handlerConfig\n\t\t[formatter: formatterName]\n\t\t[processors: [processorName, ...]]");
			}

			DI\Compiler::loadDefinitions($builder, [
				$serviceName = $this->prefix('handler.' . $handlerName) => [
					'class' => $handlerConfig['class'],
					'tags' => [
						self::TAG_HANDLER,
					],
				],
			]);
			$handler = $builder->getDefinition($serviceName);

			if (isset($handlerConfig['formatter'])) {
				$handler->addSetup('?->setFormatter(?)', [
					'@self',
					$builder->getDefinition($this->prefix('formatter.' . $handlerConfig['formatter'])),
				]);
			}

			if (isset($handlerConfig['processors'])) {
				foreach (array_reverse($handlerConfig['processors']) as $handlerName) {
					$handler->addSetup('?->pushProcessor(?)', [
						'@self',
						$builder->getDefinition($this->prefix('processor.' . $handlerName)),
					]);
				}
			}
		}

		//LOGGERS
		if (isset($config['loggers'])) {
			foreach ($config['loggers'] as $loggerName => $loggerConfig) {
				if ($loggerName === 'global') {
					continue;
				}

				if (is_string($loggerConfig)) {
					throw new \Nette\UnexpectedValueException("Wrong logger format. Loggers configuration must be in this format:\n\nloggers:\n\t{$loggerName}:\n\t\tclass: $loggerConfig\n\t\t[processors: [processorName, ...]]\n\t\t[handlers: [handlerName, ...]]");
				}

				DI\Compiler::loadDefinitions($builder, [
					$serviceName = $this->prefix('logger.' . $loggerName) => [
						'class' => $loggerConfig['class'],
						'arguments' => [
							'name' => $loggerName,
						],
						'tags' => [
							self::TAG_LOGGER,
						],
					],
				]);
				$logger = $builder->getDefinition($serviceName);

				if (isset($loggerConfig['processors'])) {
					foreach (array_reverse($loggerConfig['processors']) as $processorName) {
						$logger->addSetup('?->pushProcessor(?)', [
							'@self',
							$builder->getDefinition($this->prefix('processor.' . $processorName)),
						]);
					}
				}

				if (isset($loggerConfig['handlers'])) {
					foreach (array_reverse($loggerConfig['handlers']) as $handlerName) {
						$logger->addSetup('?->pushHandler(?)', [
							'@self',
							$builder->getDefinition($this->prefix('handler.' . $handlerName)),
						]);
					}
				}
			}
		}

		unset($config['handlers']); //handled by this extension
		unset($config['processors']); //handled by this extension
		$this->setConfig($config);
		parent::loadConfiguration();
	}

	public function beforeCompile()
	{
		$config = $this->getConfig($this->defaults);

		if (isset($config['loggers'])) {
			$loggers = $config['loggers'];
			if (isset($loggers['global'])) {
				$builder = $this->getContainerBuilder();
				$globalConfig = $loggers['global'];

				if (isset($globalConfig['handlers'])) {
					foreach (array_reverse($globalConfig['handlers']) as $handlerName) {
						foreach ($builder->findByType(\Kdyby\Monolog\Logger::class) as $globalLogger) {
							$globalLogger->addSetup('?->pushHandler(?)', [
								'@self',
								$builder->getDefinition($this->prefix('handler.' . $handlerName)),
							]);
						}
					}
				}

				if (isset($globalConfig['processors'])) {
					foreach (array_reverse($globalConfig['processors']) as $processorName) {
						foreach ($builder->findByType(\Kdyby\Monolog\Logger::class) as $globalLogger) {
							$globalLogger->addSetup('?->pushProcessor(?)', [
								'@self',
								$builder->getDefinition($this->prefix('processor.' . $processorName)),
							]);
						}
					}
				}
			}
		}

		parent::beforeCompile();
	}

	public function afterCompile(\Nette\PhpGenerator\ClassType $class)
	{
		parent::afterCompile($class);
	}

}
