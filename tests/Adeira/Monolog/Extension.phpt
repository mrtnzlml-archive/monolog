<?php declare(strict_types = 1);

namespace Adeira\Monolog\Tests;

use Adeira\Monolog\DI;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class Extension extends \Tester\TestCase
{

	use \Testbench\TCompiledContainer;

	/** @var \Nette\DI\Container */
	private $container;

	public function setUp()
	{
		$this->container = $this->getContainer();
	}

	public function testDefaultFormatters()
	{
		Assert::same([
			'monolog.formatter.chromePHP' => TRUE,
			'monolog.formatter.fluentd' => TRUE,
			'monolog.formatter.gelfMessage' => TRUE,
			'monolog.formatter.html' => TRUE,
			'monolog.formatter.json' => TRUE,
			'monolog.formatter.line' => TRUE,
			'monolog.formatter.loggly' => TRUE,
			'monolog.formatter.mongoDB' => TRUE,
			'monolog.formatter.normalizer' => TRUE,
			'monolog.formatter.scalar' => TRUE,
			'monolog.formatter.wildfire' => TRUE,
		], $this->container->findByTag(DI\MonologExtension::TAG_FORMATTER));
	}

	public function testDefaultProcessors()
	{
		Assert::same([
			'monolog.processor.git' => TRUE,
			'monolog.processor.introspection' => TRUE,
			'monolog.processor.memoryPeakUsage' => TRUE,
			'monolog.processor.memoryUsage' => TRUE,
			'monolog.processor.processId' => TRUE,
			'monolog.processor.psrLogMessage' => TRUE,
			'monolog.processor.tag' => TRUE,
			'monolog.processor.uid' => TRUE,
			'monolog.processor.web' => TRUE,
		], $this->container->findByTag(DI\MonologExtension::TAG_PROCESSOR));
	}

	public function testDefaultHandlers()
	{
		Assert::same([
			'monolog.handler.errorLog' => TRUE,
		], $this->container->findByTag(DI\MonologExtension::TAG_HANDLER));
	}

	public function testDefaultLoggers()
	{
		Assert::same([], $this->container->findByTag(DI\MonologExtension::TAG_LOGGER));
	}

	public function testCustomAnonymousLogger()
	{
		$refreshedContainer = $this->refreshContainer([
			'monolog' => [
				'loggers' => [
					['class' => \Adeira\Monolog\Tests\Mocks\CustomLogger::class],
				],
			],
		]);
		Assert::same([
			'monolog.logger.0' => TRUE,
		], $refreshedContainer->findByTag(DI\MonologExtension::TAG_LOGGER));
	}

	public function testCustomNamedLogger()
	{
		$refreshedContainer = $this->refreshContainer([
			'monolog' => [
				'loggers' => [
					'named' => ['class' => \Adeira\Monolog\Tests\Mocks\CustomLogger::class],
				],
			],
		]);
		Assert::same([
			'monolog.logger.named' => TRUE,
		], $refreshedContainer->findByTag(DI\MonologExtension::TAG_LOGGER));
	}

	public function testEnhancedConfiguration()
	{
		$refreshedContainer = $this->refreshContainer([
			'monolog' => [
				'handlers' => [
					'slack' => [
						'class' => new \Nette\DI\Statement(\Adeira\Monolog\Handler\SlackHandler::class, [
							'%productionMode%',
							'slack.token', //DIY
							'slack.channel',
							'slack.username',
							'slack.useAttachment',
							'slack.iconEmoji',
							'slack.level',
							'slack.bubble',
							'slack.useShortAttachment',
							'slack.includeContextAndExtra',
						]),
					],
				],
				'loggers' => [
					'global' => [
						'handlers' => ['slack', 'errorLog'],
						'processors' => ['git', 'introspection', 'web'],
					],
				],
			],
		]);
		Assert::same([], $refreshedContainer->findByTag(DI\MonologExtension::TAG_LOGGER));
		/** @var \Kdyby\Monolog\Logger $globalLogger */
		Assert::type(\Kdyby\Monolog\Logger::class, $globalLogger = $refreshedContainer->getByType(\Kdyby\Monolog\Logger::class));

		Assert::same([
			\Kdyby\Monolog\Handler\FallbackNetteHandler::class,
			\Adeira\Monolog\Handler\SlackHandler::class,
			\Monolog\Handler\ErrorLogHandler::class,
		], array_map(function ($item) {
			return get_class($item);
		}, $globalLogger->getHandlers()));

		Assert::same([
			\Kdyby\Monolog\Processor\TracyExceptionProcessor::class,
			\Kdyby\Monolog\Processor\PriorityProcessor::class,
			\Monolog\Processor\GitProcessor::class,
			\Monolog\Processor\IntrospectionProcessor::class,
			\Monolog\Processor\WebProcessor::class,
		], array_map(function ($item) {
			return get_class($item);
		}, $globalLogger->getProcessors()));
	}

}

(new Extension)->run();
