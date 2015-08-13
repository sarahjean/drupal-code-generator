<?php

namespace DrupalCodeGenerator\Tests;

use DrupalCodeGenerator\Commands;
use DrupalCodeGenerator\Commands\Other;
use DrupalCodeGenerator\GeneratorsDiscovery;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * A test for a whole application.
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase {

  /**
   * Total count of genetators.
   *
   * @var integer
   */
  protected $totalGenerators;

  /**
   * The application.
   *
   * @var Application
   */
  protected $application;

  /**
   * The navigation command.
   *
   * @var Commands\Navigation
   */
  protected $command;

  /**
   * Sample answers and output for testing.
   *
   * @var array
   *
   * @see integration-fixtures.php
   */
  protected $fixtures;

  /**
   * The question helper.
   *
   * @var HelperInterface
   */
  protected $questionHelper;

  /**
   * The helper set.
   *
   * @var HelperSet
   */
  protected $helperSet;

  /**
   * The command tester.
   *
   * @var CommandTester
   */
  protected $commandTester;

  /**
   * The filesystem utility.
   *
   * @var Filesystem
   */
  protected $filesystem;

  /**
   * The destination directory.
   *
   * @var string
   *
   * @see Navigation::configure()
   */
  protected $destination;

  /**
   * {@inheritdoc}
   */
  public function setUp() {

    $this->application = new Application('Drupal Code Generator', '@git-version@');

    $filesystem = new Filesystem();
    $twig_loader = new \Twig_Loader_Filesystem(DCG_ROOT . '/src/Resources/templates');
    $twig = new \Twig_Environment($twig_loader);

    $discovery = new GeneratorsDiscovery([DCG_ROOT . '/src/Commands'], $filesystem, $twig);
    $generators = $discovery->getGenerators();
    $this->totalGenerators = count($generators);

    $this->application->addCommands($generators);

    $navigation = new Commands\Navigation();
    $navigation->init($generators);
    $this->application->add($navigation);

    $this->command = $this->application->find('navigation');

    $this->fixtures = require 'integration-fixtures.php';

    $this->questionHelper = $this->getMock('Symfony\Component\Console\Helper\QuestionHelper', ['ask']);

    $this->helperSet = $this->command->getHelperSet();

    $this->commandTester = new CommandTester($this->command);

    $this->filesystem = new Filesystem();

    $this->destination = DCG_SANDBOX . '/tests';
  }

  /**
   * Test callback.
   */
  public function testExecute() {
    foreach ($this->fixtures as $fixture) {
      $this->mockQuestionHelper($fixture['answers']);
      $this->commandTester->execute(['command' => 'navigation', '--destination' => './sandbox/tests']);
      $this->assertEquals(implode("\n", $fixture['output']) . "\n", $this->commandTester->getDisplay());
      $this->filesystem->remove($this->destination);
    }

    $this->assertEquals(
      $this->totalGenerators,
      count($this->fixtures),
      'Some generators are not represented in the integration test.'
    );
  }

  /**
   * Mocks question helper.
   */
  protected function mockQuestionHelper(array $answers) {
    foreach ($answers as $key => $answer) {
      $this->questionHelper->expects($this->at($key))
        ->method('ask')
        ->will($this->returnValue($answer));
    }

    $this->helperSet->set($this->questionHelper, 'question');
  }

}
