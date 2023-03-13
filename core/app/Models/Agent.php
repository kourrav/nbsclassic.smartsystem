<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
  protected $guarded = ['id'];
  protected $table = 'agents';
  protected $casts = [
    //  'email_verified_at' => 'datetime',
      'address' => 'object',
    //  'ver_code_send_at' => 'datetime'
  ];
}
