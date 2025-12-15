<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Proveedor extends Model {
    protected $table = 'Proveedor';
    protected $primaryKey = 'IdProveedor';
    public $timestamps = false;
    protected $fillable = ['Nombre', 'Contacto', 'Telefono', 'Email', 'Direccion'];
}
