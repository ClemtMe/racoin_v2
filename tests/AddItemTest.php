<?php

use PHPUnit\Framework\TestCase;
use controller\PostItem;
use Twig\Environment;
use Twig\TemplateWrapper;
use Mockery as m;

class AddItemTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testAddItemView()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);

        // 2. Define mock behavior
        $twig->shouldReceive('load')->with('add.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $addItemController = new PostItem();
        $addItemController->addItemView($twig, [], '', [], []);
    }

    public function testAddNewItemSuccess()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);
        m::mock('overload:model\Annonce');
        $annonceur = m::mock('overload:model\Annonceur');

        // 2. Define mock behavior
        $_POST = [
            'nom' => 'Test User',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'ville' => 'Test Ville',
            'departement' => 1,
            'categorie' => 1,
            'title' => 'Test Title',
            'description' => 'Test Description',
            'price' => 10,
            'psw' => 'password',
            'confirm-psw' => 'password',
        ];

        $annonceurInstance = m::mock(\model\Annonceur::class);
        $annonceurInstance->shouldReceive('save')->once()->andReturn(true);
        $annonceurInstance->shouldReceive('annonce->save')->once()->andReturn(true);
        
        $annonceur->shouldReceive('newInstance')->once()->andReturn($annonceurInstance);

        $twig->shouldReceive('load')->with('add-confirm.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $addItemController = new PostItem();
        $addItemController->addNewItem($twig, [], '', $_POST);
    }

    public function testAddNewItemError()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);

        // 2. Define mock behavior
        $_POST = [
            'nom' => '',
            'email' => 'invalid-email',
            'phone' => 'not-a-number',
            'ville' => '',
            'departement' => 'not-a-number',
            'categorie' => 'not-a-number',
            'title' => '',
            'description' => '',
            'price' => 'not-a-number',
            'psw' => 'password',
            'confirm-psw' => 'wrong-password',
        ];

        $twig->shouldReceive('load')->with('add-error.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once()->andReturnUsing(function ($data) {
            $this->assertCount(10, $data['errors']);
        });

        // 3. Create controller and call method
        $addItemController = new PostItem();
        $addItemController->addNewItem($twig, [], '', $_POST);
    }
}
