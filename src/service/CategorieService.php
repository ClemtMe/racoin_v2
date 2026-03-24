<?php

namespace service;

use model\Annonce;
use model\Annonceur;
use model\Categorie;
use model\Photo;

class CategorieService
{
    public function getCategorieContent($chemin, $idCategorie): array
    {
        $tmp = Annonce::with("Annonceur")->orderBy('id_annonce','desc')->where('id_categorie', "=", $idCategorie)->get();
        $annonce = [];
        foreach($tmp as $t) {
            $t->nb_photo = Photo::where("id_annonce", "=", $t->id_annonce)->count();
            if($t->nb_photo > 0){
                $t->url_photo = Photo::select("url_photo")
                    ->where("id_annonce", "=", $t->id_annonce)
                    ->first()->url_photo;
            }else{
                $t->url_photo = $chemin.'/img/noimg.png';
            }
            $t->nom_annonceur = Annonceur::select("nom_annonceur")
                ->where("id_annonceur", "=", $t->id_annonceur)
                ->first()->nom_annonceur;
            array_push($annonce, $t);
        }
        return $annonce;
    }

    public function getCategories()
    {
        return Categorie::orderBy('nom_categorie')->get()->toArray();
    }
}