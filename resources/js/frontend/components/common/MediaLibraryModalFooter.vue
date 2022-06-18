<template>
    <div class="modal-footer">
        <button
            type="button"
            class="btn btn-secondary"
            data-dismiss="modal"
            @click="handleClick"
        >
            Close
        </button>
    </div>
</template>
<script>
export default {
    methods: {
        handleClick() {
            this.$emit("click");
        },
    },
};
</script>
