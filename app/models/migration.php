<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Migration extends Model {
    protected $table = 'migrations';
    protected $fillable = ['table', 'batch', 'note'];
}


