<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Category extends Model
{
    protected $table = 'categories'; protected $guarded = [];
    public function containers() { return $this->hasMany(Container::class, 'categorie_id'); }

    public function translationKey(): string
    {
        $name = strtolower((string) $this->nom);

        return match (true) {
            str_contains($name, 'nuovi') => 'category_new_containers',
            str_contains($name, 'usati') => 'category_used_containers',
            str_contains($name, 'noleggio') => 'category_rental_containers',
            str_contains($name, 'cabine') => 'category_portable_cabins',
            str_contains($name, 'accessori') => 'category_accessories',
            str_contains($name, 'attrezzature') => 'category_equipment',
            str_contains($name, 'casa mobile') || str_contains($name, 'case modulari') => 'category_modular_homes',
            default => 'category_generic',
        };
    }

    public function localizedName(): string
    {
        $translated = __($this->translationKey());

        return $translated === $this->translationKey() ? $this->nom : $translated;
    }

    public function localizedDescription(): ?string
    {
        $key = $this->translationKey().'_desc';
        $translated = __($key);

        return $translated === $key ? $this->description : $translated;
    }
}
