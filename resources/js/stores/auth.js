import { defineStore } from "pinia";
import axios from "@/services/axios";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        token: localStorage.getItem("token") || null,
        loading: false,
    }),

    actions: {
        async login(email, password) {
            this.loading = true;

            try {
                const res = await axios.post("/login", {
                    email,
                    password,
                });

                this.token = res.data.token;
                localStorage.setItem("token", res.data.token);

                // load user
                await this.fetchUser();

                return true;

            } catch (err) {
                return false;

            } finally {
                this.loading = false;
            }
        },

        async register(name, email, password, password_confirmation) {
            this.loading = true;

            try {
                const res = await axios.post("/register", {
                    name,
                    email,
                    password,
                    password_confirmation
                });

                this.token = res.data.token;
                localStorage.setItem("token", res.data.token);

                await this.fetchUser();

                return true;

            } finally {
                this.loading = false;
            }
        },

        async fetchUser() {
            if (!this.token) return;

            const res = await axios.get("/user");
            this.user = res.data;
        },

        logout() {
            this.user = null;
            this.token = null;
            localStorage.removeItem("token");
        }
    },
});
