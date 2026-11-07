<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

// shortcut: Eloquent không hỗ trợ khóa ghép nên find() chỉ lọc theo cột đầu; hãy dùng where() với đủ các cột khóa.
abstract class ChiTietModel extends Model
{
    /** @var list<string> */
    protected array $khoaGhep = [];

    protected function setKeysForSaveQuery($query): Builder
    {
        foreach ($this->khoaGhep as $cot) {
            $query->where($cot, $this->getOriginal($cot) ?? $this->getAttribute($cot));
        }

        return $query;
    }
}
