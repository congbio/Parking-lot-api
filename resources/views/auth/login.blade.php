<form method="POST" action="{{ route('admin.login.submit') }}">
    @csrf

    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        @error('email')
            <span>{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        @error('password')
            <span>{{ $message }}</span>
        @enderror
    </div>

   

    <div>
        <button type="submit">Log in</button>
    </div>
</form>
