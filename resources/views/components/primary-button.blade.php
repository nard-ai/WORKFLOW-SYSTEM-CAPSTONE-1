<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-cta-yellow border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-cta-yellow-dark focus:bg-cta-yellow-dark active:bg-cta-yellow-dark focus:outline-none focus:ring-2 focus:ring-cta-yellow focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
