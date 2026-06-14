<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpEmployee extends Model {
    use SoftDeletes;
    protected $fillable = ['matricule','first_name','last_name','position','phone','base_salary','cnss_number','ifu_number','hire_date','status'];
    protected $casts = ['base_salary'=>'decimal:2','hire_date'=>'date'];
    public function payrolls() { return $this->hasMany(ErpPayroll::class); }
}
