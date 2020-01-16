<h3>Hello {{ $user->name }}</h3>

<h6>You change your email address. So you need to verify new email. Please verify the email from this link:</h6>

<a class="btn btn-info" href="{{ route('verify', $user->verification_token) }}">Click To Verify</a>
