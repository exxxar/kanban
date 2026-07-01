<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'action_type',
        'title',
        'description',
        'metadata',
        'user_name'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
