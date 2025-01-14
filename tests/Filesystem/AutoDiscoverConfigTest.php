<?php declare(strict_types=1);

namespace ADR\Filesystem;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class AutoDiscoverConfigTest extends TestCase
{
    private vfsStreamDirectory $vfs;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->vfs = vfsStream::setup();
        $this->filesystem = new Filesystem();
        $this->filesystem->copy('adr.yml.dist', $this->vfs->url() . '/adr.yml.dist');
    }

    private function getService(): AutoDiscoverConfig
    {
        return new AutoDiscoverConfig(
            $this->vfs->url(),
        );
    }

    public function testGetConfigReturnsDefaultYaml(): void
    {
        $config = $this->getService()->getConfig();

        $this->assertSame($this->vfs->url() . '/adr.yml.dist', $config);
    }

    public function testGetConfigReturnsPassedValue(): void
    {
        $config = $this->getService()->getConfig($this->vfs->url() . '/adr.yml.dist');

        $this->assertSame($this->vfs->url() . '/adr.yml.dist', $config);
    }

    public function testGetConfigReturnsEmptyIfPassedValueDoesNotExist(): void
    {
        $this->expectExceptionObject(new FileNotFoundException('Config file "does-not-exist.yml" does not exists'));
        $config = $this->getService()->getConfig('does-not-exist.yml');

        $this->assertSame('', $config);
    }

    public function testGetConfigCustom(): void
    {
        // setup
        $customConfig = 'adr.yml';
        $configContent = file_get_contents('adr.yml.dist');
        $configContent = str_replace('docs/arch', '/custom_path', $configContent);
        file_put_contents($this->vfs->url() . '/' . $customConfig, $configContent);

        $config = $this->getService()->getConfig(null);
        $this->assertSame($this->vfs->url() . '/adr.yml', $config);
    }

    protected function tearDown(): void
    {
        if ($this->filesystem->exists('adr.yml')) {
            $this->filesystem->remove('adr.yml');
        }
    }
}
