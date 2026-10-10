<!DOCTYPE html>
<html lang="en"> 

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Reset Password - Bitroxia PMS</title>
  <meta content="Bitroxia PMS Password Reset" name="description">

  <!-- Favicons -->
  <link href="{{ asset('admin/assets/img/favicon/favicon.ico') }}" rel="icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('admin/assets/vendor/css/core.css') }}" rel="stylesheet">
  <link href="{{ asset('admin/assets/css/demo.css') }}" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: linear-gradient(135deg, #eef5ff 0%, #c9d8ee 48%, #aeb8cc 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .reset-card {
      border: none;
      border-radius: 24px;
      box-shadow: 0 20px 45px rgba(8, 16, 35, 0.12);
      width: 100%;
      max-width: 440px;
    }
    @media (max-width: 576px) {
      .reset-card {
        border-radius: 18px;
        box-shadow: 0 10px 25px rgba(8, 16, 35, 0.08);
      }
      .reset-card .card-body {
        padding: 24px 18px !important;
      }
    }
  </style>
</head>

<body>

<main class="w-100 py-4 px-3 d-flex align-items-center justify-content-center">
  <div class="reset-card card bg-white">
    <div class="card-body p-4 p-sm-5">
      <div class="text-center mb-4">
        <h4 class="fw-bold mb-1">Reset Password 🔒</h4>
        <p class="text-muted small mb-0">Enter your new password below</p>
      </div>

                <!-- Show Validation Errors -->
                @if ($errors->any())
                  <div class="alert alert-danger">
                    <ul class="mb-0">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}">
                  @csrf

                  <!-- Hidden Token -->
                  <input type="hidden" name="token" value="{{ $token }}">

                  <!-- Email -->
                  <div class="col-12 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-control" required autofocus>
                  </div>

                  <!-- Password -->
                  <div class="col-12 mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password">
                  </div>

                  <!-- Confirm Password -->
                  <div class="col-12 mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                  </div>

                  <!-- Submit -->
                  <div class="col-12">
                    <button class="btn btn-primary w-100" type="submit">Reset Password</button>
                  </div>
                </form>

                <div class="text-center mt-3">
                  <a href="{{ route('login') }}" class="text-muted small text-decoration-none">
                    &larr; Back to login
                  </a>
                </div>
              </div>
            </div>
</main>

<!-- Scripts -->
<script src="{{ asset('admin/assets/vendor/js/bootstrap.js') }}"></script>
</body>
</html>


