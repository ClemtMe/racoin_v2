<?php

namespace model;

class Photo extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'photo';
    protected $primaryKey = 'id_photo';
    public $timestamps = false;

    protected $fillable = [
        'id_annonce',
        'url_photo',
    ];

    public function annonce()
    {
        return $this->belongsTo('model\Annonce', 'id_annonce');
    }
}

?>