<?php

use PHPUnit\Framework\TestCase;
use controller\getCategorie;
use Twig\Environment;
use Twig\TemplateWrapper;
use Mockery as m;

class GetCategorieTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testGetCategories()
    {
        // 1. Create mock
        $categorie = m::mock('overload:model\Categorie');

        // 2. Define mock behavior
        $categoriesData = [
            ['nom_categorie' => 'A'],
            ['nom_categorie' => 'B'],
            ['nom_categorie' => 'C'],
        ];
        $categorie->shouldReceive('orderBy->get->toArray')->andReturn($categoriesData);

        // 3. Create controller and call method
        $getCategorieController = new getCategorie();
        $categories = $getCategorieController->getCategories();

        // 4. Assertions
        $this->assertEquals('A', $categories[0]['nom_categorie']);
        $this->assertEquals('B', $categories[1]['nom_categorie']);
        $this->assertEquals('C', $categories[2]['nom_categorie']);
    }

    public function testGetCategorieContent()
    {
        // 1. Create mocks
        $annonce = m::mock('overload:model\Annonce');
        m::mock('overload:model\Photo');
        m::mock('overload:model\Annonceur');

        // 2. Define mock behavior
        $annonce->shouldReceive('with->orderBy->where->get')->andReturn([]);

        // 3. Create controller and call method
        $getCategorieController = new getCategorie();
        $getCategorieController->getCategorieContent('', 1);

        // 4. Assertions
        $this->assertIsArray($getCategorieController->getAnnonce());
    }

    public function testDisplayCategorie()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);
        $categorie = m::mock('overload:model\Categorie');
        $annonce = m::mock('overload:model\Annonce');
        m::mock('overload:model\Photo');
        m::mock('overload:model\Annonceur');

        // 2. Define mock behavior
        $categorie->shouldReceive('find')->with(1)->andReturn((object)['nom_categorie' => 'Test Categorie']);
        $annonce->shouldReceive('with->orderBy->where->get')->andReturn([]);
        $twig->shouldReceive('load')->with('index.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $getCategorieController = new getCategorie();
        $getCategorieController->displayCategorie($twig, [], '', [], 1);
    }
}
