<?php

namespace App\services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PatientSearch
{
    public function normalize(string $value): string
    {
        $value = strtr($value, array_combine(
            preg_split('//u', '۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩يك', -1, PREG_SPLIT_NO_EMPTY),
            preg_split('//u', '01234567890123456789یک', -1, PREG_SPLIT_NO_EMPTY)
        ));

        return trim(preg_replace('/[\s\x{200c}\x{200f}]+/u', ' ', $value));
    }

    public function query(string $value): Builder
    {
        $term = $this->normalize($value);
        $query = User::query();
        if (strlen($term) === 1 && ctype_digit($term)) {
            return $query->where('caseNumber', $term)->orderByDesc('id');
        }
        if (mb_strlen($term) < 2 || !preg_match('/[\p{L}\p{N}]/u', $term)) {
            return $query->whereRaw('1 = 0');
        }

        $prefix = $this->escapeLike($term).'%';
        if (ctype_digit($term)) {
            // Numeric prefixes avoid noisy substring matches across unrelated records.
            $query->where(function (Builder $query) use ($prefix) {
                foreach (['caseNumber', 'nationalCode', 'phone'] as $column) {
                    $query->orWhereRaw("{$column} LIKE ? ESCAPE '!'", [$prefix]);
                }
            });
            $query->orderByRaw('CASE WHEN caseNumber = ? THEN 0 WHEN nationalCode = ? THEN 1 WHEN phone = ? THEN 2 ELSE 3 END', [$term, $term, $term]);
        } else {
            $first = "REPLACE(REPLACE(REPLACE(name, 'ي', 'ی'), 'ك', 'ک'), '‌', ' ')";
            $last = "REPLACE(REPLACE(REPLACE(lastName, 'ي', 'ی'), 'ك', 'ک'), '‌', ' ')";
            foreach (explode(' ', $term) as $word) {
                $query->where(function (Builder $query) use ($first, $last, $word) {
                    foreach ([$first, $last] as $column) {
                        $query->orWhereRaw("{$column} LIKE ? ESCAPE '!'", [$this->escapeLike($word).'%'])
                            ->orWhereRaw("{$column} LIKE ? ESCAPE '!'", ['% '.$this->escapeLike($word).'%']);
                    }
                });
            }
            $query->orderByRaw("CASE WHEN CONCAT($first, ' ', $last) = ? THEN 0 WHEN $last = ? THEN 1 WHEN $first = ? THEN 2 ELSE 3 END", [$term, $term, $term]);
        }

        return $query->orderByDesc('id');
    }

    public function suggestions(string $term)
    {
        return $this->query($term)
            ->select(['id', 'name', 'lastName', 'caseNumber', 'nationalCode'])
            ->limit(5)->get();
    }

    private function escapeLike(string $value): string
    {
        return strtr($value, ['!' => '!!', '%' => '!%', '_' => '!_']);
    }
}
