@extends('layouts.app')

@section('title', 'Masuk Pengurus — Masjid Pintar')

@section('content')
<div class="container-narrow" style="padding:24px 0;">
    <div class="card" style="max-width:460px; margin:0 auto;">
        <h1 style="font-size:1.75rem; margin-bottom:6px;">Assalamu'alaikum</h1>
        <p style="color:var(--text-muted); margin-top:0; margin-bottom:24px;">Masuk ke panel internal bendahara dan pengurus DKM.</p>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="field">
                <label class="label" for="email">Email Pengurus</label>
                <input id="email" type="email" name="email" value="{{ old('email', 'bendahara@masjidpintar.id') }}" class="input" required autofocus>
            </div>

            <div class="field">
                <label class="label" for="password">Kata Sandi</label>
                <input id="password" type="password" name="password" class="input" required>
            </div>

            <label style="display:flex; align-items:center; gap:8px; font-size:0.9rem; margin-bottom:20px; cursor:pointer;">
                <input type="checkbox" name="remember" value="1">
                <span>Ingat perangkat ini</span>
            </label>

            <button type="submit" class="btn btn-primary" style="width:100%;">Masuk ke Dashboard</button>
        </form>
    </div>
</div>
@endsection
