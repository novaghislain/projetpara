<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un employé dans le module ERP (RH).
 *
 * Table associée : `erp_employees` (via convention Laravel)
 *
 * Relations :
 * - Un employé peut avoir plusieurs fiches de paie (ErpPayroll)
 */
class ErpEmployee extends Model {
    use SoftDeletes;
    protected $fillable = ['matricule','first_name','last_name','position','phone','base_salary','cnss_number','ifu_number','hire_date','status'];
    protected $casts = ['base_salary'=>'decimal:2','hire_date'=>'date'];
    public function payrolls() { return $this->hasMany(ErpPayroll::class); }
}
