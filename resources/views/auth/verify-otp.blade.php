<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="{{ asset('admin/assets/img/favicon.png') }}" type="image/png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #eef5ff 0%, #c9d8ee 48%, #aeb8cc 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 16px;
        }
        .container {
            background: #ffffff;
            padding: 2.25rem 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(8, 16, 35, 0.12);
            text-align: center;
            max-width: 420px;
            width: 100%;
        }
        .container h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #111827;
        }
        .container p {
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
            color: #64748b;
        }
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            font-size: 1.1rem;
            letter-spacing: 0.15em;
            text-align: center;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .btn {
            background: #2563eb;
            color: #fff;
            padding: 0.8rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .btn:active {
            transform: scale(0.99);
        }
        .error {
            display: block;
            margin-top: 6px;
            color: #ef4444;
            font-size: 0.82rem;
            font-weight: 600;
        }
        @media (max-width: 480px) {
            .container {
                padding: 1.75rem 1.25rem;
                border-radius: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify OTP</h1>
        <p>Please enter the OTP sent to your email to verify your login.</p>

        <form method="POST" action="{{ route('verify-otp') }}">
            @csrf
            <div class="form-group">
                <label for="otp">OTP Code:</label>
                <input type="text" name="otp" id="otp" placeholder="Enter your OTP" required>
                @if ($errors->has('otp'))
                    <span class="error">{{ $errors->first('otp') }}</span>
                @endif
            </div>
            <button type="submit" class="btn">Verify</button>
        </form>
    </div>
</body>
</html>
