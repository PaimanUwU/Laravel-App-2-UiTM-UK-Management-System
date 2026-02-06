<aside id="sidebar" class="flex flex-col justify-between h-screen overflow-hidden">

    <div class="flex flex-col">
        <div class="flex flex-initial items-center justify-between h-16 px-6 border-b border-zinc-200">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <x-application-logo class="w-8 h-8 fill-current text-zinc-800" />
                <span class="text-lg font-bold text-zinc-900">{{ config('app.name', 'UK UITM') }}</span>
            </a>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-accent/10 text-accent' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900' }}">
                <flux:icon.home variant="outline" class="w-5 h-5 mr-3" />
                {{ __('Dashboard') }}
            </a>

            @auth
                @role('system_admin|doctor|staff')
                <a href="{{ route('appointments.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('appointments.*') ? 'bg-accent/10 text-accent' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900' }}">
                    <flux:icon.calendar variant="outline" class="w-5 h-5 mr-3" />
                    {{ __('Appointments') }}
                </a>
                <a href="{{ route('patients.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('patients.*') ? 'bg-accent/10 text-accent' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900' }}">
                    <flux:icon.users variant="outline" class="w-5 h-5 mr-3" />
                    {{ __('Patients') }}
                </a>
                <a href="{{ route('doctors.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('doctors.*') ? 'bg-accent/10 text-accent' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900' }}">
                    <flux:icon.user-group variant="outline" class="w-5 h-5 mr-3" />
                    {{ __('Doctors') }}
                </a>
                @endrole

                @role('system_admin|head_office|staff')
                <a href="{{ route('inventory.mex.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('inventory.*') ? 'bg-accent/10 text-accent' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900' }}">
                    <flux:icon.archive-box variant="outline" class="w-5 h-5 mr-3" />
                    {{ __('Inventory') }}
                </a>
                @endrole

                @role('doctor')
                <a href="{{ route('doctor.dashboard') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('doctor.dashboard') ? 'bg-accent/10 text-accent' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900' }}">
                    <flux:icon.presentation-chart-line variant="outline" class="w-5 h-5 mr-3" />
                    {{ __('Team Oversight') }}
                </a>
                @endrole

                @role('head_office')
                <a href="{{ route('ho.analytics') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('ho.*') ? 'bg-accent/10 text-accent' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900' }}">
                    <flux:icon.chart-bar variant="outline" class="w-5 h-5 mr-3" />
                    {{ __('Analytics') }}
                </a>
                @endrole

                @role('system_admin')
                <div class="pt-4 mt-4 border-t border-zinc-200">
                    <span
                        class="px-3 text-xs font-semibold text-zinc-600 uppercase tracking-wider">{{ __('Administration') }}</span>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-accent/10 text-accent' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900' }}">
                            <flux:icon.users variant="outline" class="w-5 h-5 mr-3" />
                            {{ __('Users') }}
                        </a>
                        <a href="{{ route('admin.audit-logs') }}"
                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.audit-logs') ? 'bg-accent/10 text-accent' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900' }}">
                            <flux:icon.clipboard-document-list variant="outline" class="w-5 h-5 mr-3" />
                            {{ __('Audit Logs') }}
                        </a>
                        <a href="{{ route('admin.settings') }}"
                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.settings') ? 'bg-accent/10 text-accent' : 'text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900' }}">
                            <flux:icon.cog-6-tooth variant="outline" class="w-5 h-5 mr-3" />
                            {{ __('Settings') }}
                        </a>
                    </div>
                </div>
                @endrole
            @endauth
        </nav>
    </div>

    <div class="justify-self-end p-4 border-t border-zinc-200 ">
        @auth
            <flux:dropdown align="end" position="top">
                <button
                    class="flex items-center w-full px-3 py-2 text-sm font-medium text-zinc-700 rounded-md hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-zinc-100 text-zinc-700 font-bold mr-3">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span class="truncate">{{ Auth::user()->name }}</span>
                    <flux:icon.chevron-up class="w-4 h-4 ml-auto" />
                </button>

                <flux:menu>
                    <flux:menu.item icon="user" href="{{ route('profile.edit') }}">{{ __('Profile') }}</flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        @endauth
    </div>
</aside>