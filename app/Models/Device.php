<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\User;


class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'marca',
        'serial',
        'foto_serial',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFotoSerialUrlAttribute()
    {
        if (!$this->foto_serial) {
            return null;
        }
        return Storage::url($this->foto_serial);
    }
}