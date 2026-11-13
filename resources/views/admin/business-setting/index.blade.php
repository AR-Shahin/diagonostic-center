@extends('admin.layouts.master')

@section('title', 'Business rules')

@section('master_content')
    <x-admin.page-header title="Business rules" icon="fas fa-sliders-h" subtitle="How bills, referral commission and report delivery work at every branch." />

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0 small">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @php($canEdit = in_array('businesssetting-edit', $permissions))
    <form method="POST" action="{{ route('admin.business-settings.update') }}">
        @csrf
        <fieldset @disabled(! $canEdit)>
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header"><b><i class="fas fa-file-invoice text-primary me-1"></i> Billing</b></div>
                        <div class="card-body row">
                            <div class="col-md-4"><x-admin.input name="currency_symbol" label="Currency" :value="$values['currency_symbol']" required /></div>
                            <div class="col-md-4"><x-admin.input name="invoice_prefix" label="Invoice prefix" :value="$values['invoice_prefix']" required help="INV-2611-00001" /></div>
                            <div class="col-md-4"><x-admin.input name="receipt_prefix" label="Receipt prefix" :value="$values['receipt_prefix']" required help="MR-2611-00001" /></div>
                            <div class="col-md-4"><x-admin.input name="vat_percent" label="VAT %" type="number" step="0.01" min="0" :value="$values['vat_percent']" required help="0 if you don't charge VAT." /></div>
                            <div class="col-md-4"><x-admin.input name="home_collection_charge" label="Home collection charge (৳)" type="number" step="0.01" min="0" :value="$values['home_collection_charge']" required /></div>
                            <div class="col-md-4"><x-admin.input name="default_delivery_hours" label="Default report time (hours)" type="number" min="0" :value="$values['default_delivery_hours']" required /></div>
                            <div class="col-12"><x-admin.switch name="round_to_whole_taka" label="Round bill totals to whole taka" :checked="(bool) $values['round_to_whole_taka']" /></div>
                            <div class="col-12">
                                <div class="alert alert-light border small mb-0">
                                    <b>How a bill is calculated</b><br>
                                    Net payable = sum of item prices − discount + home collection charge + VAT. Paid = sum of receipts. Due = net payable − paid.
                                    Each staff member's maximum discount is set on their admin account.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-3">
                        <div class="card-header"><b><i class="fas fa-handshake text-success me-1"></i> Referral commission</b></div>
                        <div class="card-body">
                            <x-admin.switch name="commission_enabled" label="Pay commission to referring doctors / agents" :checked="(bool) $values['commission_enabled']" />
                            <x-admin.select name="discount_bearer" label="Who bears the patient's discount?" :options="$bearers" :value="$values['discount_bearer']" />
                            <x-admin.select name="commission_trigger" label="Commission is earned" :options="$triggers" :value="$values['commission_trigger']" />
                            <div class="alert alert-light border small mb-0">
                                <b>Example</b> (company bears discount): CBC ৳400 at 30% + USG ৳1,500 at 15% with a ৳200 discount →
                                commission = 120 + 225 = <b>৳345</b>; the centre absorbs the ৳200 discount.
                                Package tests earn commission on their share of the package price.
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header"><b><i class="fas fa-file-medical text-danger me-1"></i> Report delivery</b></div>
                        <div class="card-body">
                            <x-admin.switch name="block_report_if_due" label="Hold the printed report at the counter until the due is paid" :checked="(bool) $values['block_report_if_due']" />
                            <x-admin.switch name="block_portal_report_if_due" label="Hide the online report in the patient portal while a due is unpaid" :checked="(bool) $values['block_portal_report_if_due']" />
                        </div>
                    </div>
                </div>
            </div>

            @if ($canEdit)
                <div class="text-end mt-3 mb-5"><button class="btn btn-success px-5"><i class="fas fa-save me-1"></i> Save rules</button></div>
            @endif
        </fieldset>
    </form>
@endsection
