<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-brand border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-dark active:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand/40 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
