<?php

namespace model;

class Departement extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'departement';
    protected $primaryKey = 'id_departement';
    public $timestamps = false;

    protected $fillable = [
        'nom_departement',
    ];
}

?>