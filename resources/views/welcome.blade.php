<x-public.layout
    title="Reservix — La réservation qui travaille avec vous"
    description="Reservix réunit prise de rendez-vous, disponibilités d’équipe et confirmations client dans un parcours simple."
    body-class="bg-[#f7f8fc] text-slate-950 antialiased"
>
    <div class="min-h-screen overflow-hidden">
        <header class="relative z-20 border-b border-slate-200/80 bg-[#f7f8fc]/90 backdrop-blur-xl">
            <div class="mx-auto flex h-18 max-w-7xl items-center justify-between px-5 sm:px-6 lg:px-8">
                <x-public.brand :href="url('/')" />

                <nav class="hidden items-center gap-7 text-sm font-medium text-slate-600 md:flex" aria-label="Navigation principale">
                    <a class="transition hover:text-slate-950" href="#fonctionnement">Fonctionnement</a>
                    <a class="transition hover:text-slate-950" href="#avantages">Avantages</a>
                    <a class="transition hover:text-slate-950" href="#securite">Sécurité</a>
                    <a class="transition hover:text-slate-950" href="#faq">FAQ</a>
                </nav>

                <a class="rx-button rx-button--small rx-button--secondary" href="{{ url('/app/login') }}">
                    Se connecter
                    <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m7.5 4 6 6-6 6"/></svg>
                </a>
            </div>
        </header>

        <main>
            <section class="relative">
                <div class="mx-auto grid max-w-7xl items-center gap-14 px-5 py-16 sm:px-6 sm:py-24 lg:grid-cols-[0.95fr_1.05fr] lg:px-8 lg:py-28">
                    <div class="relative z-10">
                        <p class="rx-eyebrow">Pensé pour les équipes sur rendez-vous</p>
                        <h1 class="mt-6 max-w-3xl text-5xl font-semibold leading-[1.02] tracking-[-0.055em] text-slate-950 sm:text-6xl lg:text-[4.65rem]">
                            Vos clients réservent.<br>
                            <span class="text-indigo-600">Vous gardez le rythme.</span>
                        </h1>
                        <p class="mt-7 max-w-xl text-lg leading-8 text-slate-600">
                            Reservix transforme les horaires de votre équipe en un parcours de réservation clair, puis centralise chaque rendez-vous sans conversation à rallonge.
                        </p>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a class="rx-button rx-button--primary" href="{{ url('/app/login') }}">
                                Accéder à mon espace
                                <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m7.5 4 6 6-6 6"/></svg>
                            </a>
                            <a class="rx-button rx-button--secondary" href="#fonctionnement">Découvrir le parcours</a>
                        </div>

                        <div class="mt-10 flex flex-wrap gap-x-7 gap-y-3 text-sm text-slate-600">
                            <span class="rx-check">Sans compte côté client</span>
                            <span class="rx-check">Email et SMS</span>
                            <span class="rx-check">Agenda multi-équipe</span>
                        </div>
                    </div>

                    <div class="relative mx-auto w-full max-w-2xl lg:mx-0">
                        <div class="absolute -left-16 -top-24 -z-10 size-80 rounded-full bg-indigo-200/55 blur-3xl"></div>
                        <div class="rx-product-window">
                            <div class="rx-product-window__bar">
                                <span class="flex gap-1.5" aria-hidden="true"><i></i><i></i><i></i></span>
                                <span class="truncate">reservix.app/b/atelier-nord/book</span>
                                <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M5 9V6a5 5 0 0 1 10 0v3M4 9h12v8H4V9Z"/></svg>
                            </div>

                            <div class="grid md:grid-cols-[0.82fr_1.18fr]">
                                <div class="border-b border-slate-200 bg-slate-950 p-6 text-white md:border-b-0 md:border-r">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-300">Atelier Nord</p>
                                    <h2 class="mt-4 text-2xl font-semibold tracking-tight">Réservez votre prochain passage.</h2>
                                    <p class="mt-3 text-sm leading-6 text-slate-400">Quatre étapes. Seulement les informations utiles.</p>

                                    <ol class="mt-8 space-y-5 text-sm">
                                        <li class="flex items-center gap-3 text-white"><span class="rx-demo-step rx-demo-step--done">✓</span>Service</li>
                                        <li class="flex items-center gap-3 text-white"><span class="rx-demo-step rx-demo-step--active">2</span>Date et heure</li>
                                        <li class="flex items-center gap-3 text-slate-500"><span class="rx-demo-step">3</span>Coordonnées</li>
                                        <li class="flex items-center gap-3 text-slate-500"><span class="rx-demo-step">4</span>Confirmation</li>
                                    </ol>
                                </div>

                                <div class="bg-white p-5 sm:p-7">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-semibold text-indigo-600">COUPE & COIFFAGE · 45 MIN</p>
                                            <p class="mt-2 text-lg font-semibold text-slate-950">Choisissez un créneau</p>
                                        </div>
                                        <button class="text-sm font-medium text-slate-500" type="button" tabindex="-1">Modifier</button>
                                    </div>

                                    <div class="mt-6 flex gap-2 overflow-hidden" aria-hidden="true">
                                        <div class="rx-demo-date"><span>MAR</span><strong>18</strong></div>
                                        <div class="rx-demo-date rx-demo-date--active"><span>MER</span><strong>19</strong></div>
                                        <div class="rx-demo-date"><span>JEU</span><strong>20</strong></div>
                                        <div class="rx-demo-date"><span>VEN</span><strong>21</strong></div>
                                    </div>

                                    <p class="mt-7 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Matin</p>
                                    <div class="mt-3 grid grid-cols-3 gap-2">
                                        <span class="rx-demo-slot">09:00</span>
                                        <span class="rx-demo-slot rx-demo-slot--active">09:45</span>
                                        <span class="rx-demo-slot">10:30</span>
                                    </div>
                                    <p class="mt-5 text-xs text-slate-500">Avec Léa · Confirmation instantanée</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="border-y border-slate-200 bg-white" aria-label="Fonctions principales">
                <div class="mx-auto grid max-w-7xl divide-y divide-slate-200 px-5 sm:px-6 md:grid-cols-3 md:divide-x md:divide-y-0 lg:px-8">
                    <div class="py-7 md:pr-8"><p class="text-sm font-semibold text-slate-950">Disponibilités fiables</p><p class="mt-1 text-sm leading-6 text-slate-500">Horaires, absences et affectations sont pris en compte.</p></div>
                    <div class="py-7 md:px-8"><p class="text-sm font-semibold text-slate-950">Confirmations automatiques</p><p class="mt-1 text-sm leading-6 text-slate-500">Le client reçoit les détails par email et par SMS.</p></div>
                    <div class="py-7 md:pl-8"><p class="text-sm font-semibold text-slate-950">Gestion centralisée</p><p class="mt-1 text-sm leading-6 text-slate-500">L’équipe retrouve ses rendez-vous dans un même espace.</p></div>
                </div>
            </section>

            <section id="fonctionnement" class="scroll-mt-20 py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                    <div class="grid gap-14 lg:grid-cols-[0.8fr_1.2fr] lg:gap-24">
                        <div>
                            <p class="rx-eyebrow">Un parcours qui va droit au but</p>
                            <h2 class="mt-5 text-4xl font-semibold tracking-[-0.045em] text-slate-950 sm:text-5xl">Moins de gestion autour du rendez-vous.</h2>
                            <p class="mt-6 text-lg leading-8 text-slate-600">Reservix relie ce que vous configurez à ce que votre client peut réellement réserver.</p>
                        </div>

                        <ol class="border-t border-slate-300">
                            <li class="rx-process-row">
                                <span>01</span>
                                <div><h3>Cadrez votre activité</h3><p>Ajoutez vos services, leurs durées, les membres de l’équipe et les horaires de chacun.</p></div>
                            </li>
                            <li class="rx-process-row">
                                <span>02</span>
                                <div><h3>Partagez votre lien</h3><p>Le client choisit un service, une date et un créneau disponible, sans créer de compte.</p></div>
                            </li>
                            <li class="rx-process-row">
                                <span>03</span>
                                <div><h3>Laissez Reservix suivre</h3><p>Le rendez-vous est enregistré, les conflits sont bloqués et les confirmations partent automatiquement.</p></div>
                            </li>
                        </ol>
                    </div>
                </div>
            </section>

            <section id="avantages" class="scroll-mt-20 bg-slate-950 py-24 text-white sm:py-32">
                <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        <p class="rx-eyebrow rx-eyebrow--dark">Ce qui change au quotidien</p>
                        <h2 class="mt-5 text-4xl font-semibold tracking-[-0.045em] sm:text-5xl">Un outil discret quand tout va bien. Précis quand ça compte.</h2>
                    </div>

                    <div class="mt-16 grid border-y border-white/15 md:grid-cols-3 md:divide-x md:divide-white/15">
                        <article class="py-9 md:pr-9">
                            <svg class="rx-feature-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4v3M19 4v3M4 9h16M5 6h14a1 1 0 0 1 1 1v13H4V7a1 1 0 0 1 1-1Z"/><path d="m8 14 2.2 2.2L16 11"/></svg>
                            <h3 class="mt-7 text-xl font-semibold">Une disponibilité juste</h3>
                            <p class="mt-3 leading-7 text-slate-400">Le parcours tient compte des horaires, absences et services réellement assurés par chaque personne.</p>
                        </article>
                        <article class="py-9 md:px-9">
                            <svg class="rx-feature-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v12H4V6Z"/><path d="m5 7 7 6 7-6M17 4l3 3-3 3"/></svg>
                            <h3 class="mt-7 text-xl font-semibold">Un client bien informé</h3>
                            <p class="mt-3 leading-7 text-slate-400">Email, SMS, confirmation et lien d’annulation réduisent les doutes après la réservation.</p>
                        </article>
                        <article class="py-9 md:pl-9">
                            <svg class="rx-feature-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 20v-7M12 20V4M19 20v-11"/><path d="M3 20h18"/></svg>
                            <h3 class="mt-7 text-xl font-semibold">Une équipe alignée</h3>
                            <p class="mt-3 leading-7 text-slate-400">Les rendez-vous, réglages et retours utilisateurs sont accessibles depuis l’espace de gestion.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="securite" class="scroll-mt-20 bg-white py-24 sm:py-32">
                <div class="mx-auto grid max-w-7xl gap-14 px-5 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:px-8 lg:gap-24">
                    <div>
                        <p class="rx-eyebrow">Sécurité intégrée</p>
                        <h2 class="mt-5 text-4xl font-semibold tracking-[-0.045em] text-slate-950 sm:text-5xl">La confiance ne devrait pas être une option.</h2>
                        <p class="mt-6 text-lg leading-8 text-slate-600">Les protections utiles sont présentes dans le produit, du cloisonnement des entreprises aux actions publiques sensibles.</p>
                    </div>

                    <ul class="grid gap-x-10 gap-y-8 sm:grid-cols-2">
                        <li class="rx-security-item"><span>01</span><div><h3>Données isolées</h3><p>Chaque entreprise accède uniquement à son propre espace.</p></div></li>
                        <li class="rx-security-item"><span>02</span><div><h3>Liens signés</h3><p>Confirmation et annulation passent par des liens uniques et limités.</p></div></li>
                        <li class="rx-security-item"><span>03</span><div><h3>Accès par rôle</h3><p>L’administration de la plateforme reste séparée de l’espace entreprise.</p></div></li>
                        <li class="rx-security-item"><span>04</span><div><h3>Abus limités</h3><p>Les réservations publiques sont protégées contre les requêtes excessives.</p></div></li>
                    </ul>
                </div>
            </section>

            <section id="faq" class="scroll-mt-20 border-t border-slate-200 py-24 sm:py-32">
                <div class="mx-auto grid max-w-7xl gap-14 px-5 sm:px-6 lg:grid-cols-[0.7fr_1.3fr] lg:px-8 lg:gap-24">
                    <div>
                        <p class="rx-eyebrow">Questions fréquentes</p>
                        <h2 class="mt-5 text-4xl font-semibold tracking-[-0.045em] text-slate-950 sm:text-5xl">Avant de commencer.</h2>
                    </div>

                    <div class="border-t border-slate-300">
                        <details class="rx-faq" open><summary>À qui s’adresse Reservix ?<span aria-hidden="true">+</span></summary><p>Aux entreprises de services qui organisent leur activité sur rendez-vous : salons, studios, consultants, coachs, soins et équipes de terrain.</p></details>
                        <details class="rx-faq"><summary>Mes clients doivent-ils créer un compte ?<span aria-hidden="true">+</span></summary><p>Non. Ils utilisent directement votre page publique pour choisir leur service, leur date et leur créneau.</p></details>
                        <details class="rx-faq"><summary>Peut-on gérer plusieurs collaborateurs ?<span aria-hidden="true">+</span></summary><p>Oui. Chaque collaborateur peut avoir ses services, ses horaires et ses absences, tout en partageant le même espace d’activité.</p></details>
                        <details class="rx-faq"><summary>Les confirmations sont-elles automatiques ?<span aria-hidden="true">+</span></summary><p>Oui, dès que les fournisseurs email et SMS sont configurés. Les envois sont traités en file avec plusieurs tentatives en cas d’échec.</p></details>
                    </div>
                </div>
            </section>

            <section class="px-5 pb-24 sm:px-6 sm:pb-32 lg:px-8">
                <div class="mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-indigo-600 px-6 py-14 text-center text-white sm:px-12 sm:py-18">
                    <p class="text-sm font-semibold text-indigo-200">Votre organisation est déjà là.</p>
                    <h2 class="mx-auto mt-4 max-w-3xl text-4xl font-semibold tracking-[-0.045em] sm:text-5xl">Donnez-lui un parcours de réservation à sa hauteur.</h2>
                    <a class="rx-button rx-button--light mt-8" href="{{ url('/app/login') }}">
                        Ouvrir mon espace
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m7.5 4 6 6-6 6"/></svg>
                    </a>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <x-public.brand :href="url('/')" />
                <p>La réservation, sans les allers-retours.</p>
                <div class="flex gap-5">
                    <a class="transition hover:text-slate-950" href="{{ url('/app/login') }}">Espace entreprise</a>
                    <a class="transition hover:text-slate-950" href="{{ url('/admin/login') }}">Administration</a>
                </div>
            </div>
        </footer>
    </div>
</x-public.layout>
