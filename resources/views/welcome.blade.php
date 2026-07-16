<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Reservix simplifie la prise de rendez-vous, l'organisation de l'équipe et le suivi de votre activité.">

        <title>Reservix — Les rendez-vous, sans les allers-retours</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#f5f2eb] font-sans text-stone-950 antialiased">
        <div class="relative isolate overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[46rem] bg-[radial-gradient(circle_at_80%_10%,rgba(245,158,11,0.22),transparent_30%),radial-gradient(circle_at_15%_15%,rgba(255,255,255,0.95),transparent_35%)]"></div>

            <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="Accueil Reservix">
                    <span class="grid size-10 grid-cols-2 gap-1 rounded-xl bg-stone-950 p-2 shadow-lg shadow-stone-950/10" aria-hidden="true">
                        <span class="rounded-sm bg-amber-400"></span>
                        <span class="rounded-sm bg-white/90"></span>
                        <span class="rounded-sm bg-white/45"></span>
                        <span class="rounded-sm bg-amber-400"></span>
                    </span>
                    <span class="text-xl font-black tracking-[-0.04em]">Reservix</span>
                </a>

                <nav class="hidden items-center gap-8 text-sm font-semibold text-stone-600 md:flex" aria-label="Navigation principale">
                    <a class="transition hover:text-stone-950" href="#service">Le service</a>
                    <a class="transition hover:text-stone-950" href="#avantages">Avantages</a>
                    <a class="transition hover:text-stone-950" href="#securite">Sécurité</a>
                    <a class="transition hover:text-stone-950" href="#faq">FAQ</a>
                </nav>

                <a href="{{ url('/app/login') }}" class="rounded-full bg-stone-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-amber-500 hover:text-stone-950">
                    Se connecter
                </a>
            </header>

            <main>
                <section class="mx-auto grid max-w-7xl items-center gap-14 px-6 pb-24 pt-14 lg:grid-cols-[1.02fr_0.98fr] lg:px-8 lg:pb-32 lg:pt-24">
                    <div>
                        <p class="mb-6 inline-flex items-center gap-2 rounded-full border border-stone-900/10 bg-white/65 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-stone-700 backdrop-blur">
                            <span class="size-2 rounded-full bg-emerald-500"></span>
                            Pilote privé maintenant ouvert
                        </p>

                        <h1 class="max-w-3xl text-5xl font-black leading-[0.98] tracking-[-0.055em] sm:text-6xl lg:text-7xl">
                            Vos rendez-vous n'ont plus besoin de vos messages.
                        </h1>

                        <p class="mt-7 max-w-xl text-lg leading-8 text-stone-600">
                            Reservix transforme vos disponibilités en une page de réservation simple. Vos clients choisissent, votre équipe reste organisée, votre journée avance.
                        </p>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ url('/app/login') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-amber-400 px-6 py-3.5 text-sm font-black text-stone-950 shadow-xl shadow-amber-500/20 transition hover:-translate-y-0.5 hover:bg-amber-300">
                                Accéder à mon espace
                                <span aria-hidden="true">→</span>
                            </a>
                            <a href="#service" class="inline-flex items-center justify-center rounded-full border border-stone-950/15 bg-white/50 px-6 py-3.5 text-sm font-bold text-stone-800 transition hover:border-stone-950/30 hover:bg-white">
                                Voir comment ça marche
                            </a>
                        </div>

                        <ul class="mt-9 flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold text-stone-600" aria-label="Points clés">
                            <li class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Sans compte pour vos clients</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Agenda d'équipe</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Annulation sécurisée</li>
                        </ul>
                    </div>

                    <div class="relative mx-auto w-full max-w-2xl lg:mx-0">
                        <div class="absolute -inset-5 -z-10 rotate-2 rounded-[2.25rem] bg-amber-300/50 blur-sm"></div>
                        <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-stone-950 p-4 text-white shadow-2xl shadow-stone-950/25 sm:p-6">
                            <div class="flex items-center justify-between border-b border-white/10 pb-5">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-300">Aujourd'hui</p>
                                    <p class="mt-1 text-2xl font-black tracking-tight">Une journée claire.</p>
                                </div>
                                <div class="rounded-2xl bg-white/10 px-4 py-3 text-right">
                                    <p class="text-2xl font-black">8</p>
                                    <p class="text-xs text-stone-400">rendez-vous</p>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-4 sm:grid-cols-[0.72fr_1.28fr]">
                                <div class="rounded-2xl bg-white/[0.06] p-4">
                                    <p class="text-xs font-bold text-stone-400">PROCHAIN CRÉNEAU</p>
                                    <p class="mt-3 text-3xl font-black text-amber-300">11:30</p>
                                    <p class="mt-1 text-sm text-stone-300">Coupe & coiffage</p>
                                    <div class="mt-6 h-1.5 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full w-3/4 rounded-full bg-amber-400"></div>
                                    </div>
                                    <p class="mt-2 text-xs text-stone-500">75 % de la journée planifiée</p>
                                </div>

                                <div class="space-y-2.5">
                                    <div class="flex items-center gap-3 rounded-2xl border border-amber-300/30 bg-amber-300/10 p-3.5">
                                        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-amber-300 font-black text-stone-950">09</div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold">Consultation découverte</p>
                                            <p class="text-xs text-stone-400">Maya · 45 min</p>
                                        </div>
                                        <span class="size-2 rounded-full bg-emerald-400"></span>
                                    </div>
                                    <div class="flex items-center gap-3 rounded-2xl bg-white/[0.06] p-3.5">
                                        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/10 font-black">10</div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold">Suivi personnalisé</p>
                                            <p class="text-xs text-stone-400">Nicolas · 60 min</p>
                                        </div>
                                        <span class="rounded-full bg-white/10 px-2 py-1 text-[10px] text-stone-400">confirmé</span>
                                    </div>
                                    <div class="flex items-center gap-3 rounded-2xl bg-white/[0.06] p-3.5 opacity-70">
                                        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/10 font-black">11</div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold">Coupe & coiffage</p>
                                            <p class="text-xs text-stone-400">Sofia · 50 min</p>
                                        </div>
                                        <span class="size-2 rounded-full bg-amber-300"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="service" class="bg-white py-24 sm:py-28">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                            <div>
                                <p class="text-sm font-black uppercase tracking-[0.18em] text-amber-600">Le service</p>
                                <h2 class="mt-4 text-4xl font-black tracking-[-0.045em] sm:text-5xl">De la disponibilité au rendez-vous, sans friction.</h2>
                            </div>
                            <p class="max-w-2xl text-lg leading-8 text-stone-600 lg:justify-self-end">
                                Configurez vos services, vos collaborateurs et leurs horaires. Reservix présente uniquement les créneaux réellement disponibles et centralise les réservations au même endroit.
                            </p>
                        </div>

                        <ol class="mt-16 grid gap-px overflow-hidden rounded-3xl border border-stone-200 bg-stone-200 md:grid-cols-3">
                            <li class="bg-[#faf9f6] p-8 sm:p-10">
                                <span class="text-sm font-black text-amber-600">01</span>
                                <h3 class="mt-8 text-xl font-black">Vous définissez le cadre</h3>
                                <p class="mt-3 leading-7 text-stone-600">Services, durée, équipe et horaires : votre organisation reste la source de vérité.</p>
                            </li>
                            <li class="bg-[#faf9f6] p-8 sm:p-10">
                                <span class="text-sm font-black text-amber-600">02</span>
                                <h3 class="mt-8 text-xl font-black">Le client choisit</h3>
                                <p class="mt-3 leading-7 text-stone-600">Une page claire affiche les options utiles, sans demander au client de créer un compte.</p>
                            </li>
                            <li class="bg-[#faf9f6] p-8 sm:p-10">
                                <span class="text-sm font-black text-amber-600">03</span>
                                <h3 class="mt-8 text-xl font-black">Reservix synchronise</h3>
                                <p class="mt-3 leading-7 text-stone-600">Le créneau est validé, les conflits sont bloqués et votre agenda est immédiatement à jour.</p>
                            </li>
                        </ol>
                    </div>
                </section>

                <section id="avantages" class="py-24 sm:py-28">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="max-w-2xl">
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-amber-700">Les avantages</p>
                            <h2 class="mt-4 text-4xl font-black tracking-[-0.045em] sm:text-5xl">Moins d'administration. Plus de présence.</h2>
                        </div>

                        <div class="mt-14 grid gap-5 md:grid-cols-3">
                            <article class="rounded-3xl border border-stone-900/10 bg-white/65 p-8 shadow-sm backdrop-blur">
                                <div class="grid size-12 place-items-center rounded-2xl bg-amber-300 text-xl font-black" aria-hidden="true">↗</div>
                                <h3 class="mt-7 text-xl font-black">Réservable 24 h sur 24</h3>
                                <p class="mt-3 leading-7 text-stone-600">Les clients réservent quand ils sont prêts, même lorsque vous êtes occupé ou fermé.</p>
                            </article>
                            <article class="rounded-3xl border border-stone-900/10 bg-white/65 p-8 shadow-sm backdrop-blur">
                                <div class="grid size-12 place-items-center rounded-2xl bg-stone-950 text-xl font-black text-white" aria-hidden="true">≡</div>
                                <h3 class="mt-7 text-xl font-black">Une équipe alignée</h3>
                                <p class="mt-3 leading-7 text-stone-600">Chaque collaborateur, service et horaire vit dans un seul espace facile à consulter.</p>
                            </article>
                            <article class="rounded-3xl border border-stone-900/10 bg-white/65 p-8 shadow-sm backdrop-blur">
                                <div class="grid size-12 place-items-center rounded-2xl bg-emerald-500 text-xl font-black text-white" aria-hidden="true">✓</div>
                                <h3 class="mt-7 text-xl font-black">Des règles fiables</h3>
                                <p class="mt-3 leading-7 text-stone-600">Les doubles réservations et les créneaux invalides sont bloqués avant de devenir un problème.</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section id="securite" class="mx-auto max-w-7xl px-6 pb-24 lg:px-8 sm:pb-28">
                    <div class="relative overflow-hidden rounded-[2rem] bg-stone-950 px-7 py-12 text-white sm:px-12 sm:py-16 lg:px-16">
                        <div class="absolute -right-24 -top-24 size-80 rounded-full bg-amber-400/15 blur-3xl"></div>
                        <div class="relative grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                            <div>
                                <p class="text-sm font-black uppercase tracking-[0.18em] text-amber-300">Sécurité</p>
                                <h2 class="mt-4 text-4xl font-black tracking-[-0.045em] sm:text-5xl">Vos données restent à leur place.</h2>
                                <p class="mt-6 max-w-xl text-lg leading-8 text-stone-300">Reservix isole chaque entreprise et protège les actions sensibles du parcours public.</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-6">
                                    <p class="font-black text-amber-300">Espaces isolés</p>
                                    <p class="mt-2 text-sm leading-6 text-stone-400">Les données d'une entreprise ne se mélangent jamais avec celles d'une autre.</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-6">
                                    <p class="font-black text-amber-300">Liens protégés</p>
                                    <p class="mt-2 text-sm leading-6 text-stone-400">Les confirmations et annulations utilisent des liens uniques à durée contrôlée.</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-6">
                                    <p class="font-black text-amber-300">Accès par rôle</p>
                                    <p class="mt-2 text-sm leading-6 text-stone-400">L'espace client et l'administration de la plateforme sont clairement séparés.</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-6">
                                    <p class="font-black text-amber-300">Abus limités</p>
                                    <p class="mt-2 text-sm leading-6 text-stone-400">Les points d'entrée publics sont protégés contre les requêtes excessives.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="faq" class="bg-white py-24 sm:py-28">
                    <div class="mx-auto grid max-w-7xl gap-14 px-6 lg:grid-cols-[0.72fr_1.28fr] lg:px-8">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-amber-600">FAQ</p>
                            <h2 class="mt-4 text-4xl font-black tracking-[-0.045em] sm:text-5xl">Les réponses, avant le rendez-vous.</h2>
                        </div>

                        <div class="divide-y divide-stone-200 border-y border-stone-200">
                            <details class="group py-6" open>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-6 text-lg font-black">
                                    Pour quels types d'activité Reservix est-il conçu ?
                                    <span class="text-amber-600 transition group-open:rotate-45" aria-hidden="true">+</span>
                                </summary>
                                <p class="mt-4 max-w-2xl leading-7 text-stone-600">Pour les entreprises de services qui travaillent sur rendez-vous : salons, studios, consultants, coachs, soins et équipes de terrain.</p>
                            </details>
                            <details class="group py-6">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-6 text-lg font-black">
                                    Mes clients doivent-ils créer un compte ?
                                    <span class="text-amber-600 transition group-open:rotate-45" aria-hidden="true">+</span>
                                </summary>
                                <p class="mt-4 max-w-2xl leading-7 text-stone-600">Non. Ils choisissent un service, un membre de l'équipe et un créneau, puis renseignent leurs coordonnées pour confirmer.</p>
                            </details>
                            <details class="group py-6">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-6 text-lg font-black">
                                    Puis-je gérer plusieurs collaborateurs ?
                                    <span class="text-amber-600 transition group-open:rotate-45" aria-hidden="true">+</span>
                                </summary>
                                <p class="mt-4 max-w-2xl leading-7 text-stone-600">Oui. Chaque collaborateur peut disposer de ses propres horaires, pendant que les réservations restent centralisées.</p>
                            </details>
                            <details class="group py-6">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-6 text-lg font-black">
                                    Comment rejoindre le pilote ?
                                    <span class="text-amber-600 transition group-open:rotate-45" aria-hidden="true">+</span>
                                </summary>
                                <p class="mt-4 max-w-2xl leading-7 text-stone-600">L'accès est pour le moment ouvert sur invitation. Une fois votre espace créé, vous recevez vos identifiants et pouvez configurer votre activité.</p>
                            </details>
                        </div>
                    </div>
                </section>

                <section class="px-6 py-20 lg:px-8 sm:py-24">
                    <div class="mx-auto flex max-w-5xl flex-col items-center rounded-[2rem] bg-amber-300 px-7 py-14 text-center shadow-xl shadow-amber-500/10 sm:px-12 sm:py-16">
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-stone-700">Prêt à reprendre votre temps ?</p>
                        <h2 class="mt-4 max-w-3xl text-4xl font-black tracking-[-0.05em] sm:text-5xl">Votre prochain rendez-vous peut déjà être plus simple.</h2>
                        <a href="{{ url('/app/login') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-stone-950 px-7 py-3.5 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-stone-800">
                            Ouvrir mon espace
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </section>
            </main>

            <footer class="border-t border-stone-900/10">
                <div class="mx-auto flex max-w-7xl flex-col gap-5 px-6 py-8 text-sm text-stone-600 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                    <p class="font-bold text-stone-950">Reservix <span class="font-normal text-stone-500">— La réservation, bien rangée.</span></p>
                    <div class="flex items-center gap-5">
                        <a class="font-semibold transition hover:text-stone-950" href="{{ url('/app/login') }}">Espace client</a>
                        <a class="font-semibold transition hover:text-stone-950" href="{{ url('/admin/login') }}">Administration</a>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
