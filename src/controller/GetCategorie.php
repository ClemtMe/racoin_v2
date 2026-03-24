<?php

namespace controller;

use model\Categorie;
use model\Annonce;
use model\Photo;
use model\Annonceur;
use service\CategorieService;

class GetCategorie {

    protected $annonce = array();
    protected CategorieService $categorieService;

    public function __construct()
    {
        $this->categorieService = new CategorieService();
    }

    public function displayCategorie($twig, $menu, $chemin, $cat, $idCategorie) {
        $template = $twig->load("index.html.twig");
        $menu = array(
            array('href' => $chemin,
                'text' => 'Acceuil'),
            array('href' => $chemin."/cat/".$idCategorie,
                'text' => Categorie::find($idCategorie)->nom_categorie)
        );

        $this->annonce = $this->categorieService->getCategorieContent($chemin, $idCategorie);
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "categories" => $cat,
            "annonces" => $this->annonce));
    }

    public function getAnnonce()
    {
        return $this->annonce;
    }
}
