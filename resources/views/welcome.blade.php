<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>F-Tools Dashboard</title>

	@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
		@vite(['resources/css/app.css', 'resources/js/app.js'])
	@else
		<script src="https://cdn.tailwindcss.com"></script>
	@endif
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
	<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
		<div class="absolute -top-20 -left-20 h-72 w-72 rounded-full bg-cyan-500/20 blur-3xl"></div>
		<div class="absolute top-32 right-0 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
		<div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-emerald-500/10 blur-3xl"></div>
	</div>

	<main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
		<header class="mb-10">
			<p class="mb-3 inline-flex rounded-full border border-white/20 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-200">
				F-Tools
			</p>
			<h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl">Dashboard Menu</h1>
			<p class="mt-3 max-w-2xl text-slate-300">
				Pilih tool yang ingin kamu pakai. Semua fitur utama ada di sini.
			</p>
		</header>

		<section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
			<a href="{{ route('image.index') }}" class="group rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur transition hover:-translate-y-1 hover:border-cyan-300/40 hover:bg-white/10">
				<div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/20 text-2xl">🖼️</div>
				<h2 class="text-xl font-bold text-white">Convert Image</h2>
				<p class="mt-2 text-sm text-slate-300">Convert dan compress gambar ke WebP dengan cepat.</p>
				<span class="mt-6 inline-flex text-sm font-semibold text-cyan-200 group-hover:text-cyan-100">Open Tool -&gt;</span>
			</a>

			<a href="{{ route('invoice.index') }}" class="group rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur transition hover:-translate-y-1 hover:border-amber-300/40 hover:bg-white/10">
				<div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/20 text-2xl">📄</div>
				<h2 class="text-xl font-bold text-white">Invoice</h2>
				<p class="mt-2 text-sm text-slate-300">Buat invoice profesional siap kirim ke klien.</p>
				<span class="mt-6 inline-flex text-sm font-semibold text-amber-200 group-hover:text-amber-100">Open Tool -&gt;</span>
			</a>

			<a href="{{ route('ai-hunter.index') }}" class="group rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur transition hover:-translate-y-1 hover:border-rose-300/40 hover:bg-white/10">
				<div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500/20 text-2xl">🔥</div>
				<h2 class="text-xl font-bold text-white">AI Hunter</h2>
				<p class="mt-2 text-sm text-slate-300">Cari video AI terbaru dari YouTube untuk ide konten.</p>
				<span class="mt-6 inline-flex text-sm font-semibold text-rose-200 group-hover:text-rose-100">Open Tool -&gt;</span>
			</a>
		</section>
	</main>
</body>
</html>
