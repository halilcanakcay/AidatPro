@props(['name', 'class' => ''])

<i {{ $attributes->class(['ti', 'ti-' . $name, 'app-icon', $class])->merge(['aria-hidden' => 'true']) }}></i>
