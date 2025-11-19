<template>
    <AuthLayout>
        <h1 class="text-2xl font-semibold text-center mb-6">Create Account</h1>

        <form @submit.prevent="submit" class="space-y-4">

            <div>
                <label class="block text-gray-700 text-sm mb-1">Name</label>
                <input type="text" v-model="name"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 transition" />
            </div>

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

            <div>
                <label class="block text-gray-700 text-sm mb-1">Confirm Password</label>
                <input type="password" v-model="confirm"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 transition" />
            </div>

            <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>

            <button
                class="w-full py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                :disabled="loading"
            >
                {{ loading ? "Creating..." : "Register" }}
            </button>

            <p class="text-center text-sm text-gray-600 mt-3">
                Already have an account?
                <router-link to="/login" class="text-indigo-600 hover:underline">Login</router-link>
            </p>
        </form>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from "../layouts/AuthLayout.vue"
import api from "../axios"
import { ref } from "vue"
import { useRouter } from "vue-router"

const name = ref("")
const email = ref("")
const password = ref("")
const confirm = ref("")
const loading = ref(false)
const error = ref("")
const router = useRouter()

const submit = async () => {
    loading.value = true
    error.value = ""

    if (password.value !== confirm.value) {
        error.value = "Passwords do not match"
        loading.value = false
        return
    }

    try {
        const res = await api.post("/register", {
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: confirm.value
        })

        localStorage.setItem("token", res.data.token)
        router.push("/dashboard")
    } catch {
        error.value = "Registration failed"
    }

    loading.value = false
}
</script>
