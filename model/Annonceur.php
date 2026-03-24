<?php

namespace model;

class Annonceur extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'annonceur';
    protected $primaryKey = 'id_annonceur';
    public $timestamps = false;

    protected $fillable = [
        'nom_annonceur',
        'email',
        'telephone',
    ];

    public function annonce()
    {
        return $this->hasMany('model\Annonce', 'id_annonceur');
    }
}
