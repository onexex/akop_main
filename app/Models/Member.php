<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $table = 'members';

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'suffix',
        'address_id',
        'taggedBy',
        'isTagged',
        'last_login_lat',
        'last_login_long',
    ];

    protected $casts = [
        'isTagged' => 'boolean',
        'last_login_lat' => 'float',
        'last_login_long' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships (Optional)
    |--------------------------------------------------------------------------
    */

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function taggedByUser()
    {
        return $this->belongsTo(User::class, 'taggedBy');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Optional)
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return trim(collect([
            $this->firstname,
            $this->middlename,
            $this->lastname,
            $this->suffix,
        ])->filter()->implode(' '));
    }
}
