<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ContactMessage extends Model {
    protected $table = 'messages_contact';
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = ['date_creation' => 'datetime', 'date_reponse' => 'datetime'];
}
