<template>
    <div>
        <!-- Display post details -->
        <section class="single-blog-post">
            <h1>{{ post?.title }}</h1>
            <p class="time-and-author">
                {{ post.created_at }}
                <span>Written By {{ post.user }}</span>
            </p>
            <div v-for="(image, index) in post.images" :key="index">
                <img
                    :src="image?.url"
                    :alt="`Image ${index + 1}`"
                    style="width: 150px; height: 150px"
                />
            </div>
            <div class="about-text">
                <p>{{ post.body }}</p>
            </div>
        </section>

        <!-- Display related posts -->
        <section class="recommended">
            <p>Related</p>
            <div class="recommended-cards">
                <router-link
                    v-for="relatedPost in relatedPosts"
                    :key="relatedPost.id"
                    :to="{
                        name: 'SingleBlog',
                        params: { slug: relatedPost.slug },
                    }"
                >
                    <div class="recommended-card">
                        <img
                            :src="`/${relatedPost.imagePath}`"
                            alt=""
                            loading="lazy"
                        />
                        <h4>{{ relatedPost.title }}</h4>
                    </div>
                </router-link>
            </div>
        </section>

        <!-- Display chat messages -->
        <section style="background-color: #eee">
            <div class="container py-5">
                <div class="row d-flex justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-4">
                        <div
                            class="card"
                            id="chat1"
                            style="border-radius: 15px"
                        >
                            <div
                                class="card-header d-flex justify-content-between align-items-center p-3 bg-info text-white border-bottom-0"
                                style="
                                    border-top-left-radius: 15px;
                                    border-top-right-radius: 15px;
                                "
                            >
                                <p class="mb-0 fw-bold">Mini chat</p>
                            </div>
                            <div class="card-body">
                                <!-- Display chat messages -->
                                <div
                                    v-for="(message, index) in messages"
                                    :key="index"
                                    :class="'d-flex justify-content-start mb-4'"
                                >
                                    <div>
                                        <p class="small mb-0">
                                            {{ message.text }}
                                        </p>
                                    </div>
                                </div>
                                <!-- End Chat messages -->

                                <!-- Chat input form -->
                                <div class="form-outline">
                                    <form @submit.prevent="submit">
                                        <textarea
                                            v-model="text"
                                            class="form-control"
                                            id="textAreaExample"
                                            rows="4"
                                        ></textarea>
                                        <input
                                            class="add-post-btn"
                                            type="submit"
                                            value="Submit"
                                        />
                                    </form>
                                </div>
                                <!-- End Chat input form -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import axios from "axios";
import Pusher from "pusher-js";

export default {
    props: ["slug"],
    data() {
        return {
            post: {},
            relatedPosts: [],
            messages: [],
            text: "",
        };
    },
    methods: {
        async submit() {
            const formData = new FormData();
            formData.append("text", this.text);

            try {
                await axios.post(
                    `/api/posts/${this.post.id}/messages`,
                    formData,
                    {
                        headers: { "content-type": "multipart/form-data" },
                    }
                );
                // Update local state with the new message
                this.messages.push({ text: this.text });
                // Clear the input field
                this.text = "";
            } catch (error) {
                console.error("Error submitting message:", error);
            }
        },
        async fetchData() {
            try {
                const [postResponse, relatedPostsResponse, messagesResponse] =
                    await Promise.all([
                        axios.get(`/api/posts/${this.slug}`),
                        axios.get(`/api/related-posts/${this.slug}`),
                        axios.get(`/api/posts/${this.slug}/messages`),
                    ]);

                this.post = postResponse.data.data;
                this.relatedPosts = relatedPostsResponse.data.data;
                this.messages = messagesResponse.data.data;
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        },
        initializePusher() {
            window.Echo.private("chat").listen("NewMessage", (e) => {
                console.log(e.message);
                // Add the new message to the local state
                this.messages.push(e.message);
            });
        },
    },
    mounted() {
        this.fetchData(); // Fetch initial data
        this.initializePusher(); // Initialize Pusher for real-time updates
    },
};
</script>

<style>
/* Your existing styles */
</style>
