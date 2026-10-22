<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class system_settings_tbl extends Model
{
    protected $table = 'system_settings_tbls';

    public static function getValue($key, $default = null)
    {
        $columns = [
            'company_name' => 'company_name',
            'registration_number' => 'registration_no',
            'company_address' => 'address',
            'company_phone' => 'phone',
            'company_email' => 'email',
        ];

        $column = $columns[$key] ?? $key;
        $setting = self::first();

        return $setting ? $setting->$column : $default;
    }

    public static function setValue($key, $value)
    {
        $columns = [
            'company_name' => 'company_name',
            'registration_number' => 'registration_no',
            'company_address' => 'address',
            'company_phone' => 'phone',
            'company_email' => 'email',
        ];

        $column = $columns[$key] ?? $key;

        return self::updateOrCreate(
            [$column => $key],
            [$column => $value]
        );
    }
}
