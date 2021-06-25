@props(['disabled' => false])

<select {!! $attributes->merge(['class' => "w-full border bg-white rounded px-3 py-2 outline-none"]) !!}>
    <option class="py-1">Option 1</option>
    <option class="py-1">Option 2</option>
    <option class="py-1">Option 3</option>
</select>

