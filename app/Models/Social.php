<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Str;
use stdClass;

/**
 * Class Social
 *
 * @property int id
 * @property int user_id
 * @property string network
 * @property string uid
 * @property int created_at
 */
class Social extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Генерирует уникальный логин
     *
     * @param StdClass $network
     * @param string   $delimiter
     * @return mixed
     */
    public function generateLogin($network, $delimiter = '-')
    {
        $firstName   = ucfirst(Str::slug($network->first_name, $delimiter));
        $lastName    = ucfirst(Str::slug($network->last_name, $delimiter));
        $firstLetter = $firstName[0];

        $variants = [];

        if (! empty($network->nickname)) {
            $variants[] = Str::slug($network->nickname, $delimiter);
        }

        $variants[] = $firstName;
        $variants[] = $firstName . $lastName;
        $variants[] = $firstName . $delimiter . $lastName;
        $variants[] = $lastName . $firstName;
        $variants[] = $lastName . $delimiter . $firstName;
        $variants[] = $firstLetter . $lastName;
        $variants[] = $firstLetter . $delimiter . $lastName;
        $variants[] = $lastName;

        if (! empty($network->bdate)) {
            [,, $year] = explode('.', $network->bdate);
            $shortYear = substr($year, -2);

            $variants[] = $firstName . $shortYear;
            $variants[] = $firstName . $year;
            $variants[] = $firstName . $delimiter . $shortYear;
            $variants[] = $firstName . $delimiter . $year;

            $variants[] = $firstName . $lastName . $shortYear;
            $variants[] = $firstName . $lastName . $year;
            $variants[] = $firstName . $delimiter . $lastName . $shortYear;
            $variants[] = $firstName . $delimiter . $lastName . $year;
            $variants[] = $firstName . $delimiter . $lastName . $delimiter . $shortYear;
            $variants[] = $firstName . $delimiter . $lastName . $delimiter . $year;

            $variants[] = $lastName . $firstName . $shortYear;
            $variants[] = $lastName . $firstName . $year;
            $variants[] = $lastName . $delimiter . $firstName . $shortYear;
            $variants[] = $lastName . $delimiter . $firstName . $year;
            $variants[] = $lastName . $delimiter . $firstName . $delimiter . $shortYear;
            $variants[] = $lastName . $delimiter . $firstName . $delimiter . $year;

            $variants[] = $firstLetter . $lastName . $shortYear;
            $variants[] = $firstLetter . $lastName . $year;
            $variants[] = $firstLetter . $lastName . $delimiter . $shortYear;
            $variants[] = $firstLetter . $lastName . $delimiter . $year;
            $variants[] = $firstLetter . $delimiter . $lastName . $shortYear;
            $variants[] = $firstLetter . $delimiter . $lastName . $year;
            $variants[] = $firstLetter . $delimiter . $lastName . $delimiter . $shortYear;
            $variants[] = $firstLetter . $delimiter . $lastName . $delimiter . $year;

            $variants[] = $lastName . $shortYear;
            $variants[] = $lastName . $year;
            $variants[] = $lastName . $delimiter . $shortYear;
            $variants[] = $lastName . $delimiter . $year;
        }

        foreach ($variants as $variant) {
            $variant = utfSubstr($variant, 0, 20);

            if (! getUserByLogin($variant)) {
                return $variant;
            }
        }

        $i = 0;
        while (true) {
            $firstName = utfSubstr($firstName, 0, 18);
            $login     = $firstName . ++$i;

            if (! getUserByLogin($login)) {
                return $login;
            }
        }

        return false;
    }
}
