<script setup>
import { watch, ref, onMounted } from "vue";
import axios from "@/services/axios";
import { useAuthStore } from "@/stores/auth";
import Echo from "laravel-echo";

const auth = useAuthStore();

const loading = ref(true);
const balance = ref(0);
const transactions = ref([]);
const profile = ref({}); // New profile info

// Transfer form
const receiverId = ref("");
const amount = ref("");
const transferLoading = ref(false);
const formErrors = ref({});

// Load dashboard data
const loadDashboard = async () => {
    loading.value = true;

    try {
        // Load transactions + balance
        const res = await axios.get("/transactions");
        balance.value = res.data.balance;
        transactions.value = res.data.transactions.data;

        // Load user profile
        const profileRes = await axios.get("/user");
        profile.value = profileRes.data;

    } catch (err) {
        console.error(err);
    }

    loading.value = false;
};

// Submit transfer
const submitTransfer = async () => {
    formErrors.value = {};
    transferLoading.value = true;

    try {
        await axios.post("/transactions", {
            receiver_id: receiverId.value,
            amount: amount.value,
        });

        receiverId.value = "";
        amount.value = "";

    } catch (err) {
        if (err.response?.status === 422) {
            formErrors.value = err.response.data.errors;
        }
    }

    transferLoading.value = false;
};


onMounted(() => {
    loadDashboard();

    // Wait until auth.user is loaded
    const unwatch = watch(
        () => auth.user,
        (user) => {
            if (user) {
                window.Echo.private(`user.${user.id}`)
                    .listen("transaction.created", (e) => {
                        console.log("Received event:", e);
                        balance.value = e.balance;
                        transactions.value.unshift(e.transaction);
                    });

                unwatch(); // stop watching
            }
        },
        { immediate: true }
    );
});

</script>

<template>
    <div class="max-w-4xl mx-auto p-6 space-y-8">

        <!-- Profile Info -->
        <div class="bg-white p-6 rounded-xl shadow flex flex-col md:flex-row items-start md:items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">Hello, {{ profile.name }}</h2>
                <p class="text-gray-600 mt-1">{{ profile.email }}</p>
                <p class="text-gray-500 text-sm mt-1">Joined: {{ new Date(profile.created_at).toLocaleDateString() }}</p>
            </div>

            <div class="mt-4 md:mt-0 text-right">
                <p class="text-gray-700 font-medium">User ID: {{ profile.id }}</p>
            </div>
        </div>

        <!-- Balance -->
        <div class="bg-white p-4 rounded-xl shadow">
            <h2 class="text-lg font-semibold">Current Balance</h2>
            <p class="text-3xl font-bold mt-2">{{ balance }}</p>
        </div>

        <!-- Transfer Form -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-semibold mb-4">Send Money</h2>

            <form @submit.prevent="submitTransfer" class="space-y-4">
                <div>
                    <label class="block font-medium mb-1">Recipient User ID</label>
                    <input
                        type="number"
                        v-model="receiverId"
                        class="w-full p-2 border rounded-md"
                        placeholder="Enter user ID"
                    />
                    <p class="text-red-600 text-sm" v-if="formErrors.receiver_id">
                        {{ formErrors.receiver_id[0] }}
                    </p>
                </div>

                <div>
                    <label class="block font-medium mb-1">Amount</label>
                    <input
                        type="number"
                        step="0.01"
                        v-model="amount"
                        class="w-full p-2 border rounded-md"
                        placeholder="Enter amount"
                    />
                    <p class="text-red-600 text-sm" v-if="formErrors.amount">
                        {{ formErrors.amount[0] }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="transferLoading"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ transferLoading ? "Sending..." : "Send" }}
                </button>
            </form>
        </div>

        <!-- Transaction History -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-semibold mb-4">Transaction History</h2>

            <table class="min-w-full border">
                <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Type</th>
                    <th class="p-2 border">Amount</th>
                    <th class="p-2 border">Commission</th>
                    <th class="p-2 border">Counterparty</th>
                    <th class="p-2 border">Date</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="t in transactions" :key="t.id" class="border-b">
                    <td class="p-2 border">{{ t.id }}</td>
                    <td class="p-2 border">
                        <span
                            class="px-2 py-1 rounded text-white"
                            :class="t.sender_id === auth.user.id ? 'bg-red-500' : 'bg-green-600'"
                        >
                            {{ t.sender_id === auth.user.id ? "Sent" : "Received" }}
                        </span>
                    </td>
                    <td class="p-2 border">{{ t.amount }}</td>
                    <td class="p-2 border">{{ t.commission_fee }}</td>
                    <td class="p-2 border">
                        <span v-if="t.sender_id === auth.user.id">
                            → User {{ t.receiver_id }}
                        </span>
                        <span v-else>
                            ← User {{ t.sender_id }}
                        </span>
                    </td>
                    <td class="p-2 border">{{ new Date(t.created_at).toLocaleString() }}</td>
                </tr>
                </tbody>
            </table>
        </div>

    </div>
</template>
