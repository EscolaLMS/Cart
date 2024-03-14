<?php

namespace EscolaLms\Cart\Dtos;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;

class PageDto implements DtoContract, InstantiateFromRequest
{
    private ?int $skip;

    private ?int $per_page;

    public function __construct(?int $skip, ?int $per_page)
    {
        $this->skip = $skip;
        $this->per_page = $per_page;
    }

    public function toArray(): array
    {
        return [
            'skip' => $this->getSkip(),
            'per_page' => $this->getPerPage()
        ];
    }

    public static function instantiateFromRequest(Request $request): self
    {
        $per_page = config('paginate.default.limit', 15);

        if ($request->get('page')) {
            return new self(
                $request->get('skip', ($request->get('page') - 1) * $per_page),
                $request->get('per_page', $per_page),
            );
        }

        return new self(
            $request->get('skip', config('paginate.default.limit', 0)),
            $request->get('per_page', $per_page),
        );
    }

    public function getSkip(): ?int
    {
        return $this->skip;
    }

    public function getPerPage(): ?int
    {
        return $this->per_page;
    }
}
