<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white border border-line rounded-lg font-semibold text-xs text-ink uppercase tracking-widest shadow-sm hover:bg-canvas hover:border-muted/40 focus:outline-none focus:ring-2 focus:ring-brand/25 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
