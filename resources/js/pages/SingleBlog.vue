<template>
    <section class="single-blog-post">
        <h1>{{ post.title }}</h1>

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
                                :class="{
                                    'd-flex justify-content-start mb-4':
                                        message.user_id !== currentUser.id,
                                    'd-flex justify-content-end mb-4':
                                        message.user_id === currentUser.id,
                                }"
                            >
                                <div
                                    :class="{
                                        'p-3 ms-3':
                                            message.user_id !== currentUser.id,
                                        'p-3 me-3 border':
                                            message.user_id === currentUser.id,
                                    }"
                                    :style="{
                                        borderRadius:
                                            message.user_id !== currentUser.id
                                                ? '15px'
                                                : '15px',
                                        backgroundColor:
                                            message.user_id !== currentUser.id
                                                ? 'rgba(57, 192, 237, 0.2)'
                                                : '#fbfbfb',
                                    }"
                                >
                                    <p class="small mb-0">
                                        {{
                                            message.user
                                                ? message.user.id ===
                                                  currentUser.id
                                                    ? "You"
                                                    : message.user.name
                                                : message.admin
                                                ? message.admin.id ===
                                                  currentUser.id
                                                    ? "You"
                                                    : message.admin.name
                                                : ""
                                        }}:
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
import Echo from "laravel-echo";
import Pusher from "pusher-js";
window.Pusher = Pusher;

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
            echo: null,
        };
    },

    methods: {
        submit() {
            const formData = new FormData();

            formData.append("text", this.text);
            axios
                .post(`/api/posts/${this.post.id}/messages`, formData, {
                    headers: { "content-type": "multipart/form-data" },
                })
                .then(() => {
                    this.fields = {};
                    this.urls = [];
                    this.images = [];
                    this.fields.category_id = "";
                    this.success = true;
                    this.errors = {};

                    setTimeout(() => {
                        this.success = false;
                    }, 2500);
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                    this.success = false;
                });
        },
    },
    mounted() {
        axios
            .get("/api/posts/" + this.slug)
            .then((response) => (this.post = response.data.data))
            .catch((error) => {
                console.log(error);
            });

        axios
            .get("/api/related-posts/" + this.slug)
            .then((response) => (this.relatedPosts = response.data.data))
            .catch((error) => {
                console.log(error);
            });
        axios
            .get(`/api/posts/${this.slug}/messages`)
            .then((response) => (this.messages = response.data.data))
            .catch((error) => {
                console.log(error);
            });
        axios
            .get("/api/user")
            .then((response) => {
                this.currentUser = response.data;
            })
            .catch((error) => {
                console.log(error);
            });

        window.Echo = new Echo({
            broadcaster: "pusher",
            key: "local",
            wsHost: "127.0.0.1",
            wsPort: 6001,
            cluster: "mt1",
            forceTLS: false,
            disableStats: true,
        });

        // Listen for the message created event
        this.echo.channel("messages").listen(".message.created", (event) => {
            this.messages.push(event.message);
        });
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
