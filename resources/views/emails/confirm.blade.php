@component('mail::message')
    # Hello {{ $user->name }}

    You change your email address. So you need to verify new email. Please verify the email from this link:

    @component('mail::button', ['url' =>  route("verify", $user->verification_token) ])
        Verify Account
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
