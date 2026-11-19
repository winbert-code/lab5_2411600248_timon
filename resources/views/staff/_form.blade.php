<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $staffMember->name) }}" autofocus>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $staffMember->email) }}" placeholder="staff@bytestock.com">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">
            Password
            @if ($staffMember->exists)<span class="text-muted small">(leave blank to keep current)</span>@else<span class="text-danger">*</span>@endif
        </label>
        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror"
               autocomplete="new-password">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password"
               class="form-control" autocomplete="new-password">
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> {{ $staffMember->exists ? 'Save Changes' : 'Add Staff' }}
    </button>
    <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
