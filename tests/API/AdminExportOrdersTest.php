<?php

namespace API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Enums\ExportFormatEnum;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Exports\OrdersExport;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Maatwebsite\Excel\Facades\Excel;

class AdminExportOrdersTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CartPermissionSeeder::class);
        $this->user = $this->makeAdmin();
        Excel::fake();
    }

    public function test_export_order_unauthorized(): void
    {
        $this->json('GET', '/api/admin/orders/export')->assertUnauthorized();
    }

    public function test_export_orders(): void
    {
        Order::factory()->count(10)->create();

        $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders/export')->assertOk();

        Excel::assertDownloaded('orders.csv', function (OrdersExport $ordersExport) {
            $this->assertCount(10, $ordersExport->collection());
            return true;
        });
    }

    public function test_export_orders_to_excel(): void
    {
        Order::factory()->count(10)->create();

        $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders/export', ['format' => ExportFormatEnum::XLSX])->assertOk();

        Excel::assertDownloaded('orders.xlsx', function (OrdersExport $ordersExport) {
            $this->assertCount(10, $ordersExport->collection());
            return true;
        });

        $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders/export', ['format' => ExportFormatEnum::XLS])->assertOk();

        Excel::assertDownloaded('orders.xls', function (OrdersExport $ordersExport) {
            $this->assertCount(10, $ordersExport->collection());
            return true;
        });
    }

    public function test_export_orders_with_criteria_status(): void
    {
        Order::factory()->count(10)->create(['status' => OrderStatus::PROCESSING]);
        Order::factory()->count(5)->create(['status' => OrderStatus::PAID]);

        $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders/export', [
            'status' => OrderStatus::PAID,
        ])->assertOk();

        Excel::assertDownloaded('orders.csv', function (OrdersExport $ordersExport) {
            $this->assertCount(5, $ordersExport->collection());
            return true;
        });
    }

    public function test_export_orders_with_criteria_user(): void
    {
        $student = $this->makeStudent();
        Order::factory()->count(10)->create(['status' => OrderStatus::PROCESSING]);
        Order::factory()->count(5)->create(['status' => OrderStatus::PAID]);
        Order::factory()->count(2)->create([
            'status' => OrderStatus::PAID,
            'user_id' => $student->getKey(),
        ]);

        $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders/export', [
            'status' => OrderStatus::PAID,
            'user_id' => $student->getKey(),
        ])->assertOk();

        Excel::assertDownloaded('orders.csv', function (OrdersExport $ordersExport) {
            $this->assertCount(2, $ordersExport->collection());
            return true;
        });
    }
}
