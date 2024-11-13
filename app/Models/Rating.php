<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    protected $table = 'tb_rating';

    protected $primaryKey = 'id_rating';
    public $timestamps = false;

    protected $fillable=[
        'rating',
    ];

    public function ulasan()
    {
        return $this->hasMany(Ulasan::class, 'id_rating');
    }

}
