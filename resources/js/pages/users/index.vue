<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { PropType } from 'vue';
import { index } from '@/routes/users';
import type { User } from '@/types/auth';

const props = defineProps({
    users: Object as PropType<User[]>,
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Usuários',
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Usuários" />
    <h1>User list works!</h1>
    <div v-for="user in props.users" :key="user.id">
        <p>{{ user.name }}</p>
        <p>{{ user.email }}</p>
    </div>
</template>

<style scoped></style>
