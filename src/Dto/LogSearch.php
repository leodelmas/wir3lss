<?php

namespace App\Dto;

class LogSearch
{
    private $keyword;

    public static function createFromArray(array $searchArray): self
    {
        return (new self)
            ->setKeyword($searchArray['search-keyword'] ?? null);
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(?string $keyword): self
    {
        $this->keyword = strlen($keyword) ? $keyword : null;
        return $this;
    }
}
