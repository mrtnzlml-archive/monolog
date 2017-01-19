<?php

/**
 * Test: Kdyby\Monolog\Processor\TracyExceptionProcessor.
 *
 * @testCase KdybyTests\Monolog\TracyExceptionProcessor
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Monolog
 */

namespace KdybyTests\Monolog;

use Kdyby;
use Kdyby\Monolog\Processor\TracyExceptionProcessor;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class TracyExceptionProcessorTest extends Tester\TestCase
{

	/**
	 * @var TracyExceptionProcessor
	 */
	private $processor;



	protected function setUp()
	{
		$this->processor =  new TracyExceptionProcessor(TEMP_DIR);
	}



	public function testIgnoreAlreadyProcessed()
	{
		$record = [
			'message' => 'Some error',
			'context' => [
				'tracy' => 'exception--2016-01-17--17-54--72aee7b518.html',
			],
		];
		$processed = call_user_func($this->processor, $record);
		Assert::same($record, $processed);
	}



	public function testLogBluescreenFromContext()
	{
		$record = [
			'message' => 'Some error',
			'context' => [
				'exception' => new \RuntimeException('message'),
			],
		];
		$processed = call_user_func($this->processor, $record);
		Assert::match('exception-%a%.html', $processed['context']['tracy']);
		Assert::true(file_exists(TEMP_DIR . '/' . $processed['context']['tracy']));
		Assert::false(isset($processed['context']['exception']));
	}

}

\run(new TracyExceptionProcessorTest());
