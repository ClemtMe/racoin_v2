<?php

namespace controller;

use service\AnnonceService;

class GetAnnonce
{
    protected $annonce = array();
    protected AnnonceService $annonceService;

    public function __construct()
    {
        $this->annonceService = new AnnonceService();
    }

    public function displayAllAnnonce($twig, $menu, $chemin, $cat)
    {
        $template = $twig->load("index.html.twig");
        $menu     = array(
            array(
                'href' => $chemin,
                'text' => 'Acceuil'
            ),
        );

        $this->annonce = $this->annonceService->getAllAnnonces($chemin);
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "categories" => $cat,
            "annonces"   => $this->annonce
        ));
    }

    public function getAnnonces()
    {
        return $this->annonce;
    }
}
