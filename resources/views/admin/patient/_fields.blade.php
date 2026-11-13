<div class="row">
    <div class="col-md-7"><x-admin.input name="name" label="Patient name" required /></div>
    <div class="col-md-5"><x-admin.input name="mobile" label="Mobile" required placeholder="01XXXXXXXXX" inputmode="tel" /></div>
    <div class="col-md-4"><x-admin.select name="gender" label="Gender" :options="\App\Models\Patient::GENDERS" placeholder="Select" required /></div>
    <div class="col-6 col-md-2"><x-admin.input name="age_years" label="Age (Y)" type="number" min="0" max="130" /></div>
    <div class="col-6 col-md-2"><x-admin.input name="age_months" label="(M)" type="number" min="0" max="11" /></div>
    <div class="col-md-4"><x-admin.input name="date_of_birth" label="or Date of birth" type="date" /></div>
    <div class="col-md-4"><x-admin.select name="blood_group" label="Blood group" :options="array_combine(\App\Models\Patient::BLOOD_GROUPS, \App\Models\Patient::BLOOD_GROUPS)" placeholder="—" /></div>
    <div class="col-md-8"><x-admin.input name="email" label="Email" type="email" /></div>
    <div class="col-12"><x-admin.textarea name="address" label="Address" rows="2" /></div>
    <div class="col-12"><x-admin.textarea name="notes" label="Notes" rows="2" /></div>
    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="as_family_member" value="0">
            <input class="form-check-input" type="checkbox" name="as_family_member" value="1" id="f_as_family_member">
            <label class="form-check-label" for="f_as_family_member">Add as a family member if this number is already registered</label>
        </div>
        <div class="form-text">One phone number = one website account. Family members sharing a phone are separate patients inside that account.</div>
    </div>
</div>
