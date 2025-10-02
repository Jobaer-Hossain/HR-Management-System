<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $table = 'leaves';         
    protected $primaryKey = 'leave_id';
    public $timestamps = false;

    protected $fillable = [
        'emp_id', 
        'start_date', 
        'end_date', 
        'reason', 
        'status'
    ];
}
