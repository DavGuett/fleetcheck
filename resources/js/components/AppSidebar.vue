<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    CalendarDays,
    CarFront,
    Fuel,
    LayoutGrid,
    Star,
    Truck,
    UserRound,
} from 'lucide-vue-next';
import {
    Sidebar,
    SidebarContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { index as userList } from '@/routes/users';
import type { NavItem } from '@/types';

type SidebarItem = NavItem & {
    disabled?: boolean;
};

const { isCurrentUrl } = useCurrentUrl();

const mainNavItems: SidebarItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Usuários',
        href: userList(),
        icon: UserRound,
    },
    {
        title: 'Veiculos',
        href: '#',
        icon: CarFront,
        disabled: true,
    },
    {
        title: 'Motoristas',
        href: '#',
        icon: UserRound,
        disabled: true,
    },
    {
        title: 'Manutencao',
        href: '#',
        icon: CalendarDays,
        disabled: true,
    },
    {
        title: 'OS',
        href: '#',
        icon: Star,
        disabled: true,
    },
    {
        title: 'Combustivel',
        href: '#',
        icon: Fuel,
        disabled: true,
    },
];
</script>

<template>
    <Sidebar variant="inset" collapsible="offcanvas" class="border-0">
        <div
            class="h-full rounded-2xl bg-slate-900/95 px-3 py-4 text-slate-200 shadow-xl"
        >
            <SidebarHeader class="space-y-3 p-0">
                <div
                    class="rounded-xl border border-white/10 bg-blue-950/60 p-3"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/25 text-blue-200"
                        >
                            <Truck class="h-5 w-5" />
                        </div>
                        <div>
                            <p
                                class="text-base leading-tight font-semibold text-white"
                            >
                                FleetCheck
                            </p>
                        </div>
                    </div>
                </div>
            </SidebarHeader>

            <SidebarContent class="mt-4 p-0">
                <SidebarMenu class="space-y-1">
                    <SidebarMenuItem
                        v-for="item in mainNavItems"
                        :key="item.title"
                    >
                        <Link
                            v-if="!item.disabled"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition"
                            :class="
                                isCurrentUrl(item.href)
                                    ? 'bg-blue-900/60 text-white'
                                    : 'text-slate-300 hover:bg-white/5 hover:text-white'
                            "
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </Link>

                        <div
                            v-else
                            class="flex cursor-not-allowed items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-slate-500"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </div>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarContent>
        </div>
    </Sidebar>
</template>
