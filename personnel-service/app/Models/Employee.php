<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'department',
    ];

    public static function normalizeForSearch(string $value): string
    {
        $value = strtr($value, [
            'İ' => 'I', 'ı' => 'i',
            'Ğ' => 'G', 'ğ' => 'g',
            'Ü' => 'U', 'ü' => 'u',
            'Ö' => 'O', 'ö' => 'o',
            'Ş' => 'S', 'ş' => 's',
            'Ç' => 'C', 'ç' => 'c',
        ]);

        return mb_strtolower(Str::ascii($value));
    }

    protected static function booted(): void
    {
        static::saving(function (self $employee): void {
            foreach (['first_name', 'last_name', 'email', 'department'] as $field) {
                $employee->{$field.'_search'} = self::normalizeForSearch((string) $employee->{$field});
            }
        });
    }
}
