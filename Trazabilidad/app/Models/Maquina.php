<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Maquina extends Model {
    protected $table = 'Maquina';
    protected $primaryKey = 'IdMaquina';
    public $timestamps = false;
    protected $fillable = ['Nombre', 'ImagenUrl'];
}
