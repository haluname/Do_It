<template>
      <v-overlay :value="generating" z-index="1000" opacity="0.9" class="d-flex align-center justify-center">
            <div class="text-center">
                <v-progress-circular
                    indeterminate
                    size="50"
                    width="8"
                    color="teal lighten-2"
                ></v-progress-circular>
                <div class="mt-6 text-h6 white--text">Generazione {{name}} in corso...</div>
            </div>
        </v-overlay>
</template>
<script>
export default {
    name: "Loader",
    props: {
        generating: {
            type: Boolean,
            default: false
        },
        name : {
            type: String,
            default: "Loader"
        }
    }
};
</script>