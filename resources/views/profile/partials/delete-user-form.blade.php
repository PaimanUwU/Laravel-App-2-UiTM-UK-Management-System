<section class="space-y-6">
    <p class="text-gray-600">
        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
    </p>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger">{{ __('Delete Account') }}</flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" class="md:w-[20rem]">
        <form method="post" action="{{ route('profile.destroy') }}" class="space-y-6">
            @csrf
            @method('delete')

            <div>
                <flux:heading size="lg">{{ __('Are you sure?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.') }}
                </flux:subheading>
            </div>

            <flux:input label="{{ __('Password') }}" type="password" name="password" placeholder="{{ __('Password') }}"
                required />

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger">
                    {{ __('Delete Account') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>