<?php

use controller\getCategorie;
use controller\getDepartment;
use controller\index;
use controller\item;
use model\Annonce;
use model\Annonceur;
use model\Categorie;
use model\Departement;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function(App $app, $twig, $menu, $chemin) {
    $cat = new getCategorie();
    $dpt = new getDepartment();

    $app->get('/', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
        $index = new index();
        $index->displayAllAnnonce($twig, $menu, $chemin, $cat->getCategories());
        return $response;
    });

    $app->get('/item/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
        $n    = $args['n'];
        $item = new item();
        $item->afficherItem($twig, $menu, $chemin, $n, $cat->getCategories());
        return $response;
    });

    $app->get('/add', function (Request $request, Response $response, $args) use ($twig, $app, $menu, $chemin, $cat, $dpt) {
        $ajout = new controller\addItem();
        $ajout->addItemView($twig, $menu, $chemin, $cat->getCategories(), $dpt->getAllDepartments());
        return $response;
    });

    $app->post('/add', function (Request $request, Response $response, $args) use ($twig, $app, $menu, $chemin) {
        $allPostVars = $request->getParsedBody();
        $ajout       = new controller\addItem();
        $ajout->addNewItem($twig, $menu, $chemin, $allPostVars);
        return $response;
    });

    $app->get('/item/{id}/edit', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin) {
        $id   = $args['id'];
        $item = new item();
        $item->modifyGet($twig, $menu, $chemin, $id);
        return $response;
    });
    $app->post('/item/{id}/edit', function (Request $request, Response $response, $args) use ($twig, $app, $menu, $chemin, $cat, $dpt) {
        $id          = $args['id'];
        $allPostVars = $request->getParsedBody();
        $item        = new item();
        $item->modifyPost($twig, $menu, $chemin, $id, $allPostVars, $cat->getCategories(), $dpt->getAllDepartments());
        return $response;
    });

    $app->map(['GET', 'POST'], '/item/{id}/confirm', function (Request $request, Response $response, $args) use ($twig, $app, $menu, $chemin) {
        $id          = $args['id'];
        $allPostVars = $request->getParsedBody();
        $item        = new item();
        $item->edit($twig, $menu, $chemin, $id, $allPostVars);
        return $response;
    });

    $app->get('/search', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
        $s = new controller\Search();
        $s->show($twig, $menu, $chemin, $cat->getCategories());
        return $response;
    });


    $app->post('/search', function (Request $request, Response $response) use ($app, $twig, $menu, $chemin, $cat) {
        $array = $request->getParsedBody();
        $s     = new controller\Search();
        $s->research($array, $twig, $menu, $chemin, $cat->getCategories());
        return $response;

    });

    $app->get('/annonceur/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
        $n         = $args['n'];
        $annonceur = new controller\viewAnnonceur();
        $annonceur->afficherAnnonceur($twig, $menu, $chemin, $n, $cat->getCategories());
        return $response;
    });

    $app->get('/del/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin) {
        $n    = $args['n'];
        $item = new controller\item();
        $item->supprimerItemGet($twig, $menu, $chemin, $n);
        return $response;
    });

    $app->post('/del/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
        $n    = $args['n'];
        $item = new controller\item();
        $item->supprimerItemPost($twig, $menu, $chemin, $n, $cat->getCategories());
        return $response;
    });

    $app->get('/cat/{n}', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
        $n         = $args['n'];
        $categorie = new controller\getCategorie();
        $categorie->displayCategorie($twig, $menu, $chemin, $cat->getCategories(), $n);
        return $response;
    });

    $app->get('/api(/)', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
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

    $app->group('/api', function (RouteCollectorProxy $group) use ($twig, $menu, $chemin, $cat) {

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

        $group->get('/key', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
            $kg = new controller\KeyGenerator();
            $kg->show($twig, $menu, $chemin, $cat->getCategories());
            return $response;
        });

        $group->post('/key', function (Request $request, Response $response, $args) use ($twig, $menu, $chemin, $cat) {
            $nom = $_POST['nom'];

            $kg = new controller\KeyGenerator();
            $kg->generateKey($twig, $menu, $chemin, $cat->getCategories(), $nom);
            return $response;
        });
    });
};
