<script setup>
import { ref } from "vue";

import AppMenuItem from "./AppMenuItem.vue";

const model = ref([
    {
        items: [
            { label: "Dashboard", icon: "pi pi-fw pi-home", to: "/dashboard" },
        ],
    },
    {
        // label: 'Master',
        items: [
            {
                label: "Rank",
                icon: "pi pi-fw pi-users",
                to: "/rank",
                can: "read rank",
            },
            {
                label: "Position",
                icon: "pi pi-fw pi-users",
                to: "/position",
                can: "read position",
            },
            {
                label: "Craft Size",
                icon: "pi pi-fw pi-users",
                to: "/craft_size",
                can: "read craft_size",
            },
            {
                label: "Vessel",
                icon: "pi pi-fw pi-users",
                to: "/vessel",
                can: "read vessel",
            },
            {
                label: "Junior Vessel Assignment",
                icon: "pi pi-fw pi-shield",
                to: "/juniorVesselAssignments/admin",
                can: "read junior_vessel_assignment",  // or whatever admin permission you use
            },

            {
                label: "Junior Higher Billet",
                icon: "pi pi-fw pi-shield",
                to: "/juniorHigherBillets/admin",
                can: "read junior_higher_billet",  // or whatever admin permission you use
            },
                {
                    label: "Completion Billet",
                    icon: "pi pi-fw pi-shield",
                    to: "/completionBillets/admin",
                    can: "read completion_billet",  // or whatever admin permission you use
                },
                {
                    label: "SLMC Endorsement",
                    icon: "pi pi-fw pi-shield",
                    to: "/slmcEndorsements/admin",
                    can: "read slmc_endorsement",  // or whatever admin permission you use
                },
                {
                    label: "Deliberation for Exo Billet",
                    icon: "pi pi-fw pi-shield",
                    to: "/exoDeliberations/admin",
                    can: "read exo_deliberation",  // or whatever admin permission you use
                },
                {
                    label: "Exo Reassignment",
                    icon: "pi pi-fw pi-shield",
                    to: "/exoReassignments/admin",
                    can: "read exo_reassign",  // or whatever admin permission you use
                },
        {
                    label: "Cat2 Executive Officer",
                    icon: "pi pi-fw pi-shield",
                    to: "/cat2ExoPromotions/admin",
                    can: "read cat2_exo",  // or whatever admin permission you use
                },
        
            
                
        ],
    },
    {
        // label: 'Master',
        items: [
            {
                label: "Sea Duty Record",
                icon: "pi pi-fw pi-file",
                to: "/seaDutyRecords",
                can: "read sea_duty",
            },
            {
                label: "Document",
                icon: "pi pi-fw pi-file",
                to: "/documents",
                can: "read document",
            },
            {
                label: "Chips Report",
                icon: "pi pi-fw pi-file",
                to: "/chipsReports",
                can: "read chipsReport",
            },
            {
                label: "Training and Certification",
                icon: "pi pi-fw pi-file",
                to: "/certificates",
                can: "read certificate",
            },
            {
                label: "User",
                icon: "pi pi-fw pi-user",
                to: "/user",
                can: "read user",
            },
        ],
    },
    {
        // label: 'Setting',
        items: [
            {
                label: "Role",
                icon: "pi pi-fw pi-id-card",
                to: "/role",
                can: "read role",
            },
            {
                label: "Permission",
                icon: "pi pi-fw pi-mobile",
                to: "/permission",
                class: "rotated-icon",
                can: "read permission",
            },
        ],
    },
    {
        items: [
            {
                label: "Profile",
                icon: "pi pi-fw pi-user",
                to: "/personal_data",
            },
        ],
    },
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item
                v-if="!item.separator"
                :item="item"
                :index="i"
            ></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
