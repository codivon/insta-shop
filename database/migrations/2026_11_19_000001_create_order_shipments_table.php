<?php

use App\Models\Carrier;
use App\Support\Database\Concerns\DefensiveMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-shop shipment slices (multi-seller orders).
 *
 * One checkout can contain items from SEVERAL shops — each shop owns its
 * own slice of the order: its items total, its carrier cost, its share of
 * the discount code and its OWN shipment lifecycle + cancellation.
 *
 *   order_shipments (one row per order × shop):
 *     shipment_status  none|processing|shipped|delivered   (per shop)
 *     tracking_code    کد رهگیری پستی of THIS shop's parcel
 *     canceled_at / cancel_reason / canceled_by              (slice cancel)
 *     refund_amount    money that must go back to the customer
 *     items_total / shipping_cost / discount_amount / total   (slice snapshot)
 *
 * orders.shipment_status stays as the DERIVED overall status (kept in sync
 * by Order::syncOverallShipment) so existing lists keep working.
 */
return new class extends Migration
{
    use DefensiveMigration;

    public function up(): void
    {
        self::recreate('order_shipments');

        Schema::create('order_shipments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();

            $table->string('shipment_status', 20)->default('none')->comment('none|processing|shipped|delivered');
            $table->string('tracking_code', 60)->nullable();
            $table->timestamp('shipment_status_at')->nullable();

            $table->timestamp('canceled_at')->nullable();
            $table->string('cancel_reason', 300)->default('');
            $table->string('canceled_by', 20)->nullable()->comment('seller|admin');
            $table->unsignedBigInteger('refund_amount')->default(0);

            // slice snapshot (Toman)
            $table->unsignedBigInteger('items_total')->default(0);
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);

            $table->timestamps();

            $table->unique(['order_id', 'shop_id']);
            $table->index('shop_id');
        });

        // ── Backfill: one slice per existing (order × shop) ──
        $itemShares = DB::table('order_items')
            ->selectRaw('order_id, shop_id, SUM(unit_price * quantity) as items_total')
            ->groupBy('order_id', 'shop_id')
            ->get();

        foreach ($itemShares as $share) {
            $order = DB::table('orders')->where('id', $share->order_id)->first();

            if (! $order) {
                continue;
            }

            $orderItemsTotal = (int) DB::table('order_items')->where('order_id', $order->id)->sum(DB::raw('unit_price * quantity'));

            // per-shop carrier cost from the shipments JSON snapshot
            // (pay-on-delivery carriers are NOT charged online → 0)
            $shippingCost = 0;
            foreach ((array) json_decode((string) $order->shipments, true) as $shipment) {
                if ((int) ($shipment['shop_id'] ?? 0) === (int) $share->shop_id) {
                    $carrier = Carrier::query()->find((int) ($shipment['carrier_id'] ?? 0));
                    $cost = max(0, (int) ($shipment['cost'] ?? 0));
                    $shippingCost = ($carrier && $carrier->isPayOnDelivery()) ? 0 : $cost;

                    break;
                }
            }

            // proportional share of the order-level discount code
            $discountShare = $orderItemsTotal > 0
                ? (int) floor(((int) $order->discount_amount) * ((int) $share->items_total) / $orderItemsTotal)
                : 0;

            $canceled = $order->status === 'canceled';

            DB::table('order_shipments')->insert([
                'order_id' => $order->id,
                'shop_id' => $share->shop_id,
                'shipment_status' => $canceled ? 'none' : (string) $order->shipment_status,
                'tracking_code' => $canceled ? null : $order->tracking_code,
                'shipment_status_at' => $canceled ? null : $order->shipment_status_at,
                'canceled_at' => $canceled ? $order->updated_at : null,
                'cancel_reason' => $canceled ? 'سفارش لغو شد' : '',
                'canceled_by' => $canceled ? 'admin' : null,
                'refund_amount' => 0,
                'items_total' => max(0, (int) $share->items_total),
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discountShare,
                'total' => max(0, (int) $share->items_total + $shippingCost - $discountShare),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
    }
};
