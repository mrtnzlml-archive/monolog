<?php

/**
 * Test: Kdyby\Monolog\Processor\TracyUrlProcessor.
 *
 * @testCase KdybyTests\Monolog\TracyUrlProcessor
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Monolog
 */

namespace KdybyTests\Monolog;

use Kdyby;
use Kdyby\Monolog\Processor\TracyUrlProcessor;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class TracyUrlProcessorTest extends Tester\TestCase
{

	public function testIgnoreAlreadyProcessed()
	{
		$processor = new TracyUrlProcessor('https://exceptions.kdyby.org');

		$record = [
			'message' => 'Some error',
			'context' => [
				'tracy' => 'exception--2016-01-17--17-54--72aee7b518.html',
			],
		];
		$processed = call_user_func($processor, $record);
		Assert::same('https://exceptions.kdyby.org/exception--2016-01-17--17-54--72aee7b518.html', $processed['context']['tracyUrl']);
	}

}

\run(new TracyUrlProcessorTest());
