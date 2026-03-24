<?php

use PHPUnit\Framework\TestCase;
use controller\Search;
use Twig\Environment;
use Mockery as m;

class SearchTest extends TestCase
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
        $twig->shouldReceive('load')->with('search.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $searchController = new Search();
        $searchController->show($twig, [], '', []);
    }

    public function testResearchNoParams()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(\Twig\Template::class);
        $annonce = m::mock('overload:model\Annonce');

        // 2. Define mock behavior
        $annonce->shouldReceive('all')->andReturn([]);
        $twig->shouldReceive('load')->with('index.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $searchController = new Search();
        $params = [
            'motclef' => '',
            'codepostal' => '',
            'categorie' => 'Toutes catégories',
            'prix-min' => 'Min',
            'prix-max' => 'Max',
        ];
        $searchController->research($params, $twig, [], '', []);
    }

    public function testResearchWithKeyword()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(\Twig\Template::class);
        $annonce = m::mock('overload:model\Annonce');
        $query = m::mock('stdClass');

        // 2. Define mock behavior
        $annonce->shouldReceive('select')->andReturn($query);
        $query->shouldReceive('where')->with('description', 'like', '%keyword%')->andReturnSelf();
        $query->shouldReceive('get')->andReturn([]);
        $twig->shouldReceive('load')->with('index.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $searchController = new Search();
        $params = [
            'motclef' => 'keyword',
            'codepostal' => '',
            'categorie' => 'Toutes catégories',
            'prix-min' => 'Min',
            'prix-max' => 'Max',
        ];
        $searchController->research($params, $twig, [], '', []);
    }

    public function testResearchWithPostalCode()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(\Twig\Template::class);
        $annonce = m::mock('overload:model\Annonce');
        $query = m::mock('stdClass');

        // 2. Define mock behavior
        $annonce->shouldReceive('select')->andReturn($query);
        $query->shouldReceive('where')->with('ville', '=', '75001')->andReturnSelf();
        $query->shouldReceive('get')->andReturn([]);
        $twig->shouldReceive('load')->with('index.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $searchController = new Search();
        $params = [
            'motclef' => '',
            'codepostal' => '75001',
            'categorie' => 'Toutes catégories',
            'prix-min' => 'Min',
            'prix-max' => 'Max',
        ];
        $searchController->research($params, $twig, [], '', []);
    }

    public function testResearchWithCategory()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(\Twig\Template::class);
        $annonce = m::mock('overload:model\Annonce');
        $categorie = m::mock('overload:model\Categorie');
        $query = m::mock('stdClass');

        // 2. Define mock behavior
        $categorie->shouldReceive('select->where->first')->andReturn((object)['id_categorie' => 1]);
        $annonce->shouldReceive('select')->andReturn($query);
        $query->shouldReceive('where')->with('id_categorie', '=', 1)->andReturnSelf();
        $query->shouldReceive('get')->andReturn([]);
        $twig->shouldReceive('load')->with('index.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $searchController = new Search();
        $params = [
            'motclef' => '',
            'codepostal' => '',
            'categorie' => 1,
            'prix-min' => 'Min',
            'prix-max' => 'Max',
        ];
        $searchController->research($params, $twig, [], '', []);
    }

    public function testResearchWithPriceRange()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(\Twig\Template::class);
        $annonce = m::mock('overload:model\Annonce');
        $query = m::mock('stdClass');

        // 2. Define mock behavior
        $annonce->shouldReceive('select')->andReturn($query);
        $query->shouldReceive('whereBetween')->with('prix', [15, 25])->andReturnSelf();
        $query->shouldReceive('get')->andReturn([]);
        $twig->shouldReceive('load')->with('index.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $searchController = new Search();
        $params = [
            'motclef' => '',
            'codepostal' => '',
            'categorie' => 'Toutes catégories',
            'prix-min' => '15',
            'prix-max' => '25',
        ];
        $searchController->research($params, $twig, [], '', []);
    }
}
