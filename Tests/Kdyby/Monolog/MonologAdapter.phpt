<?php

/**
 * Test: Kdyby\Monolog\MonologAdapter.
 *
 * @testCase KdybyTests\Monolog\MonologAdapterTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Monolog
 */

namespace KdybyTests\Monolog;

use Kdyby;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MonologAdapterTest extends Tester\TestCase
{

	/**
	 * @var Kdyby\Monolog\Diagnostics\MonologAdapter
	 */
	protected $adapter;

	/**
	 * @var Logger
	 */
	protected $monolog;

	/**
	 * @var TestHandler
	 */
	protected $testHandler;



	protected function setUp()
	{
		$this->monolog = new Logger('kdyby', [$this->testHandler = new TestHandler()]);
		$this->adapter = new Kdyby\Monolog\Diagnostics\MonologAdapter($this->monolog);
	}



	/**
	 * @return array
	 */
	public function dataLog_standard()
	{
		$now = new \DateTime();
		$datetime = $now->format('[Y-m-d H-i-s]');

		return [
			[$now, [$datetime, 'test message 1', ' @ https://www.kdyby.org/', NULL], 'debug'],
			[$now, [$datetime, 'test message 2', ' @ https://www.kdyby.org/', NULL], 'info'],
			[$now, [$datetime, 'test message 3', ' @ https://www.kdyby.org/', NULL], 'notice'],
			[$now, [$datetime, 'test message 4', ' @ https://www.kdyby.org/', NULL], 'warning'],
			[$now, [$datetime, 'test message 5', ' @ https://www.kdyby.org/', NULL], 'error'],
			[$now, [$datetime, 'test message 6', ' @ https://www.kdyby.org/', NULL], 'critical'],
			[$now, [$datetime, 'test message 7', ' @ https://www.kdyby.org/', NULL], 'alert'],
			[$now, [$datetime, 'test message 8', ' @ https://www.kdyby.org/', NULL], 'emergency'],
		];
	}



	/**
	 * @dataProvider dataLog_standard
	 */
	public function testLog_standard(\DateTime $now, $message, $priority)
	{
		Assert::count(0, $this->testHandler->getRecords());
		$this->adapter->log($message, $priority);
		Assert::count(1, $this->testHandler->getRecords());

		list($record) = $this->testHandler->getRecords();
		Assert::same('kdyby', $record['channel']);
		Assert::same($message[1], $record['message']);
		Assert::same(strtoupper($priority), $record['level_name']);
		Assert::same($priority, $record['context']['priority']);
		Assert::type('DateTime', $record['datetime']);
		Assert::same('https://www.kdyby.org/', $record['context']['at']);
	}



	public function testLog_fromCli()
	{
		$now = new \DateTime();
		$datetime = $now->format('[Y-m-d H-i-s]');

		$this->adapter->log([$datetime, 'test message', ' @ CLI: php www/index.php orm:validate', NULL], 'info');
		Assert::count(1, $this->testHandler->getRecords());

		list($record) = $this->testHandler->getRecords();
		Assert::same('kdyby', $record['channel']);
		Assert::same('test message', $record['message']);
		Assert::same('INFO', $record['level_name']);
		Assert::same('info', $record['context']['priority']);
		Assert::type('DateTime', $record['datetime']);
		Assert::same('CLI: php www/index.php orm:validate', $record['context']['at']);
	}



	public function testLog_withTracy()
	{
		$now = new \DateTime();
		$datetime = $now->format('[Y-m-d H-i-s]');

		$this->adapter->log([$datetime, 'test message', ' @ https://www.kdyby.org/', ' @@ exception-2014-08-14-11-11-26-88167e58be9dc0dfd12a61b3d8d33838.html'], 'exception');
		Assert::count(1, $this->testHandler->getRecords());

		list($record) = $this->testHandler->getRecords();
		Assert::same('kdyby', $record['channel']);
		Assert::same('test message', $record['message']);
		Assert::same('CRITICAL', $record['level_name']);
		Assert::same('exception', $record['context']['priority']);
		Assert::same('https://www.kdyby.org/', $record['context']['at']);
		Assert::same('exception-2014-08-14-11-11-26-88167e58be9dc0dfd12a61b3d8d33838.html', $record['context']['tracy']);
	}



	public function testLog_withCustomPriority()
	{
		$now = new \DateTime();
		$datetime = $now->format('[Y-m-d H-i-s]');

		$this->adapter->log([$datetime, 'test message', ' @ https://www.kdyby.org/', NULL], 'nemam');
		Assert::count(1, $this->testHandler->getRecords());

		list($record) = $this->testHandler->getRecords();
		Assert::same('kdyby', $record['channel']);
		Assert::same('test message', $record['message']);
		Assert::same('INFO', $record['level_name']);
		Assert::same('nemam', $record['context']['priority']);
		Assert::same('https://www.kdyby.org/', $record['context']['at']);
	}

}

\run(new MonologAdapterTest());
