<template>
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
            <p>
                {{ post.body }}
            </p>
        </div>
    </section>
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

    <section style="background-color: #eee">
        <div class="container py-5">
            <div class="row d-flex justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-4">
                    <div class="card" id="chat1" style="border-radius: 15px">
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
                            <!-- Chat messages -->
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
</template>
<script>
import axios from "axios";
import Pusher from "pusher-js";

export default {
    emits: ["updateSidebar"],
    props: ["slug"],
    data() {
        return {
            post: {},
            relatedPosts: [],
            messages: [],
            text: "",
            currentUser: null,
            pusher: null,
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
                this.text = "";
            } catch (error) {
                console.error("Error submitting message:", error);
            }
        },
        async getMessages() {
            return axios.get(`/api/posts/${this.slug}/messages`);
        },
    },
    async mounted() {
        try {
            const [
                postResponse,
                relatedPostsResponse,
                userResponse,
                messagesResponse,
            ] = await Promise.all([
                axios.get("/api/posts/" + this.slug),
                axios.get("/api/related-posts/" + this.slug),
                axios.get("/api/user"),
                axios.get(`/api/posts/${this.slug}/messages`),
            ]);

            this.post = postResponse.data.data;
            this.relatedPosts = relatedPostsResponse.data.data;
            this.messages = messagesResponse.data.data;
            this.currentUser = userResponse.data;

            if (!this.currentUser || !this.currentUser.id) {
                console.error(
                    "User data is missing or incomplete:",
                    this.currentUser
                );
                return;
            }

            // Initialize Pusher
            // Echo.private(`chat`).listen("NewMessage", (e) => {
            //     console.log(e);
            //     this.messages.push(e.message);
            // });

            window.Echo.private("chat").listen("NewMessage", (e) => {
                console.log(e.message);
                this.messages.push(e.message);
                this.messages = this.getMessages.data.data;
            });

            // const channel = this.pusher.subscribe(
            //     `messages.${this.currentUser.id}`
            // );

            // channel.bind("NewMessage", (data) => {
            //     console.log("New message received:", data);
            //     this.messages.push(data.message);
            // });
        } catch (error) {
            console.error("Error fetching data:", error);
        }
    },
};
</script>

<style>
#chat1 .form-outline .form-control ~ .form-notch div {
    pointer-events: none;
    border: 1px solid;
    border-color: #eee;
    box-sizing: border-box;
    background: transparent;
}

#chat1 .form-outline .form-control ~ .form-notch .form-notch-leading {
    left: 0;
    top: 0;
    height: 100%;
    border-right: none;
    border-radius: 0.65rem 0 0 0.65rem;
}

#chat1 .form-outline .form-control ~ .form-notch .form-notch-middle {
    flex: 0 0 auto;
    max-width: calc(100% - 1rem);
    height: 100%;
    border-right: none;
    border-left: none;
}

#chat1 .form-outline .form-control ~ .form-notch .form-notch-trailing {
    flex-grow: 1;
    height: 100%;
    border-left: none;
    border-radius: 0 0.65rem 0.65rem 0;
}

#chat1 .form-outline .form-control:focus ~ .form-notch .form-notch-leading {
    border-top: 0.125rem solid #39c0ed;
    border-bottom: 0.125rem solid #39c0ed;
    border-left: 0.125rem solid #39c0ed;
}

#chat1 .form-outline .form-control:focus ~ .form-notch .form-notch-leading,
#chat1 .form-outline .form-control.active ~ .form-notch .form-notch-leading {
    border-right: none;
    transition: all 0.2s linear;
}

#chat1 .form-outline .form-control:focus ~ .form-notch .form-notch-middle {
    border-bottom: 0.125rem solid;
    border-color: #39c0ed;
}

#chat1 .form-outline .form-control:focus ~ .form-notch .form-notch-middle,
#chat1 .form-outline .form-control.active ~ .form-notch .form-notch-middle {
    border-top: none;
    border-right: none;
    border-left: none;
    transition: all 0.2s linear;
}

#chat1 .form-outline .form-control:focus ~ .form-notch .form-notch-trailing {
    border-top: 0.125rem solid #39c0ed;
    border-bottom: 0.125rem solid #39c0ed;
    border-right: 0.125rem solid #39c0ed;
}

#chat1 .form-outline .form-control:focus ~ .form-notch .form-notch-trailing,
#chat1 .form-outline .form-control.active ~ .form-notch .form-notch-trailing {
    border-left: none;
    transition: all 0.2s linear;
}

#chat1 .form-outline .form-control:focus ~ .form-label {
    color: #39c0ed;
}

#chat1 .form-outline .form-control ~ .form-label {
    color: #bfbfbf;
}
</style>
