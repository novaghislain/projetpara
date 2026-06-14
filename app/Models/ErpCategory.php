<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpCategory extends Model {
    use SoftDeletes;
    protected $fillable = ['name','type','description'];
    public function items() { return $this->hasMany(ErpItem::class); }
}
