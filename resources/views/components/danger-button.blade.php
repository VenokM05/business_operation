<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-danger border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:brightness-110 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-danger/40 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
