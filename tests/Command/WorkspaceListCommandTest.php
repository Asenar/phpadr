<?php

namespace ADR\Command;

use ADR\Filesystem\AutoDiscoverConfig;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class WorkspaceListCommandTest extends TestCase
{
    private Filesystem $filesystem;

    /**
     * @var WorkspaceListCommand
     */
    private $command;

    public function setUp(): void
    {
        $this->vfs = vfsStream::setup();
        $this->filesystem = new Filesystem();
        $this->command = new WorkspaceListCommand(
            $this->vfs->url(),
        );
    }

    public function testInstanceOfCommand()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testName()
    {
        $this->assertEquals('workspace:list', $this->command->getName());
    }

    public function testDescription()
    {
        $this->assertEquals('List the ADRs', $this->command->getDescription());
    }

    public function testHelp()
    {
        $this->assertEquals('This command allows you list the ADRs', $this->command->getHelp());
    }

    public function testOptions()
    {
        $this->assertTrue($this->command->getDefinition()->hasOption('config'));

        $this->assertCount(1, $this->command->getDefinition()->getOptions());
    }

    public function testOptionConfig()
    {
        $option = $this->command->getDefinition()->getOption('config');

        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());
        $this->assertEquals('Config file (default: adr.yml)', $option->getDescription());
        $this->assertEquals(null, $option->getDefault());
    }

    public function testExecute(): void
    {
        $this->vfs->addChild(vfsStream::newDirectory('docs'));
        $this->vfs->getChild('docs')->addChild(vfsStream::newDirectory('arch'));
        $arch = $this->vfs->getChild('docs')->getChild('arch');

        $configContent = file_get_contents('adr.yml.dist');
        $configContent = str_replace('docs/arch', $this->vfs->url() . '/docs/arch', $configContent);
        $configFile = vfsStream::newFile('adr.yml')->at($this->vfs)->setContent($configContent)->url();

        $input = [
            'command'  => $this->command->getName(),
            '--config' => $configFile,
        ];

        (new Application())->add($this->command);

        $tester = new CommandTester($this->command);
        $tester->execute($input);

        $this->assertRegexp('/Workspace is empty/', $tester->getDisplay());

        $this->vfs->addChild(vfsStream::newFile('0001-foo.md')->at($arch));
        $this->vfs->addChild(vfsStream::newFile('0002-bar.md')->at($arch));

        $tester->execute($input);

        $this->assertContains('0001-foo.md', $tester->getDisplay());
        $this->assertContains('0002-bar.md', $tester->getDisplay());
    }

    public function testExecuteWithoutConfigParamShouldUseRootConfig(): void
    {
        $this->vfs->addChild(vfsStream::newDirectory('docs'));
        $this->vfs->getChild('docs')->addChild(vfsStream::newDirectory('arch'));
        $arch = $this->vfs->getChild('docs')->getChild('arch');

        $configContent = file_get_contents('adr.yml.dist');
        $configContent = str_replace('docs/arch', $this->vfs->url() . '/docs/arch', $configContent);
        $configFile = vfsStream::newFile('adr.yml')->at($this->vfs)->setContent($configContent)->url();

        $input = [
            'command'  => $this->command->getName(),
        ];

        (new Application())->add($this->command);

        $tester = new CommandTester($this->command);
        $tester->execute($input);

        $this->assertRegexp('/Workspace is empty/', $tester->getDisplay());

        $this->vfs->addChild(vfsStream::newFile('0001-foo.md')->at($arch));
        $this->vfs->addChild(vfsStream::newFile('0002-bar.md')->at($arch));

        $tester->execute($input);

        $this->assertContains('0001-foo.md', $tester->getDisplay());
        $this->assertContains('0002-bar.md', $tester->getDisplay());
    }
}