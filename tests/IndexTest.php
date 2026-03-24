<?php

use PHPUnit\Framework\TestCase;
use controller\GetAnnonce;
use Twig\Environment;
use Twig\TemplateWrapper;
use Mockery as m;

class IndexTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testGetAll()
    {
        // 1. Create mocks
        $annonce = m::mock('overload:model\Annonce');
        $annonceur = m::mock('overload:model\Annonceur');
        $photo = m::mock('overload:model\Photo');

        // 2. Define mock behavior
        $annoncesData = [];
        for ($i = 1; $i <= 12; $i++) {
            $annoncesData[] = (object)[
                'id_annonce' => $i,
                'id_annonceur' => $i,
                'nb_photo' => 1,
                'url_photo' => 'test.jpg',
                'nom_annonceur' => 'Test Annonceur ' . $i,
            ];
        }

        $annonce->shouldReceive('with->orderBy->take->get')->andReturn($annoncesData);
        $photo->shouldReceive('where->count')->andReturn(1);
        $photo->shouldReceive('select->where->first')->andReturn((object)['url_photo' => 'test.jpg']);
        $annonceur->shouldReceive('select->where->first')->andReturn((object)['nom_annonceur' => 'Test Annonceur']);

        // 3. Create controller and call method
        $indexController = new GetAnnonce();
        $indexController->getAll('');

        // 4. Assertions
        $this->assertCount(12, $indexController->getAnnonces());
    }

    public function testDisplayAllAnnonce()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);
        $annonce = m::mock('overload:model\Annonce');
        m::mock('overload:model\Photo');
        m::mock('overload:model\Annonceur');

        // 2. Define mock behavior
        $annonce->shouldReceive('with->orderBy->take->get')->andReturn([]);
        $twig->shouldReceive('load')->with('index.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $indexController = new GetAnnonce();
        $indexController->displayAllAnnonce($twig, [], '', []);
    }
}
