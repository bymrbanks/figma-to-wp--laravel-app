<form method="POST" action="{{ route('callback') }}">
    @csrf
    <input type="hidden" name="state" value="{{ $writeKey }}">
    <input type="email" name="email" required placeholder="Email">
    <input type="password" name="password" required placeholder="Password">
    <button type="submit">Login</button>
</form>