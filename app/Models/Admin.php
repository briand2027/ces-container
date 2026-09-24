<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table = 'administrateurs';
    protected $guarded = [];
    protected $hidden = ['mot_de_passe'];
    public function getAuthPassword(): string { return $this->mot_de_passe; }
}
