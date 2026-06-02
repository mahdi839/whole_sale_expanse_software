<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'documents',
        'employment_type',
        'joining_date',
        'salary_amount',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary_amount' => 'decimal:2',
    ];

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function salaryAdvances()
    {
        return $this->hasMany(SalaryAdvance::class);
    }
}
