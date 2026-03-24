<?php

use controller\GetAnnonce;
use controller\ItemController;
use model\Annonce;
use model\Annonceur;
use model\Categorie;
use model\Departement;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use service\DepartmentService;
use service\CategorieService;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function(App $app, $twig, $menu, $chemin) {
    $categorieService = new CategorieService();
    $departmentService = new DepartmentService();

    $app->get('/', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $categorieService) {
        $index = new GetAnnonce();
        $index->displayAllAnnonce($twig, $menu, $chemin, $categorieService->getCategories());
        return $response;
    });

    $app->get('/item/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $categorieService) {
        $n    = $args['n'];
        $item = new ItemController();
        $item->afficherItem($twig, $menu, $chemin, $n, $categorieService->getCategories());
        return $response;
    });

    $app->get('/add', function (Request $request, Response $response, $args) use ($twig, $app, $menu, $chemin, $categorieService, $departmentService) {
        $ajout = new controller\PostItem();
        $ajout->addItemView($twig, $menu, $chemin, $categorieService->getCategories(), $departmentService->getAllDepartments());
        return $response;
    });

    $app->post('/add', function (Request $request, Response $response, $args) use ($twig, $app, $menu, $chemin) {
        $allPostVars = $request->getParsedBody();
        $ajout       = new controller\PostItem();
        $ajout->addNewItem($twig, $menu, $chemin, $allPostVars);
        return $response;
    });

    $app->get('/item/{id}/edit', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin) {
        $id   = $args['id'];
        $item = new ItemController();
        $item->modifyGet($twig, $menu, $chemin, $id);
        return $response;
    });
    $app->post('/item/{id}/edit', function (Request $request, Response $response, $args) use ($twig, $app, $menu, $chemin, $categorieService, $departmentService) {
        $id          = $args['id'];
        $allPostVars = $request->getParsedBody();
        $item        = new ItemController();
        $item->modifyPost($twig, $menu, $chemin, $id, $allPostVars, $categorieService->getCategories(), $departmentService->getAllDepartments());
        return $response;
    });

    $app->map(['GET', 'POST'], '/item/{id}/confirm', function (Request $request, Response $response, $args) use ($twig, $app, $menu, $chemin) {
        $id          = $args['id'];
        $allPostVars = $request->getParsedBody();
        $item        = new ItemController();
        $item->edit($twig, $menu, $chemin, $id, $allPostVars);
        return $response;
    });

    $app->get('/search', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $categorieService) {
        $s = new controller\Search();
        $s->show($twig, $menu, $chemin, $categorieService->getCategories());
        return $response;
    });


    $app->post('/search', function (Request $request, Response $response) use ($app, $twig, $menu, $chemin, $categorieService) {
        $array = $request->getParsedBody();
        $s     = new controller\Search();
        $s->research($array, $twig, $menu, $chemin, $categorieService->getCategories());
        return $response;

    });

    $app->get('/annonceur/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $categorieService) {
        $n         = $args['n'];
        $annonceur = new controller\GetAnnonceur();
        $annonceur->afficherAnnonceur($twig, $menu, $chemin, $n, $categorieService->getCategories());
        return $response;
    });

    $app->get('/del/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin) {
        $n    = $args['n'];
        $item = new controller\ItemController();
        $item->supprimerItemGet($twig, $menu, $chemin, $n);
        return $response;
    });

    $app->post('/del/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $categorieService) {
        $n    = $args['n'];
        $item = new controller\ItemController();
        $item->supprimerItemPost($twig, $menu, $chemin, $n, $categorieService->getCategories());
        return $response;
    });

    $app->get('/cat/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $categorieService) {
        $n         = $args['n'];
        $categorie = new controller\GetCategorie();
        $categorie->displayCategorie($twig, $menu, $chemin, $categorieService->getCategories(), $n);
        return $response;
    });

    $app->get('/api(/)', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $categorieService) {
        $template = $twig->load('api.html.twig');
        $menu     = array(
            array(
                'href' => $chemin,
                'text' => 'Acceuil'
            ),
            array(
                'href' => $chemin . '/api',
                'text' => 'Api'
            )
        );
        $response->getBody()->write($template->render(array('breadcrumb' => $menu, 'chemin' => $chemin)));
        return $response;
    });

    $app->group('/api', function (RouteCollectorProxy $group) use ($twig, $menu, $chemin, $categorieService) {

        $group->group('/annonce', function (RouteCollectorProxy $group) {

            $group->get('/{id}', function (Request $request, Response $response, $args) {
                $id          = $args['id'];
                $annonceList = ['id_annonce', 'id_categorie as categorie', 'id_annonceur as annonceur', 'id_departement as departement', 'prix', 'date', 'titre', 'description', 'ville'];
                $return      = Annonce::select($annonceList)->find($id);

                if (isset($return)) {
                    $response              = $response->withHeader('Content-Type', 'application/json');
                    $return->categorie     = Categorie::find($return->categorie);
                    $return->annonceur     = Annonceur::select('email', 'nom_annonceur', 'telephone')
                                                      ->find($return->annonceur);
                    $return->departement   = Departement::select('id_departement', 'nom_departement')->find($return->departement);
                    $links                 = [];
                    $links['self']['href'] = '/api/annonce/' . $return->id_annonce;
                    $return->links         = $links;
                    $response->getBody()->write($return->toJson());
                } else {
                    $response = $response->withStatus(404);
                }
                return $response;
            });
        });

        $group->group('/annonces(/)', function (RouteCollectorProxy $group) {

            $group->get('/', function (Request $request, Response $response) {
                $annonceList = ['id_annonce', 'prix', 'titre', 'ville'];
                $response    = $response->withHeader('Content-Type', 'application/json');
                $a           = Annonce::all($annonceList);
                $links       = [];
                foreach ($a as $ann) {
                    $links['self']['href'] = '/api/annonce/' . $ann->id_annonce;
                    $ann->links            = $links;
                }
                $links['self']['href'] = '/api/annonces/';
                $a->links              = $links;
                $response->getBody()->write($a->toJson());
                return $response;
            });
        });


        $group->group('/categorie', function (RouteCollectorProxy $group) {

            $group->get('/{id}', function (Request $request, Response $response, $args) {
                $id       = $args['id'];
                $response = $response->withHeader('Content-Type', 'application/json');
                $a        = Annonce::select('id_annonce', 'prix', 'titre', 'ville')
                                   ->where('id_categorie', '=', $id)
                                   ->get();
                $links    = [];

                foreach ($a as $ann) {
                    $links['self']['href'] = '/api/annonce/' . $ann->id_annonce;
                    $ann->links            = $links;
                }

                $c                     = Categorie::find($id);
                $links['self']['href'] = '/api/categorie/' . $id;
                $c->links              = $links;
                $c->annonces           = $a;
                $response->getBody()->write($c->toJson());
                return $response;
            });
        });

        $group->group('/categories(/)', function (RouteCollectorProxy $group) {
            $group->get('/', function (Request $request, Response $response, $args) {
                $response = $response->withHeader('Content-Type', 'application/json');
                $c        = Categorie::get();
                $links    = [];
                foreach ($c as $cat) {
                    $links['self']['href'] = '/api/categorie/' . $cat->id_categorie;
                    $cat->links            = $links;
                }
                $links['self']['href'] = '/api/categories/';
                $c->links              = $links;
                $response->getBody()->write($c->toJson());
                return $response;
            });
        });

        $group->get('/key', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $categorieService) {
            $kg = new controller\KeyGenerator();
            $kg->show($twig, $menu, $chemin, $categorieService->getCategories());
            return $response;
        });

        $group->post('/key', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $categorieService) {
            $nom = $_POST['nom'];

            $kg = new controller\KeyGenerator();
            $kg->generateKey($twig, $menu, $chemin, $categorieService->getCategories(), $nom);
            return $response;
        });
    });
};
