<template>
    <AuthLayout>
        <h1 class="text-2xl font-semibold text-center mb-6">Welcome Back</h1>

        <form @submit.prevent="submit" class="space-y-4">

            <div>
                <label class="block text-gray-700 text-sm mb-1">Email</label>
                <input type="email" v-model="email"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 transition" />
            </div>

            <div>
                <label class="block text-gray-700 text-sm mb-1">Password</label>
                <input type="password" v-model="password"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 transition" />
            </div>

            <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>

            <button
                class="w-full py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                :disabled="loading"
            >
                {{ loading ? "Logging in..." : "Login" }}
            </button>

            <p class="text-center text-sm text-gray-600 mt-3">
                Don’t have an account?
                <router-link to="/register" class="text-indigo-600 hover:underline">Register</router-link>
            </p>
        </form>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from "../layouts/AuthLayout.vue"
import api from "../axios"
import { ref } from "vue"
import { useRouter } from "vue-router"

const email = ref("")
const password = ref("")
const loading = ref(false)
const error = ref("")
const router = useRouter()

const submit = async () => {
    loading.value = true
    error.value = ""

    try {
        const res = await api.post("/login", {
            email: email.value,
            password: password.value,
        })

        localStorage.setItem("token", res.data.token)
        router.push("/dashboard")
    } catch {
        error.value = "Invalid email or password"
    }

    loading.value = false
}
</script>
