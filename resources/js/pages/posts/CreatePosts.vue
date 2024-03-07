<template>
    <main class="create-post">
        <div class="container">
            <h1>Create Posts!</h1>
            <!-- success message -->
            <div class="success-msg" v-if="success">
                <i class="fa fa-check"></i>
                Post created successfully
            </div>
            <!-- Contact Form -->
            <div class="contact-form">
                <form @submit.prevent="submit">
                    <!-- Title -->
                    <label for="title"><span>Title</span></label>
                    <input type="text" id="title" v-model="fields.title" />
                    <span v-if="errors.title" class="error">{{
                        errors.title[0]
                    }}</span>
                    <br />

                    <!-- Image -->
                    <label for="image"><span>Image</span></label>
                    <input
                        type="file"
                        id="image"
                        @change="grabFile"
                        accept="image/*"
                        multiple
                    />
                    <span v-if="errors.file" class="error">{{
                        errors.file[0]
                    }}</span>
                    <div class="preview" v-if="urls.length > 0">
                        <div v-for="(url, index) in urls" :key="index">
                            <img :src="url" alt="" />
                        </div>
                    </div>
                    <br />

                    <!-- Drop down -->
                    <label for="categories"
                        ><span>Choose a category:</span></label
                    >
                    <select v-model="fields.category_id" id="categories">
                        <option disabled value="">Select option</option>
                        <option
                            :value="category.id"
                            v-for="category in categories"
                            :key="category.id"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                    <span v-if="errors.category_id" class="error">{{
                        errors.category_id[0]
                    }}</span>
                    <br />

                    <!-- Body-->
                    <label for="body"><span>Body</span></label>
                    <textarea id="body" v-model="fields.body"></textarea>
                    <span v-if="errors.body" class="error">{{
                        errors.body[0]
                    }}</span>
                    <!-- Button -->
                    <input class="add-post-btn" type="submit" value="Submit" />
                </form>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    data() {
        return {
            success: false,
            fields: {
                category_id: "",
            },
            errors: {},
            urls: [],
            categories: [],
            images: [],
        };
    },

    methods: {
        grabFile(e) {
            const files = e.target.files;
            this.images = [];
            this.urls = [];

            if (files.length < 3 || files.length > 3) {
                alert("3 ta rasm bo'lsin.");
                e.target.value = "";
                return;
            }

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                this.images.push(file);
                const reader = new FileReader();
                reader.onload = (e) => {
                    const url = e.target.result;
                    this.urls.push(url);
                };
                reader.readAsDataURL(file);
            }
        },

        submit() {
            const formData = new FormData();

            formData.append("category_id", this.fields.category_id);
            formData.append("body", this.fields.body);
            formData.append("title", this.fields.title);

            this.images.forEach((image, index) => {
                formData.append(`images[${index}]`, image);
            });

            axios
                .post("/api/posts", formData, {
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
            .get("/api/categories")
            .then((response) => (this.categories = response.data))
            .catch((error) => {
                console.log(error);
            });
    },
};
</script>

<style scoped>
.create-post {
    background-color: #fff;
    padding: 0 3vw;
}
.container input,
textarea,
select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    margin-top: 6px;
    margin-bottom: 20px;
    font-size: 16px;
}
h1 {
    text-align: center;
    padding: 60px 0 50px 0;
}

.add-post-btn {
    background-color: black;
    color: white;
    border: none;
    cursor: pointer;
    transition: 0.3s ease;
}

.add-post-btn:hover {
    transform: translateY(-4px);
}

.preview img {
    max-width: 100%;
    max-height: 120px;
}
</style>
