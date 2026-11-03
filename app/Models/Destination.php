<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;
    protected $fillable = ['nom', 'description', 'image'];
/**
     *
     * @return bool
     */
    public function voyages()
    {
        return $this->hasMany(Voyage::class);
    }
}

