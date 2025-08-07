<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_permanent',
        'nom',
        'universite',
        'specialite',
        'nbreEmprunts',
    ];

    public function emprunts()
    {
        return $this->hasMany(Emprunt::class);
    }
}
