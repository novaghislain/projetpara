<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une fiche de paie dans le module ERP.
 *
 * Table associée : `erp_payrolls` (via convention Laravel)
 *
 * Relations :
 * - Une fiche de paie appartient à un employé (ErpEmployee)
 */
class ErpPayroll extends Model {
    protected $fillable = ['erp_employee_id','period','base_salary','bonuses','deductions','advances','net_salary','status'];
    protected $casts = ['base_salary'=>'decimal:2','bonuses'=>'decimal:2','deductions'=>'decimal:2','advances'=>'decimal:2','net_salary'=>'decimal:2'];
    public function employee() { return $this->belongsTo(ErpEmployee::class, 'erp_employee_id'); }
}
