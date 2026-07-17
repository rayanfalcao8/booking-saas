@props([
    'href' => '/',
    'label' => 'Reservix',
])

<a {{ $attributes->class(['rx-brand']) }} href="{{ $href }}" aria-label="{{ $label }}">
    <svg class="rx-brand__mark" viewBox="0 0 36 36" aria-hidden="true">
        <rect width="36" height="36" rx="10" fill="currentColor"/>
        <path d="M10.5 11.5h8.25c4.6 0 7.25 2.2 7.25 6.05 0 2.6-1.25 4.45-3.55 5.35L26 28h-5.1l-3.05-4.45H15V28h-4.5V11.5Zm4.5 3.8v4.55h3.45c1.95 0 3-.8 3-2.3 0-1.5-1.05-2.25-3-2.25H15Z" fill="white"/>
        <circle cx="27.5" cy="9" r="3.5" fill="#7dd3fc"/>
    </svg>
    <span>{{ $label }}</span>
</a>
