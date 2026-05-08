<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aori | Login Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0f172a; }
        .login-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .aori-gradient {
            background: linear-gradient(135deg, #1d3557 0%, #0f172a 100%);
        }
        .ring-decor {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
        }
        .ring-red { background: rgba(230, 57, 70, 0.15); top: -100px; right: -100px; }
        .ring-green { background: rgba(42, 157, 143, 0.15); bottom: -100px; left: -100px; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    <div class="ring-decor ring-red"></div>
    <div class="ring-decor ring-green"></div>

    <div class="login-card w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col">
        <div class="p-12">
            <div class="flex justify-center mb-10">
                <div class="relative group">
                    <div class="absolute inset-0 bg-indigo-500 blur-xl opacity-20 group-hover:opacity-40 transition-opacity"></div>
                    <div class="relative w-20 h-20 bg-[#1d3557] rounded-3xl flex items-center justify-center border border-white/10 shadow-inner">
                        <div class="w-12 h-12 relative">
                            <div class="absolute inset-0 border-4 border-[#e63946] rounded-full"></div>
                            <div class="absolute inset-0 border-4 border-[#2a9d8f] rounded-full rotate-45 border-t-transparent border-r-transparent"></div>
                            <div class="absolute inset-0 flex items-center justify-center font-black text-white text-xl">A</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mb-10">
                <h1 class="text-3xl font-extrabold text-white tracking-tight">System Access</h1>
                <p class="text-slate-400 text-sm mt-2">Sign in to AORI Inventory Management</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] ml-1">Email Identity</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500"></i>
                        <input type="email" name="email" placeholder="admin@aori.com" class="w-full bg-slate-900/50 border border-white/5 rounded-2xl py-4 pl-12 pr-4 focus:border-indigo-500 outline-none text-white font-medium transition-all" required>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] ml-1">Security Key</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" class="w-full bg-slate-900/50 border border-white/5 rounded-2xl py-4 pl-12 pr-12 focus:border-indigo-500 outline-none text-white font-medium transition-all" required>
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                            <i id="eye-icon" data-lucide="eye" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                <div class="bg-rose-500/10 border border-rose-500/20 p-4 rounded-xl text-rose-500 text-xs font-bold flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    {{ $errors->first() }}
                </div>
                @endif

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-extrabold text-sm uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98]">
                    Enter System
                </button>
            </form>
        </div>
        
        <div class="bg-white/5 p-6 text-center border-t border-white/5">
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">© 2026 Aori Corporation • Secure Terminal</p>
        </div>
    </div>

    <script>
        lucide.createIcons();
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
