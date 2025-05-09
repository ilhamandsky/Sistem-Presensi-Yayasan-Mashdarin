{{-- Save this file as resources/views/components/input-login.blade.php --}}
@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => 'form-input',
]) !!}>

<style>
    .form-input {
        margin-top: 0.25rem;
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-input:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }

    .form-input:disabled {
        background-color: #f3f4f6;
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Dark mode styles */
    @media (prefers-color-scheme: dark) {
        .form-input {
            background-color: #111827;
            border-color: #374151;
            color: #d1d5db;
        }

        .form-input:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.2);
        }

        .form-input:disabled {
            background-color: #1f2937;
        }
    }
</style>
