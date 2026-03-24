<?php

use PHPUnit\Framework\TestCase;
use controller\KeyGenerator;
use Twig\Environment;
use Mockery as m;

class KeyGeneratorTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testShow()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(\Twig\Template::class);

        // 2. Define mock behavior
        $twig->shouldReceive('load')->with('key-generator.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $keyGeneratorController = new KeyGenerator();
        $keyGeneratorController->show($twig, [], '', []);
    }

    public function testGenerateKeySuccess()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(\Twig\Template::class);
        $apiKey = m::mock('overload:model\ApiKey');

        // 2. Define mock behavior
        $apiKeyInstance = m::mock('stdClass');
        $apiKeyInstance->shouldReceive('save')->andReturn(true);
        $apiKey->shouldReceive('newInstance')->andReturn($apiKeyInstance);

        $twig->shouldReceive('load')->with('key-generator-result.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $keyGeneratorController = new KeyGenerator();
        $keyGeneratorController->generateKey($twig, [], '', [], 'Test Key');
    }

    public function testGenerateKeyError()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(\Twig\Template::class);

        // 2. Define mock behavior
        $twig->shouldReceive('load')->with('key-generator-error.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $keyGeneratorController = new KeyGenerator();
        $keyGeneratorController->generateKey($twig, [], '', [], '');
    }
}
