@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-sf-bg dark:text-gray-300 focus:border-sf-blue dark:focus:border-sf-blue focus:ring-sf-blue dark:focus:ring-sf-blue rounded-md shadow-sm']) }}>
