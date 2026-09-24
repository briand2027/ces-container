<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Container extends Model
{
    protected $table = 'conteneurs'; protected $guarded = [];
    protected $casts = ['caracteristiques'=>'array','images_secondaires'=>'array','certificat_csc'=>'boolean'];
    public function category() { return $this->belongsTo(Category::class, 'categorie_id'); }

    public function localizedDescription(): ?string
    {
        $locale = app()->getLocale();
        $column = $locale === 'it' ? 'description' : 'description_'.$locale;

        return $this->{$column} ?: $this->description;
    }
}
