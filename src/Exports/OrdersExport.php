<?php

namespace EscolaLms\Cart\Exports;

use EscolaLms\Cart\Http\Resources\OrderExportResource;
use EscolaLms\Cart\Http\Resources\OrderResource;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
    private array $orders;
    private array $keys = [];
    public function __construct(Collection $orders)
    {
        $this->orders = json_decode(OrderExportResource::collection($orders)->toJson(), true);

        foreach ($this->orders as $order) {
            $this->keys = array_merge($this->keys, array_keys($order));
        }

        $this->keys = array_unique($this->keys);
    }

    public function collection()
    {
        return collect($this->orders)->map(function ($order) {
            $result = [];
            foreach ($this->keys as $key) {
                $result[$key] = $order[$key] ?? '';
            }

            return $result;
        });
    }

    public function headings(): array
    {
        return $this->keys;
    }
}
