<?php

/**
 * Test: Kdyby\Monolog\PriorityProcessor.
 *
 * @testCase KdybyTests\Monolog\PriorityProcessorTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Monolog
 */

namespace KdybyTests\Monolog;

use Kdyby;
use Kdyby\Monolog\Processor\PriorityProcessor;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class PriorityProcessorTest extends Tester\TestCase
{

	public function dataFunctional()
	{
		return [
			[
				['channel' => 'kdyby', 'context' => []],
				['channel' => 'kdyby', 'context' => ['priority' => 'debug']],
			],
			[
				['channel' => 'kdyby', 'context' => []],
				['channel' => 'kdyby', 'context' => ['priority' => 'info']],
			],
			[
				['channel' => 'kdyby', 'context' => []],
				['channel' => 'kdyby', 'context' => ['priority' => 'notice']],
			],
			[
				['channel' => 'kdyby', 'context' => []],
				['channel' => 'kdyby', 'context' => ['priority' => 'warning']],
			],
			[
				['channel' => 'kdyby', 'context' => []],
				['channel' => 'kdyby', 'context' => ['priority' => 'error']],
			],
			[
				['channel' => 'kdyby', 'context' => []],
				['channel' => 'kdyby', 'context' => ['priority' => 'critical']],
			],
			[
				['channel' => 'kdyby', 'context' => []],
				['channel' => 'kdyby', 'context' => ['priority' => 'alert']],
			],
			[
				['channel' => 'kdyby', 'context' => []],
				['channel' => 'kdyby', 'context' => ['priority' => 'emergency']],
			],

			// when bluescreen is rendered Tracy
			[
				['channel' => 'exception', 'context' => []],
				['channel' => 'kdyby', 'context' => ['priority' => 'exception']],
			],

			// custom priority
			[
				['channel' => 'nemam', 'context' => []],
				['channel' => 'kdyby', 'context' => ['priority' => 'nemam']],
			],

			// custom channel provided in $context parameter when adding record
			[
				['channel' => 'emails', 'context' => []],
				['channel' => 'kdyby', 'context' => ['channel' => 'emails']],
			],
			[
				['channel' => 'smses', 'context' => []],
				['channel' => 'kdyby', 'context' => ['channel' => 'smses']],
			],
		];
	}



	/**
	 * @dataProvider dataFunctional
	 */
	public function testFunctional($expectedRecord, $providedRecord)
	{
		Assert::same($expectedRecord, call_user_func(new PriorityProcessor(), $providedRecord));
	}

}

\run(new PriorityProcessorTest());
