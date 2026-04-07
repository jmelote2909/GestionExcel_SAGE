<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $guarded = [];

    protected $appends = ['corrected_saldo_float'];

    public function getCorrectedSaldoFloatAttribute()
    {
        $val = !empty($this->correccion) ? $this->correccion : $this->SaldoP;
        $val = (string)$val;
        // Only strip dots if there is also a comma, which indicates European thousands/decimal format
        if (strpos($val, ',') !== false) {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        }
        return (float)$val;
    }
}
