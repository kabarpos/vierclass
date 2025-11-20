<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Error</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800 flex items-center justify-center">
    <div class="max-w-xl w-full mx-auto p-6">
        <div class="rounded-xl shadow-md bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-mountain-meadow-100 text-mountain-meadow-700">
                    <!-- Simple alert icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-8-4a1 1 0 011 1v4a1 1 0 11-2 0V7a1 1 0 011-1zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h1 class="text-xl font-semibold">Terjadi Kesalahan pada Checkout</h1>
            </div>

            <p class="mb-3 text-gray-700">{{ $message ?? 'Terjadi kesalahan saat memproses checkout Anda.' }}</p>
            @if(!empty($error))
                <div class="mt-2 p-3 rounded-lg bg-gray-100 text-gray-600 text-sm">
                    {{ $error }}
                </div>
            @endif

            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('front.index') }}" class="cursor-pointer inline-flex items-center px-4 py-2 rounded-lg bg-mountain-meadow-600 hover:bg-mountain-meadow-700 text-white font-semibold transition">
                    Kembali ke Beranda
                </a>
                <a href="javascript:history.back()" class="cursor-pointer inline-flex items-center px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold transition">
                    Coba Lagi
                </a>
            </div>
        </div>
    </div>
    
</body>
</html>
