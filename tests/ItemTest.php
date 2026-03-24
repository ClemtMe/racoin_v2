<?php

use PHPUnit\Framework\TestCase;
use controller\ItemController;
use Twig\Environment;
use Twig\TemplateWrapper;
use Mockery as m;

class ItemTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testAfficherItem()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);
        $annonce = m::mock('overload:model\Annonce');
        $annonceur = m::mock('overload:model\Annonceur');
        $departement = m::mock('overload:model\Departement');
        $photo = m::mock('overload:model\Photo');
        $categorie = m::mock('overload:model\Categorie');

        // 2. Define mock behavior
        $annonceData = (object)[
            'id_annonce' => 1,
            'id_categorie' => 1,
            'id_annonceur' => 1,
            'id_departement' => 1,
            'titre' => 'Test Annonce'
        ];
        $annonceurData = (object)['nom_annonceur' => 'Test Annonceur'];
        $departementData = (object)['nom_departement' => 'Test Departement'];
        $categorieData = (object)['nom_categorie' => 'Test Categorie'];

        $annonce->shouldReceive('find')->with(1)->andReturn($annonceData);
        $annonceur->shouldReceive('find')->with(1)->andReturn($annonceurData);
        $departement->shouldReceive('find')->with(1)->andReturn($departementData);
        $categorie->shouldReceive('find')->with(1)->andReturn($categorieData);
        $photo->shouldReceive('where->get')->andReturn([]);

        $twig->shouldReceive('load')->with('item.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once()->andReturnUsing(function ($data) {
            $this->assertEquals('Test Annonce', $data['annonce']->titre);
            $this->assertEquals('Test Annonceur', $data['annonceur']->nom_annonceur);
            $this->assertEquals('Test Departement', $data['dep']);
        });

        // 3. Create controller and call method
        $itemController = new ItemController();
        $itemController->afficherItem($twig, [], '', 1, []);
    }

    public function testSupprimerItemGet()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);
        $annonce = m::mock('overload:model\Annonce');

        // 2. Define mock behavior
        $annonceData = (object)['id_annonce' => 1, 'titre' => 'Test Annonce'];
        $annonce->shouldReceive('find')->with(1)->andReturn($annonceData);
        $twig->shouldReceive('load')->with('delGet.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once()->andReturnUsing(function ($data) {
            $this->assertEquals('Test Annonce', $data['annonce']->titre);
        });

        // 3. Create controller and call method
        $itemController = new ItemController();
        $itemController->supprimerItemGet($twig, [], '', 1);
    }

    public function testSupprimerItemPost()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);
        $annonce = m::mock('overload:model\Annonce');
        $photo = m::mock('overload:model\Photo');

        // 2. Define mock behavior
        $password = 'password';
        $annonceData = m::mock('stdClass');
        $annonceData->mdp = password_hash($password, PASSWORD_DEFAULT);
        $annonceData->shouldReceive('delete')->andReturn(true);
        $_POST['pass'] = $password;

        $annonce->shouldReceive('find')->with(1)->andReturn($annonceData);
        $photo->shouldReceive('where->delete')->andReturn(true);

        $twig->shouldReceive('load')->with('delPost.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once()->andReturnUsing(function ($data) {
            $this->assertTrue($data['pass']);
        });

        // 3. Create controller and call method
        $itemController = new ItemController();
        $itemController->supprimerItemPost($twig, [], '', 1, []);
    }

    public function testModifyGet()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);
        $annonce = m::mock('overload:model\Annonce');

        // 2. Define mock behavior
        $annonceData = (object)['id_annonce' => 1, 'titre' => 'Test Annonce'];
        $annonce->shouldReceive('find')->with(1)->andReturn($annonceData);
        $twig->shouldReceive('load')->with('modifyGet.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once()->andReturnUsing(function ($data) {
            $this->assertEquals('Test Annonce', $data['annonce']->titre);
        });

        // 3. Create controller and call method
        $itemController = new ItemController();
        $itemController->modifyGet($twig, [], '', 1);
    }

    public function testModifyPost()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);
        $annonce = m::mock('overload:model\Annonce');
        $annonceur = m::mock('overload:model\Annonceur');
        $departement = m::mock('overload:model\Departement');
        $categorie = m::mock('overload:model\Categorie');

        // 2. Define mock behavior
        $password = 'password';
        $annonceData = (object)[
            'id_annonce' => 1,
            'id_categorie' => 1,
            'id_annonceur' => 1,
            'id_departement' => 1,
            'titre' => 'Test Annonce',
            'mdp' => password_hash($password, PASSWORD_DEFAULT),
        ];
        $annonceurData = (object)['nom_annonceur' => 'Test Annonceur'];
        $departementData = (object)['nom_departement' => 'Test Departement'];
        $categorieData = (object)['nom_categorie' => 'Test Categorie'];
        $_POST['pass'] = $password;

        $annonce->shouldReceive('find')->with(1)->andReturn($annonceData);
        $annonceur->shouldReceive('find')->with(1)->andReturn($annonceurData);
        $departement->shouldReceive('find')->with(1)->andReturn($departementData);
        $categorie->shouldReceive('find')->with(1)->andReturn($categorieData);

        $twig->shouldReceive('load')->with('modifyPost.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once()->andReturnUsing(function ($data) {
            $this->assertTrue($data['pass']);
            $this->assertEquals('Test Annonce', $data['annonce']->titre);
            $this->assertEquals('Test Annonceur', $data['annonceur']->nom_annonceur);
            $this->assertEquals('Test Departement', $data['dptItem']);
            $this->assertEquals('Test Categorie', $data['categItem']);
        });

        // 3. Create controller and call method
        $itemController = new ItemController();
        $itemController->modifyPost($twig, [], '', 1, [], [], []);
    }

    public function testEditSuccess()
    {
        // 1. Create mocks
        $twig = m::mock(Environment::class);
        $template = m::mock(TemplateWrapper::class);
        $annonce = m::mock('overload:model\Annonce');
        $annonceur = m::mock('overload:model\Annonceur');

        // 2. Define mock behavior
        $_POST = [
            'nom' => 'New Name',
            'email' => 'new@email.com',
            'phone' => '0987654321',
            'ville' => 'New Ville',
            'departement' => 1,
            'categorie' => 1,
            'title' => 'New Title',
            'description' => 'New Description',
            'price' => '20.00',
            'psw' => 'new_password',
        ];
        
        $annonceData = m::mock('stdClass');
        $annonceData->shouldReceive('save')->andReturn(true);
        $annonceData->id_annonceur = 1;
        
        $annonceurData = m::mock('stdClass');
        $annonceurData->shouldReceive('save')->andReturn(true);
        $annonceurData->shouldReceive('annonce->save')->with($annonceData)->andReturn(true);


        $annonce->shouldReceive('find')->with(1)->andReturn($annonceData);
        $annonceur->shouldReceive('find')->with(1)->andReturn($annonceurData);

        $twig->shouldReceive('load')->with('modif-confirm.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once();

        // 3. Create controller and call method
        $itemController = new ItemController();
        $itemController->edit($twig, [], '', $_POST, 1);
    }

    public function testEditError()
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
            'psw' => '',
        ];

        $twig->shouldReceive('load')->with('add-error.html.twig')->andReturn($template);
        $template->shouldReceive('render')->once()->andReturnUsing(function ($data) {
            $this->assertCount(9, $data['errors']);
        });

        // 3. Create controller and call method
        $itemController = new ItemController();
        $itemController->edit($twig, [], '', $_POST, 1);
    }
}
