<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ErpPayroll extends Model {
    protected $fillable = ['erp_employee_id','period','base_salary','bonuses','deductions','advances','net_salary','status'];
    protected $casts = ['base_salary'=>'decimal:2','bonuses'=>'decimal:2','deductions'=>'decimal:2','advances'=>'decimal:2','net_salary'=>'decimal:2'];
    public function employee() { return $this->belongsTo(ErpEmployee::class, 'erp_employee_id'); }
}
