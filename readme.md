More awesome Monolog for Nette Framework
========================================

[![Build Status](https://travis-ci.org/adeira/monolog.svg?branch=master)](https://travis-ci.org/adeira/monolog)

This package is extending [Kdyby\Monolog](https://github.com/Kdyby/Monolog) to make it more flexible, configurable and awesome.

Inspiration:

- [Logging with Monolog](http://symfony.com/doc/current/logging.html)
- [How to Log Messages to different Files](http://symfony.com/doc/current/logging/channels_handlers.html)
- [Configure multiple loggers and handlers](https://github.com/theorchard/monolog-cascade)

Installation
------------

	composer require adeira/monolog

And register DI extension in `config.neon`:

	extensions:
		monolog: Adeira\Monolog\DI\MonologExtension

Default configuration
---------------------
Package `adeira/monolog` brings a lot of [formatters](https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md#formatters) and [processors](https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md#processors) by default. This means you can use them only by name. Here is the complete list of defaults:

	formatters:
		chromePHP: Monolog\Formatter\ChromePHPFormatter
		fluentd: Monolog\Formatter\FluentdFormatter
		gelfMessage: Monolog\Formatter\GelfMessageFormatter
		html: Monolog\Formatter\HtmlFormatter
		json: Monolog\Formatter\JsonFormatter
		line: Monolog\Formatter\LineFormatter
		loggly: Monolog\Formatter\LogglyFormatter
		mongoDB: Monolog\Formatter\MongoDBFormatter
		normalizer: Monolog\Formatter\NormalizerFormatter
		dcalar: Monolog\Formatter\ScalarFormatter
		wildfire: Monolog\Formatter\WildfireFormatter

	processors:
		git: Monolog\Processor\GitProcessor
		introspection: Monolog\Processor\IntrospectionProcessor
		memoryPeakUsage: Monolog\Processor\MemoryPeakUsageProcessor
		memoryUsage: Monolog\Processor\MemoryUsageProcessor
		processId: Monolog\Processor\ProcessIdProcessor
		psrLogMessage: Monolog\Processor\PsrLogMessageProcessor
		tag: Monolog\Processor\TagProcessor
		uid: Monolog\Processor\UidProcessor
		web: Monolog\Processor\WebProcessor

	handlers:
		errorLog:
			class: Monolog\Handler\ErrorLogHandler

	loggers:
		global:
			class: Kdyby\Monolog\Logger # including custom global handlers and processors

**You DON'T have to copy paste this into your config file. This is just a quick reference so you can find what you want quickly.**

Overriding configuration
------------------------
Every formatter and processor uses default configuration if possible. If you want custom configuration, it's easy to override it like this:

	monolog:
		processors:
			web: Monolog\Processor\WebProcessor(NULL, [
					ip: REMOTE_ADDR,
					userAgent: HTTP_USER_AGENT,
				])

Changing global logger configuration and adding own loggers
-----------------------------------------------------------
As you already know - this package extends kdyby\monolog. And there is default global logger for whole application. You can configure it by using `global` name in `loggers` section. Another loggers will be registered independently (with name or anonymously):

	monolog:
		handlers:
			slack:
				class: Adeira\Monolog\Handler\SlackHandler(
					%productionMode%,
					%slack.token%,
					%slack.channel%,
					%slack.username%,
					%slack.useAttachment%,
					%slack.iconEmoji%,
					%slack.level%,
					%slack.bubble%,
					%slack.useShortAttachment%,
					%slack.includeContextAndExtra%
				)
		loggers:
			global: # global logger from Kdyby (\Kdyby\Monolog\Logger)
				handlers: [slack]
				processors: [git, introspection, web]
			- class: Custom\Monolog\Loggers\UsersAuditLogger
			  handlers: [database]

This way you can setup a lot of loggers with different configuration (handlers, processors, formatter). Your custom loggers are like services in DI container so you can use autowiring to get them. Nothing special. Remember, that it's good idea to extend these custom loggers from `\Monolog\Logger` or even better from `\Kdyby\Monolog\Logger`.

Using Kdyby configuration options
---------------------------------
Package `adeira/monolog` is not playing with Kdyby configuration so you can use it if you want to without changes:

	monolog:
		hookToTracy: yes
		registerFallback: yes
		usePriorityProcessor: yes

Awesome right? :)
