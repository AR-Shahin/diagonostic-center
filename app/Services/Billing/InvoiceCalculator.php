<?php

namespace App\Services\Billing;

use App\Models\LabTest;
use App\Models\Package;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Works out a bill from the chosen tests/packages. Prices always come from the database, never the browser.
 *
 *   subtotal  = Σ line prices (test = offer price or price, package = package price)
 *   discount  = % of subtotal or a flat amount (never more than the subtotal)
 *   VAT       = VAT% × (subtotal − discount)
 *   net       = subtotal − discount + VAT + home collection charge, rounded to whole taka if enabled
 *
 * The discount is spread over the lines in proportion to their prices (for department income reports).
 * Package lines are split into their tests using Package::priceShares().
 */
class InvoiceCalculator
{
    /**
     * @param  array<int, array{type: string, id: int|string}>  $items
     */
    public function calculate(array $items, int $branchId, ?string $discountType, float $discountValue, bool $homeCollection): array
    {
        if (empty($items)) {
            throw ValidationException::withMessages(['items' => 'Add at least one test or package.']);
        }

        $lines = $this->buildLines($items, $branchId);
        $this->rejectDuplicateTests($lines);

        $subtotal = round($lines->sum('price'), 2);

        $discount = 0.0;
        if ($discountValue > 0 && in_array($discountType, ['percent', 'flat'], true)) {
            if ($discountType === 'percent' && $discountValue > 100) {
                throw ValidationException::withMessages(['discount_value' => 'Discount cannot be more than 100%.']);
            }
            $discount = $discountType === 'percent'
                ? round($subtotal * $discountValue / 100, 2)
                : min(round($discountValue, 2), $subtotal);
        }

        $vatPercent = (float) Setting::get('vat_percent', 0);
        $vat = round(($subtotal - $discount) * $vatPercent / 100, 2);
        $homeCharge = $homeCollection ? round((float) Setting::get('home_collection_charge', 0), 2) : 0.0;

        $raw = $subtotal - $discount + $vat + $homeCharge;
        $net = (bool) Setting::get('round_to_whole_taka', 1) ? round($raw) : round($raw, 2);

        $lines = $this->allocateDiscount($lines, $discount);
        $defaultHours = (int) Setting::get('default_delivery_hours', 24);
        $slowest = (int) $lines->flatMap(fn ($l) => $l['children'] ?: [$l])->max(fn ($l) => $l['delivery_hours'] ?? $defaultHours);

        return [
            'lines' => $lines->values()->all(),
            'subtotal' => $subtotal,
            'discount_type' => $discount > 0 ? $discountType : null,
            'discount_value' => $discount > 0 ? $discountValue : 0,
            'discount_amount' => $discount,
            'discount_percent' => $subtotal > 0 ? round($discount / $subtotal * 100, 4) : 0,
            'vat_percent' => $vatPercent,
            'vat_amount' => $vat,
            'home_collection' => $homeCollection,
            'home_collection_charge' => $homeCharge,
            'round_off' => round($net - $raw, 2),
            'net_amount' => $net,
            'delivery_hours' => $slowest ?: $defaultHours,
        ];
    }

    private function buildLines(array $items, int $branchId): Collection
    {
        $testIds = collect($items)->where('type', 'test')->pluck('id')->map(fn ($id) => (int) $id);
        $packageIds = collect($items)->where('type', 'package')->pluck('id')->map(fn ($id) => (int) $id);

        $tests = LabTest::query()->whereIn('id', $testIds)->where('status', true)->availableAt($branchId)->get()->keyBy('id');
        $packages = Package::query()->whereIn('id', $packageIds)->where('status', true)
            ->with(['labTests' => fn ($q) => $q->withTrashed()])->get()->keyBy('id');

        $lines = collect();
        foreach ($items as $item) {
            $id = (int) $item['id'];
            if ($item['type'] === 'test') {
                $test = $tests->get($id) ?? throw ValidationException::withMessages(['items' => 'A selected test is inactive or not offered at this branch.']);
                $lines->push($this->testLine($test, $test->sellingPrice(), 'test'));
            } elseif ($item['type'] === 'package') {
                $package = $packages->get($id) ?? throw ValidationException::withMessages(['items' => 'A selected package is inactive.']);
                if (! $package->isCurrentlyValid()) {
                    throw ValidationException::withMessages(['items' => "The package \"{$package->name_en}\" is not valid today."]);
                }
                $shares = $package->priceShares();
                $lines->push([
                    'item_type' => 'package', 'lab_test_id' => null, 'package_id' => $package->id, 'department_id' => null,
                    'code' => $package->code, 'name' => $package->name_en,
                    'price' => (float) $package->price, 'regular_price' => $package->regularPrice(),
                    'delivery_hours' => null,
                    'children' => $package->labTests->map(fn (LabTest $t) => $this->testLine($t, $shares[$t->id] ?? 0, 'package_test'))->values()->all(),
                ]);
            } else {
                throw ValidationException::withMessages(['items' => 'Unknown item type.']);
            }
        }

        return $lines;
    }

    private function testLine(LabTest $test, float $price, string $type): array
    {
        return [
            'item_type' => $type, 'lab_test_id' => $test->id, 'package_id' => null, 'department_id' => $test->department_id,
            'code' => $test->code, 'name' => $test->name_en,
            'price' => round($price, 2), 'regular_price' => (float) $test->price,
            'delivery_hours' => $test->delivery_hours, 'children' => [],
        ];
    }

    private function rejectDuplicateTests(Collection $lines): void
    {
        $seen = [];
        foreach ($lines as $line) {
            $where = $line['item_type'] === 'package' ? "in the package \"{$line['name']}\"" : 'as a single test';
            foreach ($line['children'] ?: [$line] as $test) {
                if (isset($seen[$test['lab_test_id']])) {
                    throw ValidationException::withMessages([
                        'items' => "{$test['name']} is added twice: {$seen[$test['lab_test_id']]} and {$where}. Remove one.",
                    ]);
                }
                $seen[$test['lab_test_id']] = $where;
            }
        }
    }

    /** Spread the invoice discount over lines (and package tests) by price; rounding remainder goes to the last row. */
    private function allocateDiscount(Collection $lines, float $discount): Collection
    {
        $split = function (array $rows, float $amount): array {
            $total = array_sum(array_column($rows, 'price'));
            $given = 0.0;
            $last = count($rows) - 1;
            foreach ($rows as $i => &$row) {
                $share = $total > 0 ? ($i === $last ? round($amount - $given, 2) : round($row['price'] / $total * $amount, 2)) : 0.0;
                $given += $share;
                $row['discount_amount'] = $share;
                $row['net_amount'] = round($row['price'] - $share, 2);
            }

            return $rows;
        };

        $rows = $split($lines->all(), $discount);
        foreach ($rows as &$row) {
            if ($row['children']) {
                $row['children'] = $split($row['children'], $row['discount_amount']);
            }
        }

        return collect($rows);
    }
}
