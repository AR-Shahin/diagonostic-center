{{-- Props: $form (appointment|contact|corporate). Success message or validation errors at the top of a website form. --}}
@if (session('form_sent') === $form)
    <div class="form-alert form-alert-ok" role="status" tabindex="-1" data-focus-me>
        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
        <div><b>{{ __('ধন্যবাদ! আপনার অনুরোধ পেয়েছি।') }}</b><br>{{ __('আমাদের প্রতিনিধি শীঘ্রই আপনাকে কল করে নিশ্চিত করবেন।') }}</div>
    </div>
@elseif ($errors->any() && old('_form') === $form)
    <div class="form-alert form-alert-error" role="alert" tabindex="-1" data-focus-me>
        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
        <ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
<input type="hidden" name="_form" value="{{ $form }}">
<div class="hp-field" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
