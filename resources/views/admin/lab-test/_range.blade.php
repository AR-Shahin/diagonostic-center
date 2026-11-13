{{-- One reference range row. $i = parameter index, $j = range index (or __P__/__R__ placeholders in the JS template). --}}
@php($n = "parameters[$i][ranges][$j]")
<tr data-range>
    <td>
        <select class="form-select form-select-sm" name="{{ $n }}[gender]" aria-label="Gender">
            @foreach (\App\Models\ReferenceRange::GENDERS as $value => $label)
                <option value="{{ $value }}" @selected(($r['gender'] ?? 'any') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td><input class="form-control form-control-sm" type="number" step="any" min="0" name="{{ $n }}[age_min]" value="{{ $r['age_min'] ?? '' }}" placeholder="—" aria-label="Age from"></td>
    <td><input class="form-control form-control-sm" type="number" step="any" min="0" name="{{ $n }}[age_max]" value="{{ $r['age_max'] ?? '' }}" placeholder="—" aria-label="Age to"></td>
    <td>
        <select class="form-select form-select-sm" name="{{ $n }}[age_unit]" aria-label="Age unit">
            @foreach (['years' => 'Years', 'months' => 'Months', 'days' => 'Days'] as $value => $label)
                <option value="{{ $value }}" @selected(($r['age_unit'] ?? 'years') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td><input class="form-control form-control-sm" type="number" step="any" name="{{ $n }}[low]" value="{{ $r['low'] ?? '' }}" aria-label="Low"></td>
    <td><input class="form-control form-control-sm" type="number" step="any" name="{{ $n }}[high]" value="{{ $r['high'] ?? '' }}" aria-label="High"></td>
    <td><input class="form-control form-control-sm" type="text" name="{{ $n }}[normal_text]" value="{{ $r['normal_text'] ?? '' }}" placeholder="auto" aria-label="Printed text"></td>
    <td class="text-end"><button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-range title="Remove range"><i class="fas fa-times"></i></button></td>
</tr>
