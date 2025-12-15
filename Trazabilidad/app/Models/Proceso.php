<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Proceso extends Model {
    protected $table = 'Proceso';
    protected $primaryKey = 'IdProceso';
    public $timestamps = false;
    protected $fillable = ['Nombre'];
}
