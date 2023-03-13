<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dateTime',
        'producer_code',
        'producer',
        'store',
        'product',
        'price',
        'quantity',
    ];

    /**
     * @param $date
     * @return array
     */
    private static function computeHourlyQuantity($date): array
    {
        return self::query()
            ->select('dateTime', 'producer_code', 'store', 'product', 'price')
            ->selectRaw('quantity -
                COALESCE(
                    (
                        LAG(quantity) OVER (
                            PARTITION BY store, product, price
                            ORDER BY dateTime
                        )
                    ),
                    0
                ) as subtotal')
            ->whereDate('dateTime', $date)
            ->get()
            ->toArray();
    }

    /**
     * @return array
     */
    private static function fetchUpdatedSalesDate(): array
    {
        return self::query()
            ->selectRaw('DATE_FORMAT(dateTime, "%Y-%m-%d") AS date')
            ->where('updated_at', 'like',date('Y-m-d H:i').'%')
            ->groupBy('date')
            ->get()
            ->toArray();
    }

    /**
     * @return array
     */
    public static function fetchUpdatedSales(): array
    {
        $array = [];
        foreach (self::fetchUpdatedSalesDate() as $date) {
            $array = array_merge($array, self::computeHourlyQuantity($date));
        }
        return $array;
    }
}
