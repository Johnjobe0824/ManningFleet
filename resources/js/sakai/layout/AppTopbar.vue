<script setup>
import { useLayout } from '@/sakai/layout/composables/layout';
import AppConfigurator from './AppConfigurator.vue';
import NavLink from "@/Components/NavLink.vue";
import DropdownLink from "@/Components/DropdownLink.vue";


const { onMenuToggle, toggleDarkMode, isDarkTheme } = useLayout();
</script>

<template>
    <div class="layout-topbar bg-blue-800">
        <div class="layout-topbar-logo-container">
            <button class="layout-menu-button layout-topbar-action" @click="onMenuToggle">
                <i class="pi pi-bars text-white"></i>
            </button>
            <nav-link href="/dashboard" class="layout-topbar-logo shrink-0 flex items-center">
                <div class="flex items-center">
                    <img src="/demo/images/cgfleet_logo-removebg-preview.png" class="m-2 h-10 w-auto fill-current text-gray-800" alt="CGFLEET MANNING GROUP" />
                    <span class="text-white text-xl font-bold">MANNING GROUP CGFLEET</span>
                </div>

            </nav-link>
        </div>

        <div class="layout-topbar-actions">
            <div class="layout-config-menu">
                <!-- <button type="button" class="layout-topbar-action" @click="toggleDarkMode">
                    <i :class="['pi', { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme }]"></i>
                </button> -->
                <!-- <div class="relative">
                    <button
                        v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }"
                        type="button"
                        class="layout-topbar-action layout-topbar-action-highlight"
                    >
                        <i class="pi pi-palette"></i>
                    </button>
                    <AppConfigurator />
                </div> -->
            </div>

            <button
                class="layout-topbar-menu-button layout-topbar-action"
                v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }"
            >
                <i class="pi pi-ellipsis-v"></i>
            </button>

            <div class="layout-topbar-menu hidden lg:block">
                <div class="layout-topbar-menu-content">
                    <div class="relative">
                        <button type="button" class="layout-topbar-action" v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }">
                            <i class="pi pi-user text-white"></i>
                            <span>Profile</span>
                        </button>
                        <div class="hidden bg-white shadow-md absolute right-0 mt-2 w-48 py-2 rounded-md">
                            <!-- <DropdownLink :href="route('profile.edit')"  as="button">
                                            My Profile
                                        </DropdownLink> -->
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                            <!-- <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
