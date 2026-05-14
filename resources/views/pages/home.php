<main class="min-h-screen bg-black text-white">
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_28%,rgba(239,68,68,0.38),transparent_28%),linear-gradient(135deg,#030303_0%,#130405_30%,#1b1b21_60%,#080808_100%)]"></div>
        <div class="absolute inset-0 opacity-50 bg-[linear-gradient(135deg,transparent_0%,transparent_28%,rgba(255,255,255,0.12)_29%,transparent_31%,transparent_52%,rgba(239,68,68,0.22)_53%,transparent_57%)]"></div>
        <div class="absolute inset-x-0 bottom-0 h-40 bg-linear-to-t from-black to-transparent"></div>

        <div class="relative mx-auto grid min-h-[700px] max-w-7xl gap-12 px-8 py-10 lg:grid-cols-[1fr_530px] lg:px-12">
            <header class="lg:col-span-2">
                <a href="/" class="inline-flex items-center gap-3">
                    <span class="grid size-14 grid-cols-4 gap-1 rotate-45">
                        <?php for ($i = 0; $i < 16; $i++): ?>
                            <span class="<?= $i === 10 ? 'bg-red-500' : 'bg-white/70' ?>"></span>
                        <?php endfor; ?>
                    </span>
                    <span class="text-3xl font-black tracking-tight">ISARA</span>
                </a>
            </header>

            <div class="flex max-w-xl flex-col justify-center pb-12 lg:pb-28">
                <p class="text-sm font-bold uppercase tracking-wide text-white">30-minute technical session</p>
                <h1 class="mt-5 text-5xl font-light leading-tight tracking-tight text-white md:text-6xl">
                    Skip the pitch.<br>
                    Start with the problem.
                </h1>
                <p class="mt-6 max-w-lg text-xl leading-7 text-white/70">
                    Come with your environment, constraints, and requirements. Our team will address each concern.
                </p>

                <div class="mt-11">
                    <h2 class="text-base font-bold uppercase tracking-wide text-white">What this usually looks like</h2>
                    <ul class="mt-5 space-y-4 text-white/70">
                        <li class="flex items-start gap-4">
                            <span class="mt-1.5 size-4 rotate-45 rounded-[3px] bg-red-500"></span>
                            <span><em>Where you are in your cryptographic inventory</em></span>
                        </li>
                        <li class="flex items-start gap-4">
                            <span class="mt-1.5 size-4 rotate-45 rounded-[3px] bg-red-500"></span>
                            <span><em>Which compliance deadlines are actually creating pressure</em></span>
                        </li>
                        <li class="flex items-start gap-4">
                            <span class="mt-1.5 size-4 rotate-45 rounded-[3px] bg-red-500"></span>
                            <span><em>What integration points are needed for remediation</em></span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex items-center pb-16 lg:pb-28">
                <form class="w-full rounded-2xl bg-white p-8 text-slate-950 shadow-2xl shadow-black/40 md:p-10">
                    <h2 class="text-center text-2xl font-bold">Schedule Your Technical Session</h2>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <input class="h-16 rounded-xl border border-slate-200 px-6 text-base text-slate-700 shadow-lg shadow-slate-200 outline-none placeholder:text-slate-400 focus:border-red-400" type="text" placeholder="Full Name*">
                        <input class="h-16 rounded-xl border border-slate-200 px-6 text-base text-slate-700 shadow-lg shadow-slate-200 outline-none placeholder:text-slate-400 focus:border-red-400" type="email" placeholder="Work Email*">
                    </div>

                    <input class="mt-5 h-16 w-full rounded-xl border border-slate-200 px-6 text-base text-slate-700 shadow-lg shadow-slate-200 outline-none placeholder:text-slate-400 focus:border-red-400" type="text" placeholder="Organization">

                    <textarea class="mt-5 h-40 w-full resize-none rounded-xl border border-slate-200 px-6 py-6 text-base text-slate-700 shadow-lg shadow-slate-200 outline-none placeholder:text-slate-400 focus:border-red-400" placeholder="What are you currently working through? Current environment, compliance drivers, timeline, etc..."></textarea>

                    <button class="mt-5 rounded-lg bg-red-500 px-8 py-3 font-bold text-white transition hover:bg-red-600" type="submit">
                        Submit
                    </button>
                </form>
            </div>
        </div>
    </section>

    <footer class="bg-black px-8 py-16 lg:px-12">
        <div class="mx-auto grid max-w-7xl gap-12 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
            <div>
                <a href="/" class="inline-flex items-center gap-3">
                    <span class="grid size-14 grid-cols-4 gap-1 rotate-45">
                        <?php for ($i = 0; $i < 16; $i++): ?>
                            <span class="<?= $i === 10 ? 'bg-red-500' : 'bg-white/70' ?>"></span>
                        <?php endfor; ?>
                    </span>
                    <span class="text-3xl font-black tracking-tight">ISARA</span>
                </a>

                <p class="mt-8 text-xs font-semibold leading-tight text-white/80">
                    Funded by:<br>
                    Federal Economic Development<br>
                    Agency for Southern Ontario
                </p>
                <p class="mt-2 font-serif text-4xl text-white">Canada</p>

                <div class="mt-8 flex gap-5 text-white/40">
                    <span class="text-lg">▶</span>
                    <span class="text-lg font-bold">in</span>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-white">Features</h3>
                <ul class="mt-6 space-y-3 text-white/45">
                    <li>Solutions</li>
                    <li>ISARA Advance</li>
                    <li>ISARA Radiate</li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-white">Learn More</h3>
                <ul class="mt-6 space-y-3 text-white/45">
                    <li>Blog</li>
                    <li>About</li>
                    <li>Resources</li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-white">Information</h3>
                <ul class="mt-6 space-y-3 text-white/45">
                    <li>Contact</li>
                    <li>Privacy Policy</li>
                    <li>Terms &amp; Conditions</li>
                </ul>
            </div>
        </div>
    </footer>
</main>
