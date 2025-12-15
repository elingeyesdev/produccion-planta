<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Operador extends Model {
    protected $table = 'Operador';
    protected $primaryKey = 'IdOperador';
    public $timestamps = false;
    protected $fillable = ['Nombre', 'Cargo', 'Usuario', 'PasswordHash', 'Email'];
}
