<template>
    <div>
        {{ step }} / {{ steps }}
    </div>
</template>

<script>
export default {
    name: "OpencastConfigStep",
    props: ['step', 'steps'],
    methods: {
        hide() {
            this.$emit('hide');
        }
    }
};
</script>
