<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Doc — persistent storage for markdown documentation.
 */
class Doc extends Model
{
    use HasFactory;

    protected $fillable = [ 'slug', 'title', 'body' ];
}
