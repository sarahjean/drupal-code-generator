<?php

namespace DrupalCodeGenerator\Tests\Other;

use DrupalCodeGenerator\Tests\Generator\GeneratorBaseTest;

/**
 * Test for other:nginx-virtual-host command.
 */
class NginxVirtualHostTest extends GeneratorBaseTest {

  protected $class = 'Other\NginxVirtualHost';

  protected $interaction = [
    'Server name [example.com]:' => 'example.local',
    'Document root [/var/www/example.local/docroot]:' => '/var/www/example.local/docroot',
    'Public file system path [sites/default/files]:' => 'files',
    'Private file system path:' => 'files/private',
    'Address of a FastCGI server [unix:%socket%]:' => 'unix:/run/php/php7.0-fpm.sock',
  ];

  protected $fixtures = [
    'example.local' => __DIR__ . '/_nginx_virtual_host',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getExpectedDisplay() {
    $display = parent::getExpectedDisplay();

    $socket = PHP_MAJOR_VERSION == 5
      ? '/run/php5-fpm.sock'
      : sprintf('/run/php/php%s.%s-fpm.sock', PHP_MAJOR_VERSION, PHP_MINOR_VERSION);

    return str_replace('%socket%', $socket, $display);
  }

}
