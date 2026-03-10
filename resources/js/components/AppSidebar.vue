<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ClipboardList,
    DoorOpen,
    GraduationCap,
    LayoutGrid,
    Library,
    MonitorPlay,
    Settings,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Kategori',
        href: '/categories',
        icon: Library,
    },
    {
        title: 'Soal',
        href: '/questions',
        icon: BookOpen,
    },
    {
        title: 'Ujian',
        href: '/exams',
        icon: ClipboardList,
    },
    {
        title: 'Sesi Ujian',
        href: '/exam-sessions',
        icon: MonitorPlay,
    },
    {
        title: 'Siswa',
        href: '/students',
        icon: GraduationCap,
    },
    {
        title: 'Kelas',
        href: '/classes',
        icon: Users,
    },
    {
        title: 'Ruangan',
        href: '/rooms',
        icon: DoorOpen,
    },
    {
        title: 'Pengguna',
        href: '/users',
        icon: ShieldCheck,
    },
    {
        title: 'Pengaturan',
        href: '/admin/settings',
        icon: Settings,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

