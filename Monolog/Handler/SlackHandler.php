<?php

namespace Mrtnzlml\Monolog\Handler;

class SlackHandler extends \Monolog\Handler\SlackHandler
{

	/**
	 * @var string
	 */
	private $productionMode;

	public function __construct(
		$productionMode,
		$token,
		$channel,
		$username = 'Monolog',
		$useAttachment = FALSE,
		$iconEmoji = 'poop',
		$level = \Monolog\Logger::CRITICAL,
		$bubble = TRUE,
		$useShortAttachment = FALSE,
		$includeContextAndExtra = TRUE
	) {
		parent::__construct(
			$token,
			$channel,
			$username,
			$useAttachment,
			$iconEmoji,
			$level,
			$bubble,
			$useShortAttachment,
			$includeContextAndExtra
		);
		$this->productionMode = $productionMode;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array $record
	 */
	protected function write(array $record)
	{
		if (!$this->productionMode) {
			return;
		}
		$logDirectory = \Tracy\Debugger::$logDirectory;
		$snooze = @strtotime('15 minutes') - time(); // @ timezone may not be set
		if (@filemtime($logDirectory . '/slack-notification-sent') + $snooze < time() // @ file may not exist
			&& @file_put_contents($logDirectory . '/slack-notification-sent', 'sent') // @ file may not be writable
		) {
			parent::write($record);
		}
	}

}
